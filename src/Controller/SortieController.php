<?php

namespace App\Controller;

use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_list', methods: ['GET', 'POST'])]
    public function sortie(
        Request          $request,
        SortieRepository $sortieRepository,
        CampusRepository $campusRepository,
    ): Response
    {
        // récup l'utilisateur connecté
        $user = $this->getuser();

        //recup tous les campus pour le select
        $campusList = $campusRepository->findAll();

        //recup id du campus via Get
        $selectedCampusId = $request->request->get('campus');
        dump($selectedCampusId); // Vérifiez si une valeur est récupérée
        dump($request->query->all()); // Pour GET
        dump($request->request->all()); // Pour POST

        // Si un campus est sélectionné, filtrer les sorties par ce campus
        if ($selectedCampusId) {
            // Filtrer les sorties par le campus de l'organisateur (requête personnalisée)
            $sorties = $sortieRepository->findByCampusOrganisateur($selectedCampusId);
        } else {
            // Sinon récupérer toutes les sorties
            $sorties = $sortieRepository->findAll();
        }



        return $this->render('sortie/sortie.html.twig', [
            'sorties' => $sorties,
            'user' => $user,
            'campusList' => $campusList,
            'selectedCampus' => $selectedCampusId,
        ]);
    }
}
