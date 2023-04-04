<?php

namespace App\Controller;

use App\Form\ImportCsvType;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CsvController extends AbstractController
{
    #[Route('/csv', name: 'app_csv')]
    public function importCsv(EntityManagerInterface $entityManager, Request $request)
    {
        $form = $this->createForm(ImportCsvType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('file')->getData();

            // Import CSV data into database

            $message = 'Imported successfully!';

            return $this->render('registration/register.html.twig', [
                'csvForm' => $form->createView(),
                'message' => $message,
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'csvForm' => $form->createView(),
        ]);
    }
}
