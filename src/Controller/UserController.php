<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
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
        return $this->redirectToRoute('app_afficher'); // Remplacez 'user_list' par la route appropriée
    }
}
