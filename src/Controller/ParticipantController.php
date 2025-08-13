<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ParticipantController extends AbstractController
{

    #[Route('/participant/{id}', name: 'participant_affichage', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(
        $id,
        ParticipantRepository $participantRepository
    ):Response
    {
        $participant = $participantRepository->find($id);

        return $this->render('participant/affichage.html.twig', [
            "participant" => $participant,
        ]);

    }


    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/modifier', name: 'participant_modifier', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         EntityManagerInterface $em,
                         UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $participant = $this->getUser();
        $isAdmin = $participant->isAdministrateur();
        $participantForm = $this->createForm(ParticipantType::class, $participant, [
            'isAdmin' => $isAdmin,
        ]);
        $participantForm->handleRequest($request);
        if($participantForm->isSubmitted() && $participantForm->isValid()){
            if (!$participant) {
                throw $this->createNotFoundException('Participant introuvable.');
            }
            $newMotPasse = $participant->getMotPasse()->getData();
            if (!empty($newMotPasse)) {
                $participant->setMotPasse($passwordHasher->hashPassword($participant, $newMotPasse));
            }

            $em->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->render('sortie/sortie.html.twig');
        }

        return $this->render('participant/edit.html.twig', [
            'participantForm' => $participantForm,
        ]);
    }
}
