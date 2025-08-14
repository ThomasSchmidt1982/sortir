<?php
namespace App\Security;

use App\Entity\Sortie;
use App\Entity\Participant;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SortieSecurity
{
    public function __construct(private Security $security) {}

    public function canInscrire(Sortie $sortie): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof Participant) {
            throw new AccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }

        if (strtolower($sortie->getEtat()->getLibelle()) !== 'ouverte') {
            throw new AccessDeniedException('La sortie n’est pas ouverte aux inscriptions.');
        }

        if ($sortie->getDateLimiteInscription() < new \DateTimeImmutable()) {
            throw new AccessDeniedException('La date limite d’inscription est dépassée.');
        }

        if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionMax()) {
            throw new AccessDeniedException('Plus aucune place disponible.');
        }

        if ($sortie->getParticipants()->contains($user)) {
            throw new AccessDeniedException('Vous êtes déjà inscrit à cette sortie.');
        }
    }

    public function canDesister(Sortie $sortie): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof Participant) {
            throw new AccessDeniedException("Vous devez être connecté pour vous désister.");
        }

        if (!$sortie->getParticipants()->contains($user)) {
            throw new AccessDeniedException("Vous n'êtes pas inscrit à cette sortie.");
        }

        $now = new \DateTimeImmutable();
        if ($sortie->getDateHeureDebut() <= $now) {
            throw new AccessDeniedException("Vous ne pouvez plus vous désister après le début de la sortie.");
        }

        // Interdiction si la sortie est clôturée
        if ($sortie->getEtat()->getLibelle() === 'Clôturée') {
            throw new AccessDeniedException("Impossible de se désister d'une sortie clôturée.");
        }

        // Interdiction si la sortie est annulée
        if ($sortie->getEtat()->getLibelle() === 'Annulée') {
            throw new AccessDeniedException("Impossible de se désister d'une sortie annulée.");
        }
    }

    public function canSupprimer(Sortie $sortie): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof Participant) {
            throw new AccessDeniedException("Vous devez être connecté pour supprimer une sortie.");
        }

        // Organisateur ou admin
        if ($sortie->getOrganisateur() !== $user && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("Vous n'êtes pas autorisé à supprimer cette sortie.");
        }

        // Pas déjà commencée ou terminée
        if ($sortie->getDateHeureDebut() < new \DateTimeImmutable() || $sortie->getEtat()->getLibelle() === 'Terminée') {
            throw new AccessDeniedException("Impossible de supprimer une sortie déjà commencée ou terminée.");
        }
    }

    public function canModifier(Sortie $sortie): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof Participant) {
            throw new AccessDeniedException("Vous devez être connecté pour modifier une sortie.");
        }

        if ($sortie->getOrganisateur() !== $user && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("Vous n'êtes pas autorisé à modifier cette sortie.");
        }

        if ($sortie->getDateHeureDebut() < new \DateTimeImmutable()) {
            throw new AccessDeniedException("Impossible de modifier une sortie déjà commencée ou terminée.");
        }

    }


}
