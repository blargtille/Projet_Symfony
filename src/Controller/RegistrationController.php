<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ImportCsvType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $registrationForm = $this->createForm(RegistrationFormType::class, $user);
        $registrationForm->handleRequest($request);

        $csvForm = $this->createForm(ImportCsvType::class);
        $csvForm->handleRequest($request);

        if ($csvForm->isSubmitted() && $csvForm->isValid()) {
            $csvFile = $csvForm->get('file')->getData();

            $entityManager->persist($csvFile);
            $entityManager->flush();
            $message = 'Nouveaux utilisateurs importés!';

            return $this->redirectToRoute('sortie_accueil', [
                'message' => $message,
            ]);
        }

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $user->setPseudo('pseudo');
            $user->setAdministrateur(0);
            $user->setActif(0);
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

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'csvForm' => $csvForm->createView()
        ]);
    }

}