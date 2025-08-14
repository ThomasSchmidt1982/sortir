<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Security\SortieSecurity;
use App\Service\SortieService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SortieController extends AbstractController
{

    #[isGranted('ROLE_USER')]
    #[Route('/sortie/annuler', name: 'sortie_annuler', methods: ['GET', 'POST'])]
    public function annuler(
        Request          $request,
        SortieRepository $sortieRepository,
        EtatRepository   $etatRepository,
        SortieService    $sortieService,
    ): Response
    {
        // récup ID de la sortie depuis front (POST)
        $sortieId = $request->request->get('sortie_id');
        if (!$sortieId) {
            throw $this->createNotFoundException("L'ID de la sortie est requis.");
        }
        $sortie = $sortieRepository->find($sortieId);

        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable.');
            return $this->redirectToRoute('sortie_list');
        }
        $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $etatSortie = $sortie->getEtat();
        $today = new DateTimeImmutable();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();
        // Création du formulaire
        $annulerForm = $this->createForm(AnnulationType::class, $sortie);
        $annulerForm->handleRequest($request);
        if ($annulerForm->isSubmitted() && $annulerForm->isValid()) {
            if ($dateLimiteInscription >= $today && $etatSortie === $etatOuverte) {
                $sortieService->setEtatAnnulee($sortie);

                $this->addFlash("success", "la sortie a été annulée avec succès !");
                return $this->redirectToRoute('sortie_list');
            } else {
                $this->addFlash("warning", "Impossible d'annuler la sortie car la date limite est passée ou l'état n'est pas 'Ouverte'.");
            }
        }
        return $this->render('sortie/annuler.html.twig', [
            'annulerForm' => $annulerForm->createView(),
            'sortie' => $sortie,
        ]);
    }


    #[isGranted('ROLE_USER')]
    #[Route('/sortie/inscrire', name: 'sortie_inscrire', methods: ['POST'])]
    public function inscrire(
        Request                $request,
        SortieRepository       $sortieRepository,
        SortieService          $sortieService,
        EntityManagerInterface $em,
        SortieSecurity         $sortieSecurity
    ): Response
    {
        // update des etats si inscriptions > NBplaces et/ou date limite dépassée
        $sortieService->cloturerSorties();
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        // puis recup sortie dans la bdd
        $sortie = $sortieRepository->find($sortieId);

        if (!$sortie) {
            throw $this->createNotFoundException("Sortie introuvable.");
        }
        try {
            $sortieSecurity->canInscrire($sortie);
        } catch (AccessDeniedException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('sortie_list');
        }

        $user = $this->getUser();
        $sortie->addParticipant($user);
        $em->flush();
        $this->addFlash('success', 'Vous vous êtes inscrit avec succès.');

        return $this->redirectToRoute('sortie_list');
    }


    #[isGranted('ROLE_USER')]
    #[Route('/sortie/desister', name: 'sortie_desister', methods: ['POST'])]
    public function desister(
        Request                $request,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $em,
        SortieSecurity         $sortieSecurity,
    ): Response
    {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        // recup sortie dans la bdd
        $sortie = $sortieRepository->find($sortieId);
        if (!$sortie) {
            throw $this->createNotFoundException("Sortie introuvable.");
        }

        try {
            $sortieSecurity->canDesister($sortie);
        } catch (AccessDeniedException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('sortie_list');
        }

        $user = $this->getUser();
        $sortie->removeParticipant($user);
        $em->flush();
        $this->addFlash('success', 'Vous vous êtes désinscrit avec succès.');
        return $this->redirectToRoute('sortie_list');
    }


    #[isGranted('ROLE_USER')]
    #[Route('/sortie/supprimer', name: 'sortie_supprimer', methods: ['GET', 'POST'])]
    public function supprimer(
        Request                $request,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $em,
        SortieSecurity         $sortieSecurity,
    ): Response
    {
        // Récupération et validation de l'ID
        $sortieId = $request->request->get('sortie_id');
        if (!$sortieId) {
            throw $this->createNotFoundException("ID de sortie manquant.");
        }
        $sortie = $sortieRepository->find($sortieId);
        if (!$sortie) {
            throw $this->createNotFoundException("Sortie introuvable.");
        }

        try {
            $sortieSecurity->canSupprimer($sortie);
        } catch (AccessDeniedException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('sortie_list');
        }

        // Supprimer la sortie
        $em->remove($sortie);
        $em->flush();
        $this->addFlash('success', 'La sortie a été supprimée avec succès.');
        return $this->redirectToRoute('sortie_list');
    }


    #[isGranted('ROLE_USER')]
    #[Route('/sortie/modifier', name: 'sortie_modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Request                $request,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $em,
        SortieSecurity         $sortieSecurity,
    ): Response
    {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        $sortie = $sortieRepository->find($sortieId);
        if (!$sortie) {
            throw $this->createNotFoundException("Sortie introuvable.");
        }

        try {
            $sortieSecurity->canModifier($sortie);
        } catch (AccessDeniedException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('sortie_list');
        }

        //formulaire
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La sortie a été modifiée avec succès.');
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/modifier.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie' => $sortie,
        ]);
    }


    #[isGranted('ROLE_USER')]
    #[Route('/sortie/publier', name: 'sortie_publier', methods: ['POST'])]
    public function publier(
        Request          $request,
        SortieRepository $sortieRepository,
        SortieService    $sortieService,
    ): Response
    {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');

        if (!$sortieId) {
            throw $this->createNotFoundException('L\'ID de la sortie est requis.');
        }

        $sortie = $sortieRepository->find($sortieId);
        $sortieService->setEtatOuverte($sortie);
        $this->addFlash("success", "la sortie a été publiée avec succès !");

        return $this->redirectToRoute('sortie_list');
    }


    #[isGranted('ROLE_USER')]
    #[Route('/sortie/creer', name: 'sortie_creer', methods: ['GET', 'POST'])]
    public function creer(
        Request                $request,
        EntityManagerInterface $em,
        SortieService          $sortieService,
    ): Response
    {

        //créé une instance de Sortie
        $sortie = new Sortie();
        //créé le formulaire
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        //traiter le formulaire
        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof Participant) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour créer une sortie.');
            }
            // defini l'état
            $sortieService->setEtatEnCreation($sortie);
            // defini l'organisateur est le user connecté
            $sortie->setOrganisateur($user);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "la sortie a été créée avec succès !");
        }

        return $this->render('sortie/creer.html.twig', [
            "sortieForm" => $sortieForm,
            "sortie" => $sortie,
        ]);


    }


    #[isGranted('ROLE_USER')]
    #[Route('/sortie/{id}', name: 'sortie_affichage', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(
        $id,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository
    ): Response
    {
        $sortie = $sortieRepository->find($id);
        $participant = $participantRepository->findAll();

        // Créer le formulaire avec l'objet sortie
        $sortieForm = $this->createForm(SortieType::class, $sortie, [
            'disabled' => true // Désactiver les champs
        ]);


        return $this->render('sortie/affichage.html.twig', [
            'sortie' => $sortie,
            'participant' => $participant,
            'sortieForm' => $sortieForm->createView(),
        ]);
    }


    #[isGranted('ROLE_USER')]
    #[Route('/', name: 'sortie_list', methods: ['GET', 'POST'])]
    public function sortie(
        Request          $request,
        SortieRepository $sortieRepository,
        CampusRepository $campusRepository,
        SortieService    $sortieService,
    ): Response
    {
        // update des etats ouverte->Cloturée des sorties
        $sortieRepository->updateEtatSortieDate();
        // Terminer les sorties entre hier et moins d'un mois
        $sortieService->terminerSorties();
        // update des etats si inscriptions > NBplaces et/ou date limite dépassée
        $sortieService->cloturerSorties();
        // historiser les sorties de plus d'un mois
        $sortieService->historiserSorties();


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
        // creation de la date du jour
        $currentDateTime = new DateTimeImmutable();

        $filters = [
            'campus' => $request->request->get('campus', $userCampus?->getId()),
            'showFinished' => $request->request->get('showFinished', false), // Décochée = false
            'showNotRegistered' => $request->request->get('showNotRegistered', false),
            'showRegistered' => $request->request->get('showRegistered', false),
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
            'currentDateTime' => $currentDateTime,
        ]);
    }
}
