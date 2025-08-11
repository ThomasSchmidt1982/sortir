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
        $qb = $this->createQueryBuilder('s'); // Alias pour Sortie


        // Joindre l'état pour filtrer par libellé
        $qb->join('s.etat', 'e');
        // Joindre l'organisateur pour les relations avec l'utilisateur connecté et potentiellement d'autres filtres
        $qb->join('s.organisateur', 'o');

        // Filtrer par campus uniquement si un ID est spécifié
        if (!empty($filters['campus'])) {
            $qb->join('o.campus', 'c')
                ->andWhere('c.id = :campusId')
                ->setParameter('campusId', $filters['campus']);
        }

        // Par défaut, exclure les sorties en état "En création" sauf si je suis l'organisateur
        $qb->andWhere('e.libelle != :etatEnCreation OR s.organisateur = :currentUser')
            ->setParameter('etatEnCreation', 'En création') // État à exclure sauf pour l'organisateur
            ->setParameter('currentUser', $user);


        if(!empty($filters['showFinished'])){
            $qb->join('s.etat', 'e') // Joindre l'état des sorties
            ->andWhere('e.libelle IN (:includedStates)')
                ->setParameter('includedStates', ['Terminée']);
        }

        // Exclure les sorties auxquelles l'utilisateur est déjà inscrit si "showNotRegistered" est activé
        if (!empty($filters['showNotRegistered'])) {
            $qb->andWhere(':currentUser NOT MEMBER OF s.participants') // Utilisateur non inscrit
            ->setParameter('currentUser', $user);
        }

        // Inclure uniquement les sorties auxquelles l'utilisateur est  inscrit si "showNotRegistered" est activé
        if (!empty($filters['showRegistered'])) {
            $qb->andWhere(':currentUser MEMBER OF s.participants') // Utilisateur inscrit
            ->setParameter('currentUser', $user);
        }

        // Inclure uniquement les sorties dont je sui l'organisateur si "showIOrganize" est activé
        if (!empty($filters['showIOrganize'])) {
            $qb->andWhere('s.organisateur = :currentUser')
                ->setParameter('currentUser', $user);
        }

        // recherche les sorties qui contiennent
        if (!empty($filters['searchTerm'])) {
            $qb->andWhere('s.nom LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $filters['searchTerm'] . '%');
        }

        /* // Filtrer par date de début
        if (!empty($filters['startDate'])) {
            $qb->andWhere('s.dateHeureDebut >= :startDate')
                ->setParameter('startDate', new \DateTime($filters['startDate']));
        }

        // Filtrer par date de fin
        if (!empty($filters['endDate'])) {
            $qb->andWhere('s.dateHeureDebut <= :endDate')
                ->setParameter('endDate', new \DateTime($filters['endDate']));
        } */

        // Filtrer par plage de dates (bornes)
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $qb->andWhere('s.dateHeureDebut BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', new \DateTime($filters['startDate']))
                ->setParameter('endDate', new \DateTime($filters['endDate']));
        }


        // Trier les résultats par date de début décroissante (facultatif, peut varier selon le besoin)
        $qb->orderBy('s.dateHeureDebut', 'DESC');

        // Retourner les résultats
        return $qb->getQuery()->getResult();
    }

    public function findSortiesAHistoriser(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->andWhere('s.dateHeureDebut < :date')
            ->setParameter('date', $date)
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
