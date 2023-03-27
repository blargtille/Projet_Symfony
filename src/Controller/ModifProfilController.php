<?php

namespace App\Controller;



use App\Entity\User;
use App\Form\ModifyUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModifProfilController extends AbstractController
{
    //AJOUTER UN ROLE UTILISATEUR
    #[Route('/modifUser', name: 'main_modifUser')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
    // modifier le user qui est connecté

        // recuperer l'id du user connecté
        $user = $this->getUser();

        // ne pas créer d'utilisateur si il n'est pas connecté

        $userForm = $this->createForm(ModifyUserType::class, $user);

        // hydrade l'instance wish avec les données de la request
        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){

            // enregistre le souhait en BDD
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
