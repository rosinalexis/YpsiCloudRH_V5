<?php

namespace App\Repository;

use App\Entity\JobAdvert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JobAdvert|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobAdvert|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobAdvert[]    findAll()
 * @method JobAdvert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobAdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobAdvert::class);
    }

    // /**
    //  * @return JobAdvert[] Returns an array of JobAdvert objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JobAdvert
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
