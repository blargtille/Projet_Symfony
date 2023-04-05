<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Ville;
use App\Form\SiteType;
use App\Form\VilleType;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/villes', name: 'villes')]
    public function gererVille(VilleRepository $villeRepository): Response
    {
        $villes = $villeRepository->findAll();

        return $this->render('admin/villes.html.twig', [
            'listeVilles' => $villes,
        ]);
    }

    #[Route('/villes/tri', name: 'villes_tri')]
    public function triVilles(VilleRepository $villeRepository, Request $request): Response
    {
        $barreRecherche = $request->get('rechercher');
        $listeVilles = $villeRepository->findByResearch($barreRecherche);


        return $this->render('admin/villes.html.twig', [
            'listeVilles' => $listeVilles,
        ]);
    }

    #[Route('/villes/creer', name: 'ville_creer')]
    public function creerVilles(VilleRepository $villeRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Votre ville a été ajouté !');
            return $this->redirectToRoute('admin_villes',);
        }

        return $this->render('admin/creerVille.html.twig', [
            'villeForm' => $villeForm->createView(),

        ]);
    }

    #[Route('/villes/suppr/{id}', name: 'villes_suppr')]
    public function supprVilles(VilleRepository $villeRepository, int $id, EntityManagerInterface $entityManager): Response
    {

        $ville = $villeRepository->find($id);
        $lieu = $ville->getLieu();
        if ($lieu->isEmpty()) {
            $entityManager->remove($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Votre ville a été supprimée !');
        } else {
            $this->addFlash('error', 'Votre ville n\'a pas été supprimée car elle est rattachée à un lieu !');
        }

        return $this->redirectToRoute('admin_villes', [

        ]);
    }

    #[Route('/villes/modif/{id}', name: 'villes_modif')]
    public function modifVilles(VilleRepository $villeRepository, int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $ville = $villeRepository->find($id);
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Votre ville a été modifié !');
            return $this->redirectToRoute('admin_villes',);
        }

        return $this->render('admin/creerVille.html.twig', [
            'villeForm' => $villeForm->createView(),

        ]);
    }


    #[Route('/sites', name: 'sites')]
    public function gererSites(SiteRepository $siteRepository): Response
    {
        $sites = $siteRepository->findAll();

        return $this->render('admin/sites.html.twig', [
            'listeSites' => $sites,
        ]);
    }

    #[Route('/sites/tri', name: 'sites_tri')]
    public function triSites(SiteRepository $siteRepository, Request $request): Response
    {
        $barreRecherche = $request->get('rechercher');
        $listeSites = $siteRepository->findByResearch($barreRecherche);


        return $this->render('admin/sites.html.twig', [
            'listeSites' => $listeSites,
        ]);
    }

    #[Route('/sites/creer', name: 'sites_creer')]
    public function creerSites(SiteRepository $siteRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);

        if ($siteForm->isSubmitted() && $siteForm->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            $this->addFlash('success', 'Votre site a été ajouté !');
            return $this->redirectToRoute('admin_sites',);
        }

        return $this->render('admin/creerSite.html.twig', [
            'siteForm' => $siteForm->createView(),

        ]);
    }

    #[Route('/sites/suppr/{id}', name: 'sites_suppr')]
    public function supprSites(SiteRepository $siteRepository, int $id, EntityManagerInterface $entityManager): Response
    {

        $site = $siteRepository->find($id);

        $utilisateurs = $site->getUsers();
        $sorties = $site->getSorties();

        if ($utilisateurs->isEmpty() and $sorties->isEmpty()) {
            $entityManager->remove($site);
            $entityManager->flush();
        } else {
            $this->addFlash('error', 'Votre site n\'a pas été supprimée car il est rattachée à un utilisateur ou à une sortie !');
        }


        return $this->redirectToRoute('admin_sites', [

        ]);
    }

    #[Route('/sites/modif/{id}', name: 'sites_modif')]
    public function modifSites(SiteRepository $siteRepository, int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $site = $siteRepository->find($id);
        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);

        if ($siteForm->isSubmitted() && $siteForm->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            $this->addFlash('success', 'Votre site a été modifié !');
            return $this->redirectToRoute('admin_sites',);
        }

        return $this->render('admin/creerSite.html.twig', [
            'siteForm' => $siteForm->createView(),

        ]);
    }

    #[Route('/utilisateurs', name: 'utilisateurs')]
    public function gererUtilisateurs(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/utilisateurs.html.twig', [
            'listeUser' => $users,
        ]);
    }

    #[Route('/utilisateurs/tri', name: 'utilisateurs_tri')]
    public function triUtilisateurs(UserRepository $userRepository, Request $request): Response
    {
        $barreRecherche = $request->get('rechercher');
        $listeUsers = $userRepository->findByResearch($barreRecherche);


        return $this->render('admin/utilisateurs.html.twig', [
            'listeUser' => $listeUsers,
        ]);
    }

    #[Route('/utilisateurs/desactive/{id}', name: 'utilisateur_desactive')]
    public function desactiveUtilisateur(UserRepository $userRepository, int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $userRepository->find($id);
        $user->setActif(false);
        $user->setRoles(['']);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_utilisateurs', [

        ]);
    }

    #[Route('/utilisateurs/active/{id}', name: 'utilisateur_active')]
    public function activeUtilisateur(UserRepository $userRepository, int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $userRepository->find($id);
        $user->setActif(true);
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_utilisateurs', [

        ]);
    }


    #[Route('/utilisateurs/suppr/{id}', name: 'utilisateurs_suppr')]
    public function supprUtilisateurs(UserRepository $userRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        // qu'est ce qu'il se passe si on suppr l'utilisateur ? suppr des sorties qu'il a crée ?
        $user = $userRepository->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_utilisateurs', [

        ]);
    }


}
