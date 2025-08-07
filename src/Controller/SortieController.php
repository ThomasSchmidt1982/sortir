<?php

namespace App\Controller;

use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie/{id}', name: 'sortie_affichage', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(
        $id,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository
    ):Response
    {
        $sortie = $sortieRepository->find($id);
        $participant = $participantRepository->findAll();
        return $this->render('sortie/affichage.html.twig', [
            'sortie' => $sortie,
            'participant' => $participant,

        ]);

    }


    #[Route('/', name: 'sortie_list', methods: ['GET', 'POST'])]
    public function sortie(
        Request          $request,
        SortieRepository $sortieRepository,
        CampusRepository $campusRepository,
    ): Response
    {
        // récup l'utilisateur connecté
        $user = $this->getuser();
        if (!$user) {
            // Rediriger vers la page de connexion si l'utilisateur n'est pas authentifié
            return $this->redirectToRoute('app_login');
        }

        //recup tous les campus pour le select
        $campusList = $campusRepository->findAll();
        // Récupérer le campus de l'utilisateur connecté
        $userCampus = $user->getCampus();

        $filters =[
            'campus' => $request->request->get('campus', $userCampus?->getId()),
            'showFinished' => $request->request->get('showFinished', false), // Décochée = false
            'showNotRegistered' => $request->request->get('showNotRegistered', false),
            'showRegistered'=> $request->request->get('showRegistered', false),
            'showIOrganize' => $request->request->get('showIOrganize', false),
            'searchTerm' => $request->request->get('searchTerm', ''),
            'startDate' => $request->request->get('startDate', ''),
            'endDate' => $request->request->get('endDate', ''),
        ];


        $sorties = $sortieRepository->findByFilters($filters, $user);

        return $this->render('sortie/sortie.html.twig', [
            'sorties' => $sorties,
            'user' => $user,
            'campusList' => $campusList,
            'selectedFilters' => $filters,
        ]);
    }
}
