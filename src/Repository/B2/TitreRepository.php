<?php

namespace App\Repository\B2;

use App\Entity\B2\Titre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Titre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Titre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Titre[]    findAll()
 * @method Titre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TitreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Titre::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Titre $entity, bool $flush = true): void
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
    public function remove(Titre $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    /**
     * @param $rapproche
     * @return float|int|mixed|string
     *
     *
     * SELECT
     *
     * FROM
     * b2_titre c
     * INNER JOIN b2_traitements p ON c.id = p.titre_id
     * WHERE p.id =(
     * SELECT p2.id
     * FROM b2_traitements p2
     * WHERE p.titre_id = p2.titre_id
     * ORDER BY traite_at DESC
     * LIMIT 1
     * )
     * AND c.is_rapproche = 1
     * ORDER BY c.montant DESC
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function findWithTraitement($rapproche, $order, $sens)
    {
        $entityManager = $this->getEntityManager();
        $sql = "    SELECT
                     *
                    FROM
                    b2_titre c
                    INNER JOIN b2_traitements p ON c.id = p.titre_id
                    WHERE p.id =(
                    SELECT p2.id
                    FROM b2_traitements p2
                    WHERE p.titre_id = p2.titre_id
                    ORDER BY traite_at DESC
                    LIMIT 1
                    )
                    AND c.is_rapproche = ?
                    ORDER BY '.$order.' '.$sens.'";
        $query = $entityManager->getConnection()->prepare($sql);
        $query->bindValue(1, $rapproche);

        $result = $query->executeQuery()->fetchAllAssociative();

        return $result;
    }

    public function findByPaginated($page, $limit, $rapproche, $order, $sens)
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.is_rapproche = :rapproche')
            ->setParameter('rapproche', $rapproche)
            ->orderBy('a.'.$order, $sens)
            ->setFirstResult(($page * $limit) - $limit)
            ->setMaxResults($limit);
        return $query->getQuery()->getResult();
    }

    public function getTotalRejetsNonRapproche()
    {
        $query = $this->createQueryBuilder('r')
            ->select('COUNT(r)')
            ->where('r.is_rapproche = 0')
            ;
        return $query->getQuery()->getSingleScalarResult();
    }
    // /**
    //  * @return Titre[] Returns an array of Titre objects
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
    public function findOneBySomeField($value): ?Titre
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
