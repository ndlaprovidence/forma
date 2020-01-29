<?php

namespace App\Repository;

use App\Entity\SessionLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SessionLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionLocation[]    findAll()
 * @method SessionLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionLocation::class);
    }

    // /**
    //  * @return SessionLocation[] Returns an array of SessionLocation objects
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

    public function findOneById($id): ?SessionLocation
    {
        return $this->createQueryBuilder('sl')
            ->andWhere('sl.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findSameSessionLocation($city,$codePostal,$street)
    {
        $qb = $this->createQueryBuilder('sl')
            ->where('sl.postal_code = :val1')
            ->setParameter('val1', $codePostal)
            ->andWhere('sl.city = :val2')
            ->setParameter('val2', $city)
            ->andWhere('sl.street = :val3')
            ->setParameter('val3', $street);

        $query = $qb->getQuery();

        return $query->execute();
    }
}
