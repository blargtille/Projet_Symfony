<?php

namespace App\Controller;



use App\Entity\User;
use App\Form\ModifyUserType;
use App\notification\Sender;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class ModifProfilController extends AbstractController
{
    //AJOUTER UN ROLE UTILISATEUR
    #[Route('/modifUser', name: 'main_modifUser')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator,
                             AppAuthenticator $authenticator): Response
    {
    // modifier le user qui est connecté

        $user = $this->getUser();
        $userForm = $this->createForm(ModifyUserType::class, $user);
        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){

            $user->setRoles(['ROLE_USER']);

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Idea successfully added!');

            return $this->redirectToRoute('main_modifUser', ['id' => $user->getId()]);

        }

        return $this->render('main/modifUser.html.twig', [
            'userForm' => $userForm
        ]);

    }
}
