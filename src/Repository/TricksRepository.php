<?php

namespace App\Repository;

use App\Entity\Tricks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tricks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tricks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tricks[]    findAll()
 * @method Tricks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TricksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tricks::class);
    }

    /**
    * @return Tricks[] Returns an array of Tricks objects
    */
    public function findFirstTricks($limit)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT tricks
             FROM App\Entity\Tricks tricks
             ORDER BY tricks.dateAtCreated DESC' 
        )->setMaxResults($limit);
        
        return $query->getResult();

    }

    /**
    * @return Tricks[] Returns an array of Tricks objects
    */
    public function findMoreTricks($offset)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT tricks
             FROM App\Entity\Tricks tricks
             ORDER BY tricks.dateAtCreated DESC' 
        )->setFirstResult($offset);
        
        return $query->getResult();
        
    }

    /**
     * @return Tricks[] Returns an array of Tricks objects
     */
    public function findTricksIdByName($name)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT tricks.id
             FROM App\Entity\Tricks tricks
             WHERE tricks.name = :name'
        )->setParameter('name', $name);

        $result = $query->getResult();

        return $result[0]['id']; 
    }

    public function findTricksByCategory($categoryId)
    {
        return $this->createQueryBuilder('tricks')
        ->select('tricks')
        ->where('tricks.category = :category')
        ->setParameter('category', $categoryId)
        ->getQuery()
        ->getResult();
    }
}
