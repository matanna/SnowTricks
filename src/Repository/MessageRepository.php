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

    /** 
    * @return Message[] Returns an array of Message objects
    */
    public function findByTricks($id, $limit, $offset)
    {
        return $this->createQueryBuilder('message')
                    ->where('message.tricks = :id')
                    ->setParameter('id', $id)
                    ->orderBy('message.dateMessage', 'DESC')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->getQuery()
                    ->getResult();
    }

    public function countByTricks($id)
    {
        return $this->createQueryBuilder('message')
                    ->select('count(message.id)')
                    ->where('message.tricks = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getSingleScalarResult();
    }


    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
