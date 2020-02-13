<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    // /**
    //  * @return Session[] Returns an array of Session objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    public function getNbTrainees($id)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(t.last_name)')
            ->leftJoin('s.trainees', 't')
            ->andWhere('s.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
        ;
    }


    public function findOneById($id): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    

    public function findSessionByParameters($location,$training,$date)
    {
        return $this->createQueryBuilder('s')
            ->where('s.location = :val1')
            ->setParameter('val1', $location)
            ->andWhere('s.training = :val2')
            ->setParameter('val2', $training)
            ->andWhere('s.date = :val3')
            ->setParameter('val3', $date)
            ->getQuery()
            ->getResult()
        ;
    }


    public function findSessionsCollectionByUpload($upload)
    {
        return $this->createQueryBuilder('s')
            ->where('s.upload = :val')
            ->setParameter('val', $upload)
            ->getQuery()
            ->getResult()
        ;
    }


    public function CountSessionsWithSameUpload()
    {
        return $this->createQueryBuilder('s')
            ->groupBy('s.upload')
            ->getQuery()
            ->getResult();
    }
}
