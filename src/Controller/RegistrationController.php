<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ImportCsvType;
use App\Form\RegistrationFormType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager, SiteRepository $siteRepository): Response
    {
        $user = new User();
        $registrationForm = $this->createForm(RegistrationFormType::class, $user);
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid())
        {
            $user->setPseudo('pseudo');
            $user->setAdministrateur(0);
            $user->setActif(1);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $registrationForm->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Nouvel utilisateur enregistré');
            return $this->redirectToRoute('sortie_accueil');
        }

        $csvForm = $this->createForm(ImportCsvType::class);
        $csvForm->handleRequest($request);

        if ($request->isMethod('POST') && $csvForm->isSubmitted() && $csvForm->isValid())
        {
            $csvFile = $csvForm->get('file')->getData();

            $csv = Reader::createFromPath($csvFile->getPathname(), 'r');
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(';');

            $records = $csv->getRecords();

            foreach ($records as $record) {
                $site = $siteRepository->find($record['site_id']);
                $user = new User();
                $user->setEmail($record['email']);
                $user->setRoles([$record['roles']]);
                $user->setPassword($record['password']);
                $user->setPseudo($record['pseudo']);
                $user->setPrenom($record['prenom']);
                $user->setNom($record['nom']);
                $user->setAdministrateur($record['administrateur']);
                $user->setActif($record['actif']);
                $user->setSite($site);

                $entityManager->persist($user);
                $entityManager->flush();
            }
            $this->addFlash('success', 'Nouveaux utilisateurs importés!');;
            return $this->redirectToRoute('app_register');

        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'csvForm' => $csvForm->createView()
        ]);
    }
}