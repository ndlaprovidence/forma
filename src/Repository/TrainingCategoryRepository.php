<?php

namespace App\Repository;

use App\Entity\TrainingCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TrainingCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrainingCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrainingCategory[]    findAll()
 * @method TrainingCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingCategory::class);
    }

    // /**
    //  * @return TrainingCategory[] Returns an array of TrainingCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findOneById($id): ?TrainingCategory
    {
        return $this->createQueryBuilder('tc')
            ->andWhere('tc.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findSameTrainingCategory($trainingCategoryTitle)
    {
        $qb = $this->createQueryBuilder('tc')
            ->where('tc.title = :val')
            ->setParameter('val', $trainingCategoryTitle);

        $query = $qb->getQuery();

        return $query->execute();
    }
}
