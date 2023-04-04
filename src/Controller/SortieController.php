<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\ModifySortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
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

        $user = $this->getUser();
        $listeSortie = $sortieRepository->findAllExceptArchivee($user);
        $listeSite = $siteRepository->findAll();


        $date = new \DateTime();

        return $this->render('sortie/accueil.html.twig', [
            'listeSortie' => $listeSortie,
            'listeSite' => $listeSite,
            'dateDuJour' => $date,
        ]);
    }


    #[Route('/afficher/{id}', name: 'afficher')]
    public function afficher(int $id, SortieRepository $sortieRepository, UserRepository $userRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $userRepository->find($id);

        return $this->render('sortie/afficher.html.twig', [
            'sortie' => $sortie,
            'user' => $user
        ]);
    }

    #[Route('/tri', name: 'tri')]
    public function tri(SortieRepository $sortieRepository, Request $request, SiteRepository $siteRepository): Response
    {

        $site = $request->get('site');

        $dateStart = $request->get('date-start');
        $dateEnd = $request->get('date-end');

        $barreRecherche = $request->get('rechercher');

        $organisateur = $request->get('orga');
        $inscrit = $request->get('inscrit');
        $nonInscrit = $request->get('nonInscrit');
        $passees = $request->get('passees');

        $date = new \DateTime();
        $user = $this->getUser();

        $listeSortie = $sortieRepository->findByTri($site, $dateStart, $dateEnd, $barreRecherche, $organisateur, $user, $passees, $inscrit, $nonInscrit, $date);
        $listeSite = $siteRepository->findAll();

        return $this->render('sortie/accueil.html.twig', [
            'listeSortie' => $listeSortie,
            'listeSite' => $listeSite,
            'dateDuJour' => $date,
            'site' => $site,
            'recherche' => $barreRecherche,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'orga' => $organisateur,
            'inscrit' => $inscrit,
            'nonInscrit' => $nonInscrit,
            'passees' => $passees,

        ]);
    }


    #[Route('/creer', name: 'creer')]
    public function creer(Request $request, EntityManagerInterface $entityManager, LieuRepository $lieuRepository, VilleRepository $villeRepository, EtatRepository $etatRepository): Response
    {
        $lieu = $lieuRepository->findAll();
        $Ville = $villeRepository->findAll();

        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $etatCreer = $etatRepository->find(1);
            $user = $this->getUser();
            $sortie->setOrganisateur($user);
            $sortie->setEtatE($etatCreer);
            $sortie->setSite($user->getSite());
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre sortie a été ajoutée! Vous devez cliquer sur "Publier" pour la rendre visible aux autres utilisateurs.');
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

    #[Route('/modifier/{id}', name: 'modifier')]
    public function modifier(int $id, Request $request, EntityManagerInterface $entityManager, LieuRepository $lieuRepository, VilleRepository $villeRepository, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser()->getId();

        $modifySortieForm = $this->createForm(ModifySortieType::class, $sortie);
        $modifySortieForm->handleRequest($request);

        $nbrPartBdd = $sortie->getParticipant()->count();

        if ($modifySortieForm->isSubmitted() && $modifySortieForm->isValid()) {
            $valeurNbrPlaces = $modifySortieForm->get('nbInscriptionMax')->getData();
            if ($user == $sortie->getOrganisateur()->getId() and ($sortie->getEtatE()->getId() == 1 || $sortie->getEtatE()->getId() == 2)) {
                dump($valeurNbrPlaces);
                dump($nbrPartBdd);
                if ($nbrPartBdd < $valeurNbrPlaces) {
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', 'Votre sortie a été modifiée!');

                    return $this->redirectToRoute('sortie_afficher',
                        ['id' => $sortie->getId()]);
                } else {
                    $this->addFlash('error', "Vous ne pouvez pas mettre un nombre de participants inférieur aux participants inscrits");
                }

            }

        }
        return $this->render('sortie/modifier.html.twig', [
            'modifySortieForm' => $modifySortieForm->createView(),
            'sortie' => $sortie
        ]);
    }

    #[Route('/publier/{id}', name: 'publier')]
    public function publier(int $id, Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response
    {

        $sortie = $sortieRepository->find($id);
        $user = $this->getUser()->getId();

        if ($user == $sortie->getOrganisateur()->getId() and $sortie->getEtatE()->getId() == 1) {
            $etatOuvert = $etatRepository->find(2);
            $sortie->setEtatE($etatOuvert);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre sortie a été publiée!');
            return $this->redirectToRoute('sortie_accueil',
                ['id' => $sortie->getId()]);
        }

        return $this->redirectToRoute('sortie_accueil');
    }

    #[Route('/annuler/{id}', name: 'annuler')]
    public function annuler(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        $date = new \DateTime();
        $sortie = $sortieRepository->find($id);
        $dateDebutSortie = $sortie->getDateHeureDebut();
        $enregistrer = $request->get('enregistrer');
        $user = $this->getUser();
        if (($user == $sortie->getOrganisateur() or $user->isAdministrateur()==true )and $date < $dateDebutSortie and $sortie->getEtatE()!=6){
            if ($enregistrer != null) {
                $etatAnnuler = $etatRepository->find(6);
                $sortie->setEtatE($etatAnnuler);
                $motif = $request->get('motif');
                if ($motif != null) {
                    $sortie->setInfosSortie($motif);
                    $entityManager->persist($sortie);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre sortie a été annulée!');
                    return $this->redirectToRoute('sortie_accueil',
                        ['id' => $sortie->getId()]);
                } else {
                    $this->addFlash('error', "Vous devez entrer un motif d'annulation");
                }
            }
        }
        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sinscrire/{id}', name: 'sinscrire')]
    public function inscriptionParticipant(Sortie $sortiesParticipation, EntityManagerInterface $entityManager): Response
    {
        $nbrParticipant = $sortiesParticipation->getParticipant()->count();
        $nbInscriptionMax = $sortiesParticipation->getNbInscriptionMax();
        $dateCloture = $sortiesParticipation->getDateLimiteInscription();
        $user = $this->getUser();
        $date = new \DateTime();

        if ($date < $dateCloture and $nbrParticipant < $nbInscriptionMax and !$sortiesParticipation->getParticipant()->contains($user)and $sortiesParticipation->getEtatE()->getId() == 2) {
            $user = $this->getUser();
            $sortiesParticipation->addParticipant($user);
            $entityManager->persist($sortiesParticipation);
            $entityManager->flush();

            $this->addFlash('success', "Vous êtes bien inscrit.e à cette sortie.");
        }

        return $this->redirectToRoute('sortie_accueil');
    }

    #[Route('/desiste/{id}', name: 'desiste')]
    public function desistementParticipant(Sortie $sortiesParticipation, EntityManagerInterface $entityManager): Response
    {

        $dateCloture = $sortiesParticipation->getDateLimiteInscription();
        $user = $this->getUser();
        $date = new \DateTime();

        if ($date < $dateCloture and $sortiesParticipation->getParticipant()->contains($user) and $sortiesParticipation->getEtatE()->getId() == 2){


            $sortiesParticipation->removeParticipant($user);

            $entityManager->persist($sortiesParticipation);
            $entityManager->flush();

            $this->addFlash('success', "Vous n'êtes plus inscrit.e à cette sortie.");

        }

        return $this->redirectToRoute('sortie_accueil');
    }
}
