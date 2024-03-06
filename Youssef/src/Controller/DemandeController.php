<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\Offre;
use App\Entity\User;
use App\Form\DemandeType;
use App\Form\DemandeTypeB;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

#[Route('/demande')]
class DemandeController extends AbstractController
{
    #[Route('/', name: 'app_demande_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }

    #[Route('/demande/pdf/{id}', name: 'app_demande_pdf')]
    public function generatePdf(Demande $demande): Response
    {
        $html = $this->renderView('demande/pdf.html.twig', [
            'demande' => $demande,
        ]);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);


        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    /**
     * @throws ConfigurationException
     * @throws TwilioException
     */
    private function sendTwilioMessage(Demande $demande): void
    {
        $twilioAccountSid = $this->getParameter('twilio_account_sid');
        $twilioAuthToken = $this->getParameter('twilio_auth_token');
        $twilioPhoneNumber = $this->getParameter('twilio_phone_number');

        $twilioClient = new Client($twilioAccountSid, $twilioAuthToken);

        // Récupérer les détails de la demande
        $demandeId = $demande->getId();
        $description = $demande->getDescription();

        // Construire le message personnalisé
        $message = "Nouvelle demande créée:\n";
        $message .= "ID de la demande: $demandeId\n";
        $message .= "Description: $description\n";

        try {
            // Envoyer le message Twilio
            $twilioClient->messages->create(
                '+21695688967',
                [
                    'from' => $twilioPhoneNumber,
                    'body' => $message,
                ]
            );
        } catch (Exception $e) {

        }
    }

    #[Route('/new/{userId}/{id}', name: 'app_demande_new', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager,int $userId,int $id): Response
    {
        // Check if the user ID is valid (you might want to add additional validation here)
        if (!is_numeric($userId) || $userId <= 0) {
            throw $this->createNotFoundException('Invalid user ID');
        }
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        $off = $this->getDoctrine()->getRepository(Offre::class)->find($id);

        // Check if the User entity exists
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        
        // Create a new Offre entity
        $demande = new demande();
        $demande->setOffre($off);
        $demande->setUser($user);
    
        // Create the form using OffreType
        $form = $this->createForm(DemandeType::class, $demande);
    
        // Handle form submission
        $form->handleRequest($request);
    
        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user ID manually
            
    
            // Persist and flush the Offre entity
            $entityManager->persist($demande);
            $entityManager->flush();
            $this->sendTwilioMessage($demande);
    
            // Redirect to a success page or return a response as needed
            return $this->redirectToRoute('user_demandes', [
                'userId' => $userId,
                'id' => $id // Add your additional parameter here
            ], Response::HTTP_SEE_OTHER);
        }
    
        // Render the form template if form is not submitted or not valid
        return $this->render('demande/new.html.twig', [
            'form' => $form->createView(),
            'userId' => $userId,
            'id' => $id, // Pass userId to the template
        ]);
    }    

    #[Route('/newB/{id}', name: 'app_demande_newB', methods: ['GET', 'POST'])]
    public function newB(Request $request, EntityManagerInterface $entityManager,$id): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeTypeB::class, $demande);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new demand
            $entityManager->persist($demande);
            $entityManager->flush();
            // Retrieve the ID of the newly persisted demand
            //$demandeId = $demande->getId();
    
            // Redirect to the showB route with the new demand ID as a parameter
            return $this->redirectToRoute('app_demande_showB', ['id' => $id], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('demande/newB.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }
    
    
    #[Route('/{id}', name: 'app_demande_showB', methods: ['GET', 'POST'])]
    public function show(DemandeRepository $demandeRepository,int $id): Response
    {
        $id = (int)$id;
        $demandes = $demandeRepository->findBy(['offre' => $id]);
    
        return $this->render('demande/showB.html.twig', [
            'demandes' => $demandes,
            'id' => $id,
        ]);
    }
    
    #[Route('/view/{id}/{userId}', name: 'user_demandes', methods: ['GET', 'POST'])]
    public function userDemandes(Request $request, int $id, int $userId, DemandeRepository $demandeRepository, PaginatorInterface $paginator): Response
    {
        // Fetch demandes of the user based on the user ID
        $demandesQuery = $demandeRepository->createQueryBuilder('d')
            ->andWhere('d.offre = :id')
            ->setParameter('id', $id)
            ->getQuery();
        
        $pagination = $paginator->paginate(
            $demandesQuery, // Query to paginate
            $request->query->getInt('page', 1), // Page number
            2 // Limit per page
        );
    
        // Render the show.html.twig template with pagination data and userId variable
        return $this->render('demande/show.html.twig', [
            'pagination' => $pagination,
            'id' => $id,
            'userId' => $userId,
             // Pass userId variable to the template
        ]);
    }


    #[Route('/editD/{id}/{userId}', name: 'app_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, DemandeRepository $demandeRepository, $id, int $userId): Response
    {
        // Find the Demande entity by ID
        $demande = $demandeRepository->find($id);
    
        // Check if Demande entity exists
        if (!$demande) {
            throw $this->createNotFoundException('Demande not found');
        }
    
        // Create the edit form using DemandeType, passing the $demande entity
        $form = $this->createForm(DemandeType::class, $demande);
    
        // Handle form submission
        $form->handleRequest($request);
    
        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist and flush the updated Demande entity
            $entityManager->flush();
    
            // Redirect to a success page or return a response as needed
             return $this->redirectToRoute('user_offers', [
                 'userId' => $userId,
                 'id' => $id, // Replace $yourIdValue with the value you want to pass
             ], Response::HTTP_SEE_OTHER);
        }
    
        // Render the edit form template if form is not submitted or not valid
        return $this->render('demande/edit.html.twig', [
            'form' => $form->createView(),
            'demande' => $demande,
            'userId' => $userId, // Pass the Demande entity to the template
            'id'=> $id,
        ]);
    }
    #[Route('/editDB/{id}', name: 'app_demande_editB', methods: ['GET', 'POST'])]
    public function editB(Request $request, EntityManagerInterface $entityManager, DemandeRepository $demandeRepository, $id): Response
    {
        // Find the Demande entity by ID
        $demande = $demandeRepository->find($id);
    
        // Check if Demande entity exists
        if (!$demande) {
            throw $this->createNotFoundException('Demande not found');
        }
    
        // Create the edit form using DemandeType, passing the $demande entity
        $form = $this->createForm(DemandeTypeB::class, $demande);
    
        // Handle form submission
        $form->handleRequest($request);
    
        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist and flush the updated Demande entity
            $entityManager->flush();
    
            // Redirect to a success page or return a response as needed
             return $this->redirectToRoute('app_offre_indexB', [
              // Replace $yourIdValue with the value you want to pass
             ], Response::HTTP_SEE_OTHER);
        }
    
        // Render the edit form template if form is not submitted or not valid
        return $this->render('demande/editB.html.twig', [
            'form' => $form->createView(),
            'demande' => $demande, // Pass the Demande entity to the template
            'id'=> $id,
        ]);
    }

    #[Route('/deleteD/{userId}/{id}', name: 'app_demande_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager, $userId, int $id): Response
    {
        // Store the Demande data before removal for the flash message
        $demandeData = [
            'OFFRE' => $demande->getOffre()->getNom(), // Adjust according to your entity's properties
            'id' => $demande->getId(),
        ];
    
        // Remove and flush the Demande entity
        try {
            $entityManager->remove($demande);
            $entityManager->flush();
            
            // Add a success flash message
            $this->addFlash('success', 'Demande de l offre :"'.$demandeData['OFFRE'].'" successfully deleted.');
        } catch (\Exception $e) {
            // Handle any exceptions (e.g., database errors)
            $this->addFlash('error', 'An error occurred while deleting the demande: '.$e->getMessage());
            return $this->redirectToRoute('user_offers', [
                'userId' => $userId,
            ]);
        }
    
        // Redirect to a specific route after successful deletion
        return $this->redirectToRoute('user_offers', [
            'userId' => $userId,
            'id' => $id, // Replace $yourIdValue with the value you want to pass
        ], Response::HTTP_SEE_OTHER);
    }
    #[Route('/deleteDB/{id}', name: 'app_demande_deleteB', methods: ['GET'])]
    public function deleteB(Request $request, Demande $demande, EntityManagerInterface $entityManager, int $id): Response
    {
        // Remove and flush the Offre entity
        try {
            $entityManager->remove($demande);
            $entityManager->flush();
        } catch (\Exception $e) {
            // Handle any exceptions (e.g., database errors)
            throw $this->createNotFoundException('Error deleting offer: '.$e->getMessage());
        }
    
        // Redirect to the user offers page after successful deletion
        return $this->redirectToRoute('app_offre_indexB', [
             // Replace $yourIdValue with the value you want to pass
        ], Response::HTTP_SEE_OTHER);
    }
    #[Route('/stat', name: 'stat')]
    public function stat(DemandeRepository $demandeRepository): Response
    {
        $topThreeDemandes = $demandeRepository->findTopThreeWithMostOffres();

        return $this->render('demande/stat.html.twig', [
            'topThreeDemandes' => $topThreeDemandes,
        ]);
    }
}
