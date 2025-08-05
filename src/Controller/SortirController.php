<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortirController extends AbstractController
{
    #[Route('/', name: 'sortir_list')]
    public function sortir(): Response
    {
        return $this->render('sortir/sortir.html.twig', [
            'controller_name' => 'SortirController',
        ]);
    }
}
