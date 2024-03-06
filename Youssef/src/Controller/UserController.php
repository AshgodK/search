<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\PassTypeFront;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/thanks/{id}', name: 'thanks' ,methods:['GET'])]
    public function thanks(User $user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('utilisateur/thanks.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/search', name: 'app_search', methods: ['GET', 'POST'])]    
public function searchUsers(Request $request, UserRepository $utilisateurRepository): Response
{
    $searchTerm = $request->request->get('search');
    $users = $utilisateurRepository->search($searchTerm); // Implement your search logic here

    return $this->render('utilisateur/index.html.twig', [
        'utilisateurs' => $users,
        'freelancers' => $utilisateurRepository->findByRole("Freelancer"),
        'admins' => $utilisateurRepository->findByRole("Admin"),
        'clients' => $utilisateurRepository->findByRole("Client"),
    ]);
}
    #[Route('/', name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UserRepository $utilisateurRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
            'freelancers' => $utilisateurRepository->findByRole("Freelancer"),
            'admins' => $utilisateurRepository->findByRole("Admin"),
            'clients' => $utilisateurRepository->findByRole("Client"),
        ]);
     
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $utilisateur = new User();
        $form = $this->createForm(UserType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(User $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }
    #[Route('/{id}/editPassUser', name: 'app_pass_front', methods: ['GET', 'POST'])]
    public function editPassFront(Request $request, User $user, UserPasswordHasherInterface $userPasswordHasher,EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PassTypeFront::class);
        $form->handleRequest($request);
       

        return $this->renderForm('utilisateur/editPassFront.html.twig', [
            
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/editPassword/Front', name: 'edit_password_db_front', methods: ['GET', 'POST'])]
    public function editPassDBFront(Request $request, User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $oldPassword = $request->request->get('old-password');
    
        // Check if the old password matches the user's current password
        if (!$passwordEncoder->isPasswordValid($user, $oldPassword)) {
            // Old password doesn't match, return an error
            $this->addFlash('error', 'Old password is incorrect.');
            return $this->redirectToRoute('app_pass_front', ['id' => $user->getId()]);
        } else {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get('password')
                )
            );
            $entityManager->flush();
    
            return $this->redirectToRoute('thanks', ['id' => $user->getId()]);
           
        }
    }
    #[Route('/{id}/ban', name: 'app_user_ban', methods: ['GET','POST'])]
    public function Ban(Request $request,UserRepository $repo,User $user, SessionInterface $session,EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(true);
        $entityManager->flush();
        
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $repo->findAll(),
            'freelancers' => $repo->findByRole("Freelancer"),
            'admins' => $repo->findByRole("Admin"),
            'clients' => $repo->findByRole("Client"),
        ]);
    }
    #[Route('/{id}/unban', name: 'app_user_unban', methods: ['GET','POST'])]
    public function unBan(Request $request,UserRepository $repo,User $user, SessionInterface $session,EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(0);
        $entityManager->flush();
    
        
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $repo->findAll(),
            'freelancers' => $repo->findByRole("Freelancer"),
            'admins' => $repo->findByRole("Admin"),
            'clients' => $repo->findByRole("Client"),
        ]);
    }
    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,UserPasswordHasherInterface $userPasswordHasher, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData() !== $form->get('confirm_password')->getData()) {
                $form->addError(new FormError('The passwords do not match.'));
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $file = $form->get('ImagePath')->getData();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
    
            // Move the file to the directory where brochures are stored
            $targetDirectory = $this->getParameter('kernel.project_dir') . '/public';
            $file->move(
                $targetDirectory,
                $fileName
            );
            $user->SetImagePath($fileName);
            $datestring=$form->get('date_n')->getData();
            $date = new \DateTime($datestring->format('m/d/Y'));
    // Format the DateTime object as per your requirement ('m-d-Y' in your case)
   

    // Set the formatted date to your user entity
    $user->setDateN($date);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/edit.html.twig', [
            'utilisateur' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/profile/{id}', name: 'app_profile', methods: ['GET'])]
    public function showFront(User $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('utilisateur/profile.html.twig', [
            'user' => $utilisateur,
        ]);
    }
    #[Route('/{id}/editProfile', name: 'app_front_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, User $user, UserRepository $utilisateurRepository,EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('ImagePath')->getData();

            if ($uploadedFile) {
                // generate a unique file name
                $newFileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public';
            
                // move the uploaded file to the target directory
                $uploadedFile->move(
                    $targetDirectory, // specify the target directory where the file should be saved
                    $newFileName      // specify the new file name
                );
                    
                            // set the image path to the path of the uploaded file
                            $user->setImagePath($newFileName);
                
            }
          
            $entityManager->flush();
            // Exemple dans votre contrôleur
             
            return $this->redirectToRoute('thanks', ['id' => $user->getId()],Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('utilisateur/editProfile.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, User $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
