<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
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
        $date_str = $date->format('Y-m-d H:i:s');

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


        $user = $this->getUser();

        $date = new \DateTime();
        $date_str = $date->format('Y-m-d H:i:s');
        $user = $this->getUser();

        $listeSortie = $sortieRepository->findByTri($site, $dateStart, $dateEnd, $barreRecherche, $organisateur, $user, $passees, $inscrit, $nonInscrit, $date_str);
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

    #[Route('/modifier', name: 'modifier')]
    public function modifier(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('sortie/modifier.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    #[Route('/annuler/{id}', name: 'annuler')]
    public function annuler(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);

        if ($this->isCsrfTokenValid('annuler' . $id, $request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/sinscrire/{id}', name: 'sinscrire')]
    public function inscriptionParticipant (Sortie $sortiesParticipation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $sortiesParticipation->addParticipant($user);
        $entityManager->persist($sortiesParticipation);
        $entityManager->flush();

        $this->addFlash('success', "Vous êtes inscrit à la sortie");

        return $this->redirectToRoute('sortie_accueil');
    }

    #[Route('/desiste/{id}', name: 'desiste')]
    public function desistementParticipant(Sortie $sortiesParticipation, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $sortiesParticipation->removeParticipant($user);

        $entityManager->persist($sortiesParticipation);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_accueil');

    }
}
