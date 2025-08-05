<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipantController extends AbstractController
{
    #[Route('/afficher', name: 'participant_afficher')]
    public function read(Request $request): Response
    {
        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantType::class, $participant);

        return $this->render('participant/read.html.twig', [
            'participantForm' => $participantForm,
        ]);
    }


    #[Route('/modifier', name: 'participant_modifier', methods: ['GET', 'POST'])]
    public function update(Request $request, EntityManagerInterface $em): Response
    {
        $participant = $this->getUser();
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);
        if($participantForm->isSubmitted() && $participantForm->isValid()){
            $em->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        }

        return $this->render('participant/read.html.twig', [
            'participantForm' => $participantForm,
        ]);
    }
}
