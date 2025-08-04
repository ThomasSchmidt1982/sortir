<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: "participant", uniqueConstraints: [
    new ORM\UniqueConstraint(name: "unique_mail_pseudo", columns: ["mail", "pseudo"])
])]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[Assert\NotBlank(message: "Veuillez renseigner le champ nom")]
    #[Assert\Range(
        min:2,
        max:100,
        minmessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxmessage: "Le nom ne doit pas contenir plus de {{ limit }} caractères",
    )]
    #[ORM\Column(length: 100)]
    private ?string $nom = null;


    #[Assert\NotBlank(message: "Veuillez renseigner le champ prénom")]
    #[Assert\Range(
        min:2,
        max:100,
        minmessage: "Le prénom doit contenir au moins {{ limit }} caractères",
        maxmessage: "Le prénom ne doit pas contenir plus de {{ limit }} caractères",
    )]
    #[ORM\Column(length: 100)]
    private ?string $prenom = null;


    #[Assert\NotBlank(message: "Veuillez renseigner le champ pseudo")]
    #[Assert\Range(
        min:2,
        max:100,
        minmessage: "Le pseudo doit contenir au moins {{ limit }} caractères",
        maxmessage: "Le pseudo ne doit pas contenir plus de {{ limit }} caractères",
    )]
    #[ORM\Column(length: 100, unique:true)]
    private ?string $pseudo = null;


    #[Assert\Length(max:10, maxmessage: "Le numéro de téléphone doit contenir au maximum {{ limit }} caractères")]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telephone = null;

    #[Assert\NotBlank(message: "Veuillez renseigner le champ de l'adresse email")]
    #[Assert\Length(max:180, maxmessage: "L' adresse mail ne peut dépasser {{ limit }} caractères")]
    #[Assert\Email(message: "Veuillez renseigner une adresse email valide")]
    #[ORM\Column(length: 180, unique:true)]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $motPasse = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $estInscrit;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur')]
    private Collection $organisateur;

    public function __construct()
    {
        $this->estInscrit = new ArrayCollection();
        $this->organisateur = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    /* public function getMotPasse(): ?string
    {
        return $this->motPasse;
    } */

    public function setMotPasse(string $motPasse): static
    {
        $this->motPasse = $motPasse;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): static
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->mail;
    }

    public function getPassword(): ?string
    {
        return $this->motPasse;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getEstInscrit(): Collection
    {
        return $this->estInscrit;
    }

    public function addEstInscrit(Sortie $estInscrit): static
    {
        if (!$this->estInscrit->contains($estInscrit)) {
            $this->estInscrit->add($estInscrit);
        }

        return $this;
    }

    public function removeEstInscrit(Sortie $estInscrit): static
    {
        $this->estInscrit->removeElement($estInscrit);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getOrganisateur(): Collection
    {
        return $this->organisateur;
    }

    public function addOrganisateur(Sortie $organisateur): static
    {
        if (!$this->organisateur->contains($organisateur)) {
            $this->organisateur->add($organisateur);
            $organisateur->setOrganisateur($this);
        }

        return $this;
    }

    public function removeOrganisateur(Sortie $organisateur): static
    {
        if ($this->organisateur->removeElement($organisateur)) {
            // set the owning side to null (unless already changed)
            if ($organisateur->getOrganisateur() === $this) {
                $organisateur->setOrganisateur(null);
            }
        }

        return $this;
    }
}
