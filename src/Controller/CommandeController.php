<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Produit;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[Route('/commande')]
class CommandeController extends AbstractController
{
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
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/commande/passer/{id}', name: 'passer_commande', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function passerCommande(Produit $produit, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $commande = new Commande();
        $commande->setDateCommande(new \DateTimeImmutable());
        $commande->setMontantTotal($produit->getPrix());
        $commande->setUser($user);
        $commande->addProduit($produit);

        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Commande passée avec succès!');

        return $this->redirectToRoute('mes_commandes');
    }


    #[Route('/mes-commandes', name: 'mes_commandes')]
    public function mesCommandes(EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Trouver les commandes de l'utilisateur connecté
        $commandes = $em->getRepository(Commande::class)->findBy(['user' => $user]);

        return $this->render('commande/mes_commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/commande/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mes_commandes');
  
    }
}    