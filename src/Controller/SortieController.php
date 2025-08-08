<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{

    #[Route('/sortie/annuler', name: 'sortie_annuler', methods: ['GET', 'POST'])]
    public function annuler(
        Request $request,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $em,
    ): Response {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        $sortie = $sortieRepository->find($sortieId);
        $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $etatSortie = $sortie->getEtat();
        $today = new \DateTimeImmutable();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();

        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable.');
            return $this->redirectToRoute('sortie_list');
        }
        if (!$sortieId) {
            throw $this->createNotFoundException('L\'ID de la sortie est requis.');
        }

        // Création du formulaire
        $annulerForm = $this->createForm(AnnulationType::class, $sortie);
        $annulerForm->handleRequest($request);

        if ($annulerForm->isSubmitted() && $annulerForm->isValid()) {
            if ($dateLimiteInscription >= $today && $etatSortie === $etatOuverte) {
                $sortie = $sortieRepository->find($sortieId);//recup l'état Annulée
                $etatAnnulee = $etatRepository->findOneBy(['libelle' => 'Annulée']);// maj de l'état en "Annulée"
                $sortie->setEtat($etatAnnulee);// sauvegarde des changement en bdd
                $em->flush();

                $this->addFlash("success", "la sortie a été annulée avec succès !");

                return $this->redirectToRoute('sortie_list');
            }
        }

        // Affichage du formulaire en cas de problème
        return $this->render('sortie/annuler.html.twig', [
            'annulerForm' => $annulerForm->createView(),
            'sortie' => $sortie,
        ]);

    }


    #[Route('/sortie/desister', name: 'sortie_desister', methods: ['GET', 'POST'])]
    public function desister(
        Request $request,
        SortieRepository $sortieRepository,
        EntityManagerInterface $em,
    ): Response {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        // recup sortie dans la bdd
        $sortie = $sortieRepository->find($sortieId);

        $user = $this->getUser();


        // si la date cloture < aujourdhui (Possibilité de se désister jusqu'à l'heure de la sortie)
        $today = new \DateTimeImmutable();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();
        if($dateLimiteInscription >= $today) {
            // enlever l'utilisateur aux participants de la sortie
            $sortie->removeParticipant($user);

            // Sauvegarder les modifications en base
            $em->flush();

            // Message de confirmation
            $this->addFlash('success', 'Vous vous êtes désinscrit avec succès.');
        }

        return $this->redirectToRoute('sortie_list');
    }


    #[Route('/sortie/inscrire', name: 'sortie_inscrire', methods: ['GET', 'POST'])]
    public function inscrire(
        Request $request,
        SortieRepository $sortieRepository,
        EntityManagerInterface $em,
    ): Response {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        // recup sortie dans la bdd
        $sortie = $sortieRepository->find($sortieId);

        $user = $this->getUser();

        // Ajouter l'utilisateur aux participants de la sortie
        $sortie->addParticipant($user);

        // Sauvegarder les modifications en base
        $em->flush();

            // Message de confirmation
            $this->addFlash('success', 'Vous vous êtes inscrit avec succès.');

        return $this->redirectToRoute('sortie_list');
    }


    #[Route('/sortie/supprimer', name: 'sortie_supprimer', methods: ['GET', 'POST'])]
    public function supprimer(
        Request $request,
        SortieRepository $sortieRepository,
        EntityManagerInterface $em,
    ): Response {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        $sortie = $sortieRepository->find($sortieId);

        // Supprimer la sortie
        $em->remove($sortie);
        $em->flush();

        // Ajouter un message de confirmation
        $this->addFlash('success', 'La sortie a été supprimée avec succès.');

        // Redirection vers la liste des sorties
        return $this->redirectToRoute('sortie_list');

    }


    #[Route('/sortie/modifier', name: 'sortie_modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Request $request,
        SortieRepository $sortieRepository,
        EntityManagerInterface $em,
    ): Response {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');
        $sortie = $sortieRepository->find($sortieId);

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
// todo verifier si user est organisateur + redirect
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $em->flush();
            // Message de confirmation
            $this->addFlash('success', 'La sortie a été modifiée avec succès.');

            return $this->redirectToRoute('sortie_list');
        }
        return $this->render('sortie/modifier.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie' => $sortie,
        ]);
    }


    #[Route('/sortie/publier', name: 'sortie_publier', methods: ['POST'])]
    public function publier(
        Request $request,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $em,
    ): Response {
        // récup ID de la sortie depuis front
        $sortieId = $request->request->get('sortie_id');

        if (!$sortieId) {
            throw $this->createNotFoundException('L\'ID de la sortie est requis.');
        }

        $sortie = $sortieRepository->find($sortieId);
        //recup l'état ouverte
        $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        // maj de l'état en "Ouverte"
        $sortie->setEtat($etatOuverte);
        // sauvegarde des changement en bdd
        $em->flush();

        $this->addFlash("success", "la sortie a été publiée avec succès !");

        return $this->redirectToRoute('sortie_list');
    }


    #[Route('/sortie/creer', name: 'sortie_creer', methods: ['GET', 'POST'])]
    public function creer(
        Request $request,
        CampusRepository $campusRepository,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $em,
    ): Response{

        //créé une instance de Sortie
        $sortie = new Sortie();
        //créé le formulaire
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        //traiter le formulaire
        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            // defini l'état
            $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'En création']));
            // defini l'organisateur est le user connecté
            $sortie->setOrganisateur($user=$this->getUser());
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "la sortie a été créée avec succès !");
        }

        return $this->render('sortie/creer.html.twig', [
            "sortieForm" => $sortieForm,
            "sortie" => $sortie,
        ]);


    }


    #[Route('/sortie/{id}', name: 'sortie_affichage', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(
        $id,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository
    ):Response
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
        // creation de la date du jour
        $currentDateTime = new \DateTimeImmutable();

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
            'currentDateTime' => $currentDateTime,
        ]);
    }
}
