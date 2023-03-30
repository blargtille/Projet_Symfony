<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
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

        return $this->render('sortie/accueil.html.twig', [
            'listeSortie' => $listeSortie,
            'listeSite' => $listeSite
        ]);
    }


    #[Route('/afficher/{id}', name: 'afficher')]
    public function afficher(int $id, SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->find($id);
        $usersBySortie = [];

        foreach ($sorties as $sortie) {
            $users = $sortie->getSorties()->map(function ($sortie) {
                return $sortie->getUser();
            });
            $usersBySortie[$sortie->getName()] = $users;
        }

        return $this->render('sortie/afficher.html.twig', [
            'sortie' => $sorties,
            'usersBySortie' => $usersBySortie
        ]);
    }

    #[Route('/tri', name: 'tri')]
    public function tri(SortieRepository $sortieRepository, Request $request, SiteRepository $siteRepository): Response
    {

        // tri dates
        $dateStart = $request->get('date-start');
        $dateEnd = $request->get('date-end');
      //  $listeSortie = $sortieRepository->findSortieByDate($dateStart, $dateEnd);
        //pb : si on met qu'une date ça ne marche pas, obligé de mettre les deux, à changer

        // tri site
        $site = $request->get('site');
      // $listeSortie = $sortieRepository->findBy(['site'=>$site], []);

        // tri barre de recherche
        $barreRecherche = $request->get('rechercher');
      //  $listeSortie = $sortieRepository->findSortieByNameResearch($barreRecherche);

        // tri par cases à cocher
        // récuperer la sélection case à cocher
        $organisateur = $request->get('orga');
        $inscrit = $request->get('inscrit');
        $nonInscrit = $request->get('nonInscrit');
        $passees = $request->get('passees');

        dump($organisateur); // met on ou null

        // en fonction des parametres cochés --> concaténation d'une requête ????????
        //1) si l'utilisateur connecté est organisateur
        $user = $this->getUser();
       // if ($organisateur = "on")
     //  $listeSortie = $sortieRepository->findSortieByOrganisateurUser($user);

        if($inscrit = "on")
         //   $listeSortie = $sortieRepository->findSortieByInscritUser($user);


        //affichage en fonction des dates passées

            $listeSortie = $sortieRepository->findByTri($dateStart, $dateEnd, $barreRecherche);

        //affichage des sites
    //   $listeSortie = $sortieRepository->findAll();
        $listeSite = $siteRepository->findAll();

        //récuperer la date du jour ?



        // nb de participant ? table sortie_user et faire une requete avec count ?

        return $this->render('sortie/accueil.html.twig', [
            'listeSortie' => $listeSortie,
            'listeSite' => $listeSite,

        ]);
    }


    #[Route('/creer', name: 'creer')]
    public function creer(Request $request, EntityManagerInterface $entityManager, LieuRepository $lieuRepository, VilleRepository $villeRepository): Response
    {
        $lieu = $lieuRepository->findAll();
        $Ville = $villeRepository->findAll();


       $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        dump($sortie);
        $sortieForm->handleRequest($request);
        dump($sortie);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setEtatE("creee");
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre sortie a été ajoutée !');
            return $this->redirectToRoute('sortie_afficher',
                ['id' => $sortie->getId()]);
        }

        dump($request);
        return $this->render('sortie/creer.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieu' => $lieu,
            'Ville' => $Ville
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
