<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[Assert\NotBlank(message: "Veuillez fournir un nom pour la sortie")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Le nom de la sortie doit avoir au moins {{ limit }} caractètres",
        maxMessage: "Le nom de la sortie doit avoir au maximum {{ limit }} caractères"
    )]
    #[ORM\Column(length: 100)]
    private ?string $nom = null;


    #[Assert\NotBlank(message: "Veuillez fournir une heure de début")]
    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeureDebut = null;


    #[ORM\Column(nullable: true)]
    private ?int $duree = null;


    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateLimiteInscription = null;


    #[ORM\Column(nullable: true)]
    private ?int $nbInscriptionMax = null;


    #[Assert\Length(
        min: 5,
        max: 2000,
        minMessage: "Le champ d'information doit avoir au moins {{ limit }} caractètres",
        maxMessage: "Le champ d'information doit avoir au maximum {{ limit }} caractères"
    )]    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $infosSortie = null;

    #[Assert\NotBlank(message: "Veuillez préciser l'état de la sortie")]
    #[ORM\Column(length: 100)]
    private ?string $etat = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(?\DateTimeImmutable $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(?int $nbInscriptionMax): static
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }
}
