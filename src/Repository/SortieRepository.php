<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Sortie::class);
        $this->em = $em;
    }

    public function updateEtatSortieDate(): void
    {
        $today = new \DateTimeImmutable();

        // 1. Récupérer l'état « Ouverte » et « Cloturée »
        $etatOuverte = $this->getEntityManager()
            ->getRepository(Etat::class)
            ->findOneBy(['libelle' => 'Ouverte']);

        $etatCloturee = $this->getEntityManager()
            ->getRepository(Etat::class)
            ->findOneBy(['libelle' => 'Cloturée']);

        // 2. Réouvrir les sorties « Cloturée » si la date limite d'inscription est repoussée
        $sortiesCloturees = $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->andWhere('e.libelle = :cloturee')
            ->andWhere('s.dateLimiteInscription >= :today') // Si date limite est dans le futur
            ->setParameter('cloturee', 'Cloturée')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        foreach ($sortiesCloturees as $sortie) {
            $sortie->setEtat($etatOuverte); // Réouverture
        }

        // 3. Clôturer les sorties « Ouverte » si la date limite d'inscription est dépassée
        $sortiesOuvertes = $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->andWhere('e.libelle = :ouverte')
            ->andWhere('s.dateLimiteInscription < :today') // Si date limite est dépassée
            ->setParameter('ouverte', 'Ouverte')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        foreach ($sortiesOuvertes as $sortie) {
            $sortie->setEtat($etatCloturee); // Clôture
        }

        // 4. Appliquer tous les changements
        $this->getEntityManager()->flush();
    }

    public function findByCampusOrganisateur(int $campusId): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.organisateur', 'o') // Join avec l'organisateur
            ->join('o.campus', 'c') // Join avec le campus
            ->andWhere('c.id = :campusId')
            ->setParameter('campusId', $campusId)
            ->getQuery()
            ->getResult();
    }

    public function findByCampusOfUser(int $campusId): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.organisateur', 'o') // Relation avec l'organisateur
            ->join('o.campus', 'c') // Relation avec le campus
            ->andWhere('c.id = :campusId') // Filtrer par le campus
            ->setParameter('campusId', $campusId)
            ->orderBy('s.dateHeureDebut', 'DESC') // Trier par date de début décroissante
            ->getQuery()
            ->getResult();
    }

    public function findByFilters(array $filters, $user): array
    {
        $qb = $this->createQueryBuilder('s'); // Alias pour la table Sortie

        // Joindre les entités nécessaires
        $qb->join('s.etat', 'etat')
            ->join('s.organisateur', 'organisateur')
            ->leftJoin('organisateur.campus', 'campus'); // Jointure avec Campus pour les filtres liés au campus

        // 1. Filtre par campus
        if (!empty($filters['campus'])) {
            $qb->andWhere('campus.id = :campusId')
                ->setParameter('campusId', $filters['campus']);
        }

        // 2. Filtre par mot-clé (nom de sortie)
        if (!empty($filters['searchTerm'])) {
            $qb->andWhere('s.nom LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $filters['searchTerm'] . '%');
        }

        // 3. Filtre par plages de dates (début et fin)
        if (!empty($filters['startDate'])) {
            $qb->andWhere('s.dateHeureDebut >= :startDate')
                ->setParameter('startDate', new \DateTimeImmutable($filters['startDate']));
        }

        if (!empty($filters['endDate'])) {
            $qb->andWhere('s.dateHeureDebut <= :endDate')
                ->setParameter('endDate', new \DateTimeImmutable($filters['endDate']));
        }

        // 4. Inclure les sorties terminées (checkbox cochée)
        if (!empty($filters['showFinished'])) {
            $qb->andWhere('etat.libelle = :finished')
                ->setParameter('finished', 'Terminée');
        }

        // 5. Exclure les sorties auxquelles l'utilisateur est inscrit (checkbox cochée)
        if (!empty($filters['showNotRegistered'])) {
            $qb->andWhere(':currentUser NOT MEMBER OF s.participants')
                ->setParameter('currentUser', $user);
        }

        // 6. Inclure uniquement les sorties où l'utilisateur est inscrit (checkbox cochée)
        if (!empty($filters['showRegistered'])) {
            $qb->andWhere(':currentUser MEMBER OF s.participants')
                ->setParameter('currentUser', $user);
        }

        // 7. Inclure uniquement les sorties où l'utilisateur est l'organisateur (checkbox cochée)
        if (!empty($filters['showIOrganize'])) {
            $qb->andWhere('s.organisateur = :currentUser')
                ->setParameter('currentUser', $user);
        }

        // Trier par date de début décroissante
        $qb->orderBy('s.dateHeureDebut', 'DESC');

        // Retourner les résultats sous forme de tableau
        return $qb->getQuery()->getResult();
    }

    public function findSortiesAHistoriser(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->andWhere('s.dateHeureDebut < :date')
            ->andWhere('e.libelle != :etatHistorisee') // On exclut celles déjà "Historisée"
            ->setParameter('date', $date)
            ->setParameter('etatHistorisee', 'Historisée')
            ->getQuery()
            ->getResult();
    }

    public function findSortiesATerminer(\DateTimeImmutable $dateDepuisUnMois, \DateTimeImmutable $datedHier): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->andWhere('s.dateHeureDebut < :datedHier')
            ->andWhere('s.dateHeureDebut > :dateDepuisUnMois')
            ->andWhere('e.libelle != :etatTerminee') // On exclut celles déjà "Terminée"
            ->setParameter('datedHier', $datedHier)
            ->setParameter('dateDepuisUnMois', $dateDepuisUnMois)
            ->setParameter('etatTerminee', 'Terminée')
            ->getQuery()
            ->getResult();
    }

    public function findNbParticipant(int $sortieId): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(p.id)') // Compter le nombre de participants
            ->join('s.participants', 'p') // Faire la jointure avec les participants
            ->where('s.id = :sortieId') // Filtrer par l'ID de la sortie
            ->setParameter('sortieId', $sortieId) // Assigner la valeur de sortie
            ->getQuery()
            ->getSingleScalarResult(); // Retourner le nombre de participants
    }

    public function findSortiesACloturer(\DateTimeImmutable $currentDate): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e') // Join les états
            ->leftJoin('s.participants', 'p') // Join les participants pour compter leurs inscriptions
            ->andWhere('e.libelle IN (:etats)')
            ->setParameter('etats', ['Ouverte'])
            ->andWhere('s.dateLimiteInscription < :currentDate OR SIZE(s.participants) >= s.nbInscriptionMax')
            ->setParameter('currentDate', $currentDate) // Date actuelle
            ->groupBy('s.id') // Nécessaire pour groupes lors des jointures avec COUNT ou SIZE
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

}
