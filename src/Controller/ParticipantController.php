<?php

namespace App\Controller;

use App\Entity\Participant;
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

    #[Route('/afficher', name: 'participant_afficher', methods: ['GET'])]
    public function show(ParticipantRepository $participantRepository):Response
    {
        $participant = $participantRepository->find(32);

        return $this->render('participant/show.html.twig', [
            "participant" => $participant,
        ]);

    }




    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/modifier', name: 'participant_modifier', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $participant = $this->getUser();
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);
        if($participantForm->isSubmitted() && $participantForm->isValid()){

            $participant->setMotPasse($passwordHasher->hashPassword($participant, $participant->getMotPasse()));

            $em->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->render('sortie/sortie.html.twig');
        }

        return $this->render('participant/edit.html.twig', [
            'participantForm' => $participantForm,
        ]);
    }
}
