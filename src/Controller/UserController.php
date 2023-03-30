<?php

namespace App\Controller;



use App\Entity\User;
use App\Form\ModifyUserType;
use App\notification\Sender;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class UserController extends AbstractController
{
    #[Route('/modifUser', name: 'main_modifUser')]
    public function modifUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator,
                             AppAuthenticator $authenticator): Response
    {

        $user = $this->getUser();
        $userForm = $this->createForm(ModifyUserType::class, $user);
        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){


            $user->setRoles(['ROLE_USER']);

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Idea successfully added!');

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('main/modifUser.html.twig', [
            'userForm' => $userForm->createView(),
        ]);

    }
    #[Route('main/detailsUser/{id}', name: 'main_detailsUser')]
    public function detailsUser(int $id, UserRepository $userRepository): Response{

        $user = $userRepository->find($id);

            if(!$user){
                throw $this->createNotFoundException('User does not exists');
            }

        return $this->render('main/detailsUser.html.twig', [
            'user' => $user
        ]);
    }
}
