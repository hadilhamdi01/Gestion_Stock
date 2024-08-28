<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'app_categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {

        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
            
           
        ]);
    }

    #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the image file upload
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Define this in services.yaml
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception during file upload
                }

                // Store the filename in the database
                $categorie->setImage($newFilename);
            }

            // Save the category
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_index');
        }

        return $this->render('categorie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(CategorieType::class, $categorie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('image')->getData();

        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/images/', $filename);
            $categorie->setImage($filename);
        }

        $entityManager->persist($categorie);
        $entityManager->flush();

        return $this->redirectToRoute('app_categorie_index');
    }

    return $this->render('categorie/edit.html.twig', [
        'categorie' => $categorie,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
