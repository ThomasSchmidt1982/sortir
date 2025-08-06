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

}
