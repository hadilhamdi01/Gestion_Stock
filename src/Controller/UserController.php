<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

use App\Form\UserType;
use App\Form\ChangePasswordType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/afficher', name: 'app_afficher')]
    public function afficher(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $forms = [];
        foreach ($users as $user) {
            $forms[$user->getId()] = $this->createForm(UserType::class, $user)->createView();
        }
        return $this->render('user/afficher.html.twig', [
            'users' => $users,
            'forms' => $forms,
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_edit', methods: ['POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_afficher');
        }

        return $this->render('user/afficher.html.twig', [
            'users' => $em->getRepository(User::class)->findAll(),
            'forms' => [
                $user->getId() => $form->createView(),
            ],
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        // Assurez-vous que l'utilisateur a les droits nécessaires pour supprimer un utilisateur
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        // Suppression de l'utilisateur
        $em->remove($user);
        $em->flush();

        // Redirection après suppression
        return $this->redirectToRoute('app_afficher');
    }

    #[Route('/profile/edit', name: 'app_user_edit_profile')]
   
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Assurez-vous que l'utilisateur est authentifié
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour éditer votre profil.');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Gérer la mise à jour du mot de passe en clair
            if ($data->getNewPassword()) {
                $user->setPassword($data->getNewPassword());
            }

            // Mettre à jour les autres champs
            $user->setNom($data->getNom());
            $user->setEmail($data->getEmail());
            $user->setContact($data->getContact());
            foreach ($data->getAdresses() as $adresse) {
                $adresse->setUser($user);
                $entityManager->persist($adresse);
            }

          
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_user_edit_profile');
        }

        return $this->render('user/edit_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
    
    