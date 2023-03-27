<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModifProfilController extends AbstractController
{
    #[Route('/modifUser', name: 'main_modifUser')]
    public function index(): Response
    {
        $utilisateurModifié= 1;
        return $this->render('main/modifUser.html.twig',[
            'utilisateurModifié' => $utilisateurModifié
        ]);
    }
}
