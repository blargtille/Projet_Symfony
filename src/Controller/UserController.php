<?php

namespace App\Controller;



use App\Form\ModifyUserType;
use App\notification\Sender;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[Route('/users', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('/modifierProfil', name: 'modifierProfil')]
    public function modifUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository): Response
    {

        $user = $this->getUser();
        $userForm = $this->createForm(ModifyUserType::class, $user);
        $userForm->handleRequest($request);
        $newFilename = $user->getPhoto();

        if($userForm->isSubmitted() && $userForm->isValid()){
            $pseudo = $userForm->get('pseudo')->getData();
            $pseudoUser = $userRepository->findOneBy(['pseudo' => $pseudo]);
            if ($pseudoUser && $pseudoUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'Ce pseudo est déjà utilisé par un autre utilisateur.');
                return $this->redirectToRoute('user_modifierProfil');
            }

            $photo = $userForm->get('photo')->getData();
            if ($photo){
                $destination = $this->getParameter('kernel.project_dir').'/public/img/imgProfil';

                $newFilename = uniqid().'.'.$photo->guessExtension();

                $photo->move(
                    $destination,
                    $newFilename
                );

                $user->setPhoto($newFilename);
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos modifications ont bien été enregistrées!');

            return $this->render('main/modifUser.html.twig', [
                'userForm' => $userForm->createView(),
                'photo' => $newFilename
            ]);

        }

        return $this->render('main/modifUser.html.twig', [
            'userForm' => $userForm->createView(),
            'photo' => $newFilename

        ]);
    }

    #[Route('/detailsUser/{id}', name: 'detail')]
    public function detailsUser(int $id, UserRepository $userRepository, SortieRepository $sortieRepository ): Response{

        $user = $userRepository->find($id);
     //   $sortie = $sortieRepository->find($id);
            if(!$user){
                throw $this->createNotFoundException('Utilisateur non trouvé');
            }

        return $this->render('main/detailsUser.html.twig', [
            'user' => $user,
            //'sortie'=>$sortie
        ]);
    }
}
