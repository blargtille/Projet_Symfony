<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\ModifySortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/sorties', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('', name: 'accueil')]
    public function list(SortieRepository $sortieRepository, SiteRepository $siteRepository): Response
    {
        $listeSortie = $sortieRepository->findAll();
        $listeSite = $siteRepository->findAll();

        $date = new \DateTime();

        return $this->render('sortie/accueil.html.twig', [
            'listeSortie' => $listeSortie,
            'listeSite' => $listeSite,
            'dateDuJour' => $date
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
            'dateDuJour' => $date

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

    #[Route('/modifier/{id}', name: 'modifier')]
    public function modifier(int $id, Request $request, EntityManagerInterface $entityManager, LieuRepository $lieuRepository, VilleRepository $villeRepository, SortieRepository $sortieRepository): Response
    {
        //condition pour modifier
             // je dois être connecté --> rôles !
            // je dois être le créateur de la sortie
            // la sortie doit être à l'état crée ou ouvert
            // je ne peux pas modifier si je modifie le nb de place et qu'il est inférieur au nb de participants

        $sortie = $sortieRepository->find($id);
        $lieu = $sortie->getLieu();
        $user = $this->getUser()->getId();

        $modifySortieForm = $this->createForm(ModifySortieType::class, $sortie);

        $modifySortieForm->handleRequest($request);
        $valeurNbrPlaces = $modifySortieForm->get('nbInscriptionMax')->getData();

        if ($modifySortieForm->isSubmitted() && $modifySortieForm->isValid()) {
            if ($user == $sortie->getOrganisateur()->getId() and $sortie->getEtatE()->getId() == 1 || $sortie->getEtatE()->getId() == 2 ) {
                if ($sortie->getParticipant()->count() > 1)
                $entityManager->persist($lieu);
                $entityManager->persist($sortie);
                $entityManager->flush();

                $this->addFlash('success', 'Votre sortie a été ajoutée !');

                return $this->redirectToRoute('sortie_afficher',
                    ['id' => $sortie->getId()]);
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
      // condition pour publier
        // je dois être connecté --> rôles !
        // je dois être l'organisateur de l'évènement
        // la sortie doit être à l'état créée
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser()->getId();

        if ($user == $sortie->getOrganisateur()->getId() and $sortie->getEtatE()->getId() == 1){
            $etatOuvert = $etatRepository->find(2);
            $sortie->setEtatE($etatOuvert);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_accueil');
    }

    #[Route('/annuler/{id}', name: 'annuler')]
    public function annuler(int $id, SortieRepository $sortieRepository,EtatRepository $etatRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $enregistrer = $request->get('enregistrer');

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
        $date = new \DateTime();

        if ($date < $dateCloture and $nbrParticipant < $nbInscriptionMax) {
            $user = $this->getUser();
            $sortiesParticipation->addParticipant($user);
            $entityManager->persist($sortiesParticipation);
            $entityManager->flush();

            $this->addFlash('success', "Vous êtes bien inscrit.e à cette sortie");
        }

        return $this->redirectToRoute('sortie_accueil');
    }

    #[Route('/desiste/{id}', name: 'desiste')]
    public function desistementParticipant(Sortie $sortiesParticipation, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $sortiesParticipation->removeParticipant($user);

        $entityManager->persist($sortiesParticipation);
        $entityManager->flush();

        $this->addFlash('success', "Vous n'êtes plus inscrit.e à cette sortie!");
        return $this->redirectToRoute('sortie_accueil');

    }
}
