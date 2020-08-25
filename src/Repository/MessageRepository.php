<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    // /**
    //  * @return Message[] Returns an array of Message objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function nextRandomMsg($value): ?Message
    {
        $builder = $this->createQueryBuilder('m')
            ->where('m.active = 1')
            ->andWhere('m.published <= :val OR m.published is NULL')
            ->setParameter('val', $value);

        $totalRecords = $builder->select('COUNT(m)')
            ->getQuery()->getSingleScalarResult();

        if ($totalRecords < 1) {
            return null;
        }
        $rowToFetch = mt_rand(1, $totalRecords - 1);

        return $builder->select('m')->getQuery()
            ->setMaxResults(1)
            ->setFirstResult($rowToFetch)
            ->getOneOrNullResult();
    }
}
