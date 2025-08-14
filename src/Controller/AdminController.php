<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'app_admin')]
    public function administration(): Response
    {
        return $this->render('admin/administration.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin/campus', name: 'admin_campus')]
    public function campus(
        CampusRepository $campusRepository,
    ): Response
    {
        $campuses = $campusRepository->findAll();
        return $this->render('admin/campus.html.twig', [
            'campuses' => $campuses,
        ]);
    }


    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin/ville', name: 'admin_ville')]
    public function ville(
        VilleRepository $villeRepository,
    ): Response
    {
        $villes = $villeRepository->findAll();
        return $this->render('admin/ville.html.twig', [
            'villes' => $villes,
        ]);
    }


    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin/utilisateur', name: 'admin_utilisateur')]
    public function utilisateur(
        ParticipantRepository $participantRepository,
    ): Response
    {
        $participants = $participantRepository->findAll();
        return $this->render('admin/utilisateur.html.twig', [
            'participants' => $participants,
        ]);
    }


    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin/desactiver', name: 'admin_desactiver', methods: ['POST'])]
    public function desactiver(
        Request                $request,
        ParticipantRepository  $participantRepository,
        EntityManagerInterface $em,
    ): Response
    {
        $participantId = $request->request->get('participant_id');
        $participant = $participantRepository->find($participantId);
        if ($participant->isActif()) {
            $participant->setActif(false);
            $em->persist($participant);
            $em->flush();
            $this->addFlash('success', 'Le utilisateur ' . $participant->getPseudo() . ' a été désactivé avec succès.');
        } else {
            $this->addFlash('warning', 'Le participant  ' . $participant->getPseudo() . ' n\'a pas été désactivé.');
        }
        return $this->redirectToRoute('admin_utilisateur');
    }


    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin/activer', name: 'admin_activer', methods: ['POST'])]
    public function activer(
        Request                $request,
        ParticipantRepository  $participantRepository,
        EntityManagerInterface $em,
    ): Response
    {
        $participantId = $request->request->get('participant_id');
        $participant = $participantRepository->find($participantId);
        if (!$participant->isActif()) {
            $participant->setActif(true);
            $em->persist($participant);
            $em->flush();
            $this->addFlash('success', 'Le participant ' . $participant->getPseudo() . '  a été activé avec succès.');
        } else {
            $this->addFlash('warning', 'Le participant ' . $participant->getPseudo() . '  n\'a pas été activé.');
        }
        return $this->redirectToRoute('admin_utilisateur');
    }


    #[isGranted('ROLE_ADMIN')]
    #[Route('/admin/modifier', name: 'admin_modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Request                     $request,
        ParticipantRepository       $participantRepository,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $isAdmin = $this->getUser()->isAdministrateur();
        $participantId = $request->request->get('participant_id');
        $participant = $participantRepository->find($participantId);
        $form = $this->createForm(ParticipantType::class, $participant, [
            'isAdmin' => $isAdmin,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newMotPasse = $participant->getMotPasse();
            if (!empty($newMotPasse)) {
                $participant->setMotPasse($passwordHasher->hashPassword($participant, $newMotPasse));
            }
            $em->flush();
            $this->addFlash('success', 'Le profil de ' . $participant->getPseudo() . ' a été mis à jour avec succès.');

        return $this->redirectToRoute('admin_utilisateur');
        }

        return $this->render('admin/modifier.html.twig', [
            'participantForm' => $form,
            'participant' => $participant,
        ]);
    }

}
