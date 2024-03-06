<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\Commande1Type;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Symfony\Component\Security\Core\Security;


#[Route('/commande')]
class CommandeController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

  #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer une commande.');
        }

        $commande = new Commande();
        // Assigner l'utilisateur actuel à la commande
        $commande->setUser($user);

        $form = $this->createForm(Commande1Type::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();
            $this->sendTwilioMessage($commande);

            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }
    #[Route('/C/{id}', name: 'app_commande_showC', methods: ['GET'])]
    public function showC(Commande $commande): Response
    {
        return $this->render('commande/showBack.html.twig', [
            'commande' => $commande,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Commande1Type::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/editC', name: 'app_commande_editC', methods: ['GET', 'POST'])]
    public function editC(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Commande1Type::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commande/editBack.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/most-ordered-services/stat', name: 'app_commande_most_ordered_services', methods: ['GET'])]
    public function mostOrderedServices(CommandeRepository $commandeRepository): Response
    {
        $mostOrderedServices = $commandeRepository->findByMostOrderedServices();


        return $this->render('commande/most_ordered_services.html.twig', [
            'mostOrderedServices' => $mostOrderedServices,
        ]);
    }
    #[Route('/pdf/{id}', name: 'app_commande_pdf')]
    public function generatePdf(Commande $commande): Response
    {
        $html = $this->renderView('commande/pdf_template.html.twig', [
            'commande' => $commande,
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

        // Set paper size (A4)
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Stream the generated PDF back to the user
        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
    /**
     * @throws ConfigurationException
     * @throws TwilioException
     */
    private function sendTwilioMessage(Commande $commande): void
    {
        $twilioAccountSid = $this->getParameter('twilio_account_sid');
        $twilioAuthToken = $this->getParameter('twilio_auth_token');
        $twilioPhoneNumber = $this->getParameter('twilio_phone_number');

        $twilioClient = new Client($twilioAccountSid, $twilioAuthToken);

        // Customize the message body with the order confirmation and date
        $messageBody = 'Dear customer, your order dated ' . $commande->getDate()->format('Y-m-d') . ' has been confirmed successfully. Total Price: $' . $commande->getPrix();


        $twilioClient->messages->create(
            '+21695688967', 
            [
                'from' => $twilioPhoneNumber,
                'body' => $messageBody,
            ]
        );
    }

    #[Route('/delete-item/{id}', name: 'app_delete_item', methods: ['POST'])]
public function deleteCartItem(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
        $entityManager->remove($commande);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_commande_indexcart'); // Redirect to the cart page
}


}
