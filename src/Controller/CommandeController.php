<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\CartService;

#[Route('/commande')]
class CommandeController extends AbstractController

{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mes_commandes');
    }

    #[Route('/ajouter-commande', name: 'ajouter_commande')]
    public function ajouterCommande(Request $request): Response
    {
        $session = $request->getSession();
        $produitId = $request->request->get('product_id');
        
        if (!$produitId) {
            $this->addFlash('error', 'ID du produit manquant.');
            return $this->redirectToRoute('app_home');
        }

        $produit = $this->entityManager->getRepository(Produit::class)->find($produitId);
        
        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('app_home');
        }

        $commandes = $session->get('commandes', []);

        $commande = [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'prix' => $produit->getPrix(),
            'quantite' => 1,
        ];
        
        $commandes[] = $commande;
        $session->set('commandes', $commandes);

        $this->addFlash('success', 'Produit ajouté au panier!');

        return $this->redirectToRoute('mes_commandes');
    }

    #[Route('/mes-commandes', name: 'mes_commandes')]
    public function mesCommandes(Request $request): Response
    {
        $session = $request->getSession();
        $commandes = $session->get('commandes', []);
        
        return $this->render('commande/mes_commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commande/passer/{id}', name: 'passer_commande', methods: ['POST'])]
    public function passerCommande(int $id, Request $request, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);
    
        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('app_home');
        }
    
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

}