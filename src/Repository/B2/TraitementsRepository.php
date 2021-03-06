<?php

namespace App\Repository\B2;

use App\Entity\B2\Traitements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Traitements|null find($id, $lockMode = null, $lockVersion = null)
 * @method Traitements|null findOneBy(array $criteria, array $orderBy = null)
 * @method Traitements[]    findAll()
 * @method Traitements[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraitementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Traitements::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Traitements $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Traitements $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function deleteTraitementRapproche(array $aRapprocher){
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.titre IN (:value)')
            ->setParameter('value', $aRapprocher, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->execute();
    }

    // /**
    //  * @return Traitements[] Returns an array of Traitements objects
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

    /*
    public function findOneBySomeField($value): ?Traitements
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
