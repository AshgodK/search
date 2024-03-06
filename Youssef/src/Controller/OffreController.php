<?php

namespace App\Controller;
use App\Entity\Offre;
use App\Entity\User;
use App\Form\OffreTypeB;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

#[Route('/offre')]
class OffreController extends AbstractController
{
    #[Route('/', name: 'app_offre_index', methods: ['GET'])]
    public function index(OffreRepository $offreRepository): Response
    {
        return $this->render('offre/index.html.twig', [
            'offres' => $offreRepository->findAll(),
        ]);
    }    
    #[Route('/back', name: 'app_offre_indexB', methods: ['GET'])]
    public function indexB(OffreRepository $offreRepository): Response
    {
        // Fetch offers from repository
        $offres = $offreRepository->findAll();
    
        // Process the data to calculate statistics
        $totalOffers = count($offres);
        $expiredOffers = 0;
    
        foreach ($offres as $offer) {
            if ($offer->getEchances() < new \DateTime()) {
                $expiredOffers++;
            }
        }
    
        // Calculate statistics
        $activeOffers = $totalOffers - $expiredOffers;
        $percentageExpired = $totalOffers > 0 ? ($expiredOffers / $totalOffers) * 100 : 0;
    
        // Render the indexB.html.twig template with both offers and statistics
        return $this->render('offre/indexB.html.twig', [
            'offres' => $offres,
            'totalOffers' => $totalOffers,
            'activeOffers' => $activeOffers,
            'expiredOffers' => $expiredOffers,
            'percentageExpired' => $percentageExpired,
        ]);
    }








    #[Route('/form/{userId}', name: 'app_offre_indexsho', methods: ['GET'])]
    public function indexsho(Request $request, OffreRepository $offreRepository, $userId): Response
    {
        // Create an instance of the Offre entity
        $offre = new Offre();
        // Optionally, set any values if needed
    
        // Create the form using the OffreType form type
        $form = $this->createForm(OffreType::class, $offre);
    
        // Render the form template with the form variable
        return $this->render('offre/_form.html.twig', [
            'userId' => $userId,
            'form' => $form->createView(),
        ]);
    }   

    #[Route('/view/{userId}', name: 'user_offers')]
    public function userOffers(Request $request, int $userId, OffreRepository $offreRepository, PaginatorInterface $paginator): Response
    {
        // Fetch offers of the user based on the user ID
        $offersQuery = $offreRepository->createQueryBuilder('o')
            ->andWhere('o.idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();
        
        $pagination = $paginator->paginate(
            $offersQuery, // Query to paginate
            $request->query->getInt('page', 1), // Page number
            2 // Limit per page
        );
    
        // Render the show.html.twig template with pagination data and userId variable
        return $this->render('offre/show.html.twig', [
            'pagination' => $pagination,
            'userId' => $userId, // Pass userId variable to the template
        ]);
    }
    
    
    

    #[Route('/edit/{id}/{userId}', name: 'app_offre_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, EntityManagerInterface $entityManager, OffreRepository $offreRepository, $id, int $userId): Response
{
    // Find the Offre entity by ID
    $offre = $offreRepository->find($id);

    // Check if Offre entity exists
    if (!$offre) {
        throw $this->createNotFoundException('Offre not found');
    }
    $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
    $username = $user->getNom();

    // Create the edit form using OffreType, passing the $offre entity
    $form = $this->createForm(OffreType::class, $offre);

    // Handle form submission
    $form->handleRequest($request);

    // Check if form is submitted and valid
    if ($form->isSubmitted() && $form->isValid()) {
        // Persist and flush the updated Offre entity
        $entityManager->flush();

        // Redirect to a success page or return a response as needed
        return $this->redirectToRoute('user_offers', ['userId' => $userId], Response::HTTP_SEE_OTHER);
    }

    // Render the edit form template if form is not submitted or not valid
    return $this->render('offre/edit.html.twig', [
        'form' => $form->createView(),
        'offre' => $offre,
        'userId' => $userId, // Pass the Offre entity to the template
        'username'=> $username,
    ]);
}
#[Route('back/edit/{id}', name: 'app_offre_editB', methods: ['GET', 'POST'])]
public function editB(Request $request, EntityManagerInterface $entityManager, OffreRepository $offreRepository, $id, $nom=null): Response
{
    // Find the Offre entity by ID
    $offre = $offreRepository->find($id);

    // Check if Offre entity exists
    if (!$offre) {
        throw $this->createNotFoundException('Offre not found');
    }

    // Create the edit form using OffreType, passing the $offre entity
    $form = $this->createForm(OffreTypeB::class, $offre);

    // Handle form submission
    $form->handleRequest($request);

    // Check if form is submitted and valid
    if ($form->isSubmitted() && $form->isValid()) {
        // Persist and flush the updated Offre entity
        $entityManager->flush();

        // Redirect to a success page or return a response as needed
        return $this->redirectToRoute('app_offre_indexB', [], Response::HTTP_SEE_OTHER);
    }

    // Render the edit form template if form is not submitted or not valid
    return $this->render('offre/editB.html.twig', [
        'form' => $form->createView(),
        'offre' => $offre, // Pass the Offre entity to the template
        'nom' => $nom,
    ]);
}
#[Route('/new/{userId}', name: 'app_offre_new', methods: ['GET', 'POST'])]
public function create(Request $request, EntityManagerInterface $entityManager, $userId): Response
{
    // Check if the user ID is valid (you might want to add additional validation here)
    if (!is_numeric($userId) || $userId <= 0) {
        throw $this->createNotFoundException('Invalid user ID');
    }

    // Fetch the User entity corresponding to the provided userId
    $user = $this->getDoctrine()->getRepository(User::class)->find($userId);

    // Check if the User entity exists
    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }
    $username = $user->getNom();

    // Create a new Offre entity
    $offre = new Offre();

    // Set the User entity for the Offre
    $offre->setIdUser($user);

    // Create the form using OffreType
    $form = $this->createForm(OffreType::class, $offre);

    // Handle form submission
    $form->handleRequest($request);

    // Check if form is submitted and valid
    if ($form->isSubmitted() && $form->isValid()) {
        // Persist and flush the Offre entity
        $entityManager->persist($offre);
        $entityManager->flush();

        // Redirect to a success page or return a response as needed
        return $this->redirectToRoute('user_offers', ['userId' => $userId], Response::HTTP_SEE_OTHER);
    }

    // Render the form template if form is not submitted or not valid
    return $this->render('offre/new.html.twig', [
        'form' => $form->createView(),
        'userId' => $userId, // Pass userId to the template
        'username'=> $username,
    ]);
}

    #[Route('/delete /{userId}/{id}', name: 'app_offre_delete', methods: ['GET'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager, $userId, int $id): Response
    {
        // Remove and flush the Offre entity
        try {
            $entityManager->remove($offre);
            $entityManager->flush();
        } catch (\Exception $e) {
            // Handle any exceptions (e.g., database errors)
            throw $this->createNotFoundException('Error deleting offer: '.$e->getMessage());
        }
    
        // Redirect to the user offers page after successful deletion
        return $this->redirectToRoute('user_offers', ['userId' => $userId], Response::HTTP_SEE_OTHER);
    }
    #[Route('/back/{id}', name: 'app_offre_deleteB', methods: ['GET'])]
    public function deleteB(Request $request, Offre $offre, EntityManagerInterface $entityManager, int $id): Response
    {
        // Remove and flush the Offre entity
        try {
            $entityManager->remove($offre);
            $entityManager->flush();
        } catch (\Exception $e) {
            // Handle any exceptions (e.g., database errors)
            throw $this->createNotFoundException('Error deleting offer: '.$e->getMessage());
        }
    
        // Redirect to the user offers page after successful deletion
        return $this->redirectToRoute('app_offre_indexB', [], Response::HTTP_SEE_OTHER);
    }    
    
       
    #[Route('/of/{id}', name: 'offre_show', methods: ['GET', 'POST'])]
    public function show(Offre $offre): Response
    {
        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }
#[Route('back/new', name: 'app_offre_newB', methods: ['GET', 'POST'])]
public function newB(Request $request, EntityManagerInterface $entityManager): Response
{
    // Create a new Offre entity
    $offre = new Offre();

    // Set the user ID

    // Create the form using OffreTypeB
    $form = $this->createForm(OffreTypeB::class, $offre);
      
    // Handle form submission
    $form->handleRequest($request);

    // Check if form is submitted and valid
    if ($form->isSubmitted() && $form->isValid()) {
        // Persist and flush the Offre entity
        $entityManager->persist($offre);
        $entityManager->flush();

        return $this->redirectToRoute('app_offre_indexB', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('offre/newB.html.twig', [
        'form' => $form,
    ]);
}
public function fetchOfferDetails(Request $request): JsonResponse
    {
        // Get the offer ID from the request parameters
        $offerId = $request->query->get('id');

        // Fetch offer details based on the ID
        $offer = $this->offerRepository->find($offerId);

        // Check if offer exists
        if (!$offer) {
            // Return JSON response with error message
            return new JsonResponse(['error' => 'Offer not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Construct array with offer details
        $offerDetails = [
            'id' => $offer->getId(),
            'nom' => $offer->getNom(),
            // Add other properties as needed
        ];

        // Return JSON response with offer details
        return new JsonResponse($offerDetails);
    }
    #[Route('/search', name: 'app_search', methods: ['GET', 'POST'])]
    public function search(Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $query = $request->query->get('query');
    
        // Get the entity manager
        $entityManager = $this->getDoctrine()->getManager();
    
        // Perform a database query to search for offers based on the name
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('offre', 'user.id AS userId') // Select the offer and associated user ID
            ->from(Offre::class, 'offre')
            ->leftJoin('offre.idUser', 'user') // Join the User entity
            ->where('offre.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%');
    
        // Paginate the query
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Pass the query to paginate
            $request->query->getInt('page', 1),
            2 // Limit per page
        );
    
        // Prepare search results for JSON response
        $searchResults = [];
        foreach ($pagination as $result) {
            // Assuming $result is an array, access the offer and user ID
            $offer = $result[0]; // The offer object is at index 0
            $userId = $result['userId']; // The user ID is at the 'userId' key
            // Serialize the offer and user ID into an array
            $searchResults[] = [
                'offer' => [
                    'id' => $offer->getId(),
                    'nom' => $offer->getNom(),
                    'description' => $offer->getDescription(),
                    'echances' => $offer->getEchances() ? $offer->getEchances()->format('Y-m-d') : null, // Format date if not null
                    'statut' => $offer->getStatut(),
                    'prix' => $offer->getPrix(),
                ],
                'userId' => $userId,
            ];
        }
    
        // Return JSON response with paginated search results
        return new JsonResponse($searchResults);
    }
    #[Route('/statistics', name: 'statistics')]
    public function statistics(OffreRepository $offreRepository): Response
    {
        $topThreeOffers = $offreRepository->findTopThreeByDemandCount();

        return $this->render('demande/statistics.html.twig', [
            'topThreeOffers' => $topThreeOffers,
        ]);
    }

    #[Route('/offres/sort-by-echances', name: 'offres_sort_by_echances')]
    public function sortByEchances(OffreRepository $offreRepository): Response
    {
        $offres = $offreRepository->findAllOrderedByEchancesAsc();

        return $this->render('offre/trie.html.twig', [
            'offres' => $offres,
        ]);
    }
    #[Route('/offres/sort-by-approved', name: 'offres_sort_by_approved')]
    public function sortByApprovedStatut(OffreRepository $offreRepository): Response
    {
        $approvedOffres = $offreRepository->findAllOrderedByApprovedStatut();

        return $this->render('offre/approved_trie.html.twig', [
            'approvedOffres' => $approvedOffres,
        ]);
    }
}
