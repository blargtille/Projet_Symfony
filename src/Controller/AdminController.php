<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Ville;
use App\Form\SiteType;
use App\Form\VilleType;
use App\Repository\SiteRepository;
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
        $entityManager->remove($ville);
        $entityManager->flush();

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
        $entityManager->remove($site);
        $entityManager->flush();

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


}
