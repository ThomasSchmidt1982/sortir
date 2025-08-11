<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class SortieService
{
    public function __construct(EntityManagerInterface $em,
                                EtatRepository $etatRepository,
                                SortieRepository $sortieRepository,
    )
    {
        $this->em = $em;
        $this->etatRepository = $etatRepository;
        $this->sortieRepository = $sortieRepository;
    }


    public function setEtatEnCreation(Sortie $sortie):void{
        $etatEnCreation = $this->etatRepository->findOneBy(['libelle' => 'En création']);
        $sortie->setEtat($etatEnCreation);
        $this->em->flush();
    }

    public function setEtatOuverte(Sortie $sortie):void{
        $etatOuverte = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $sortie->setEtat($etatOuverte);
        $this->em->flush();
    }

    public function setEtatEnCours(Sortie $sortie):void{
        $etatEnCours = $this->etatRepository->findOneBy(['libelle' => 'En cours']);
        $sortie->setEtat($etatEnCours);
        $this->em->flush();
    }

    public function setEtatCloturee(Sortie $sortie):void{
        $etatCloturee = $this->etatRepository->findOneBy(['libelle' => 'Cloturée']);
        $sortie->setEtat($etatCloturee);
        $this->em->flush();
    }

    public function setEtatAnnulee(Sortie $sortie):void{
        $etatAnnulee = $this->etatRepository->findOneBy(['libelle' => 'Annulée']);
        $sortie->setEtat($etatAnnulee);
        $this->em->flush();
    }

    public function setEtatTerminee(Sortie $sortie):void{
        $etatTerminee = $this->etatRepository->findOneBy(['libelle' => 'Terminée']);
        $sortie->setEtat($etatTerminee);
        $this->em->flush();
    }

    public function setEtatHistorisee(Sortie $sortie):void{
        $etatHistorisee = $this->etatRepository->findOneBy(['libelle' => 'Historisée']);
        $sortie->setEtat($etatHistorisee);
        $this->em->flush();
    }

    public function getNbParticipants(Sortie $sortie): int{
        return $sortie->getParticipants()->count();
    }

    public function historiserSorties():void
    {
        // Calculer la date limite : aujourd'hui moins 1 mois
        $dateDepuisUnMois = new \DateTimeImmutable('-1 month');
        // Récupérer les sorties éligibles
        $sortiesAHistoriser = $this->sortieRepository->findSortiesAHistoriser($dateDepuisUnMois);
        if ($sortiesAHistoriser) { // Mettre à jour l'état des sorties
            foreach ($sortiesAHistoriser as $sortie) {
                $this->setEtatHistorisee($sortie);
            }
            $this->em->flush();
        }
    }

    public function terminerSorties():void
    {
        // Calculer la date limite : aujourd'hui moins 1 mois
        $dateDepuisUnMois = new \DateTimeImmutable('-1 month');
        $dateDHier = new \DateTimeImmutable('-1 day');
        // Récupérer les sorties éligibles
        $sortiesATerminer = $this->sortieRepository->findSortiesATerminer($dateDepuisUnMois, $dateDHier);
        if ($sortiesATerminer) { // Mettre à jour l'état des sorties
            foreach ($sortiesATerminer as $sortie) {
                $this->setEtatTerminee($sortie);
            }
            $this->em->flush();
        }
    }

    public function cloturerSorties():void
    {
        $currentDate = new \DateTimeImmutable('now');
        // filtrage date dépassé ou nb participant atteint
        $sortiesACloturer = $this->sortieRepository->findSortiesACloturer($currentDate);

        if ($sortiesACloturer) {
            foreach ($sortiesACloturer as $sortie) {
                $this->setEtatCloturee($sortie);
            }
            $this->em->flush();

        }

    }

    public function cloturerDateLimite(Sortie $sortie):void
    {

    }
}