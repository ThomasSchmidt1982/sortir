<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\ManyToMany(targetEntity: Participant::class, mappedBy: 'estInscrit')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'organisateur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function isAvailable(Participant $user, \DateTimeImmutable $currentDateTime): bool
    {
        // Vérifier que l'état est défini et qu'il est bien "Ouverte"
        if (!$this->etat || $this->etat->getLibelle() !== 'Ouverte') {
            return false;
        }

        // Vérifier que la date limite d'inscription n'est pas passée
        if (!$this->dateLimiteInscription || $this->dateLimiteInscription < $currentDateTime) {
            return false;
        }

        // Vérifier que l'utilisateur n'est pas déjà inscrit
        if ($this->participants->contains($user)) {
            return false;
        }

        // Toutes les conditions sont remplies, la sortie est disponible pour l'utilisateur
        return true;
    }

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

    public function getEtat(): Etat
    {
        return $this->etat;
    }

    public function setEtat(Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->addEstInscrit($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeEstInscrit($this);
        }

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }
}
