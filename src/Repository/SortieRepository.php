<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
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
    public function findByFilters(array $filters, $user): array
    {
        $qb = $this->createQueryBuilder('s'); // Alias pour Sortie

        // Joindre l'organisateur pour les relations avec l'utilisateur connecté et potentiellement d'autres filtres
        $qb->join('s.organisateur', 'o');


        // Filtrer par campus uniquement si un ID est spécifié
        if (!empty($filters['campus'])) {
            $qb->join('o.campus', 'c')
                ->andWhere('c.id = :campusId')
                ->setParameter('campusId', $filters['campus']);
        }

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

}
