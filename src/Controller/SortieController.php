<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sorties', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('', name: 'accueil')]
    public function list(SortieRepository $sortieRepository, SiteRepository $siteRepository): Response
    {
        $listeSortie = $sortieRepository->findAll();
        $listeSite = $siteRepository->findAll();

        return $this->render('sortie/accueil.html.twig', compact("listeSortie"),
        );
    }


    #[Route('/afficher/{id}', name: 'afficher')]
    public function afficher(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('sortie/afficher.html.twig', [
            'sortie' => $sortie
        ]);
    }


    #[Route('/creer', name: 'creer')]
    public function creer(Request $request, EntityManagerInterface $entityManager): Response
    {
       $sortie = new Sortie();

       $sortieForm = $this->createForm(SortieType::class, $sortie);

        dump($sortie);
        $sortieForm->handleRequest($request);
        dump($sortie);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre sortie a été ajoutée !');
            return $this->redirectToRoute( 'sortie_afficher',
            ['id'=>$sortie->getId()]);
        }

        dump($request);
        return $this->render('sortie/creer.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    #[Route('/modifier', name: 'modifier')]
    public function modifier(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('sortie/modifier.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    #[Route('/annuler', name: 'annuler')]
    public function annuler(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('sortie/annuler.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }


}
