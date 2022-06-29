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
    public function findWithTraitement($rapproche)
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
                    AND c.is_rapproche = ?";
        $query = $entityManager->getConnection()->prepare($sql);
        $query->bindValue(1, $rapproche);

        $result = $query->executeQuery()->fetchAllAssociative();

        return $result;
    }

    public function findOneJson($reference)
    {
        $entityManager = $this->getEntityManager();
        $sql = "    SELECT
                     c.*,
                     p.*,
                     o.name AS observation, o.color, o.bgcolor,
                     u.*,
                     us.username
                    FROM
                    b2_titre c
                    INNER JOIN b2_traitements p ON c.id = p.titre_id
                    INNER JOIN b2_observations o ON p.observation_id = o.id
                    INNER JOIN b2_uh u ON c.uh_id = u.id
                    INNER JOIN users us ON p.user_id = us.id
                    WHERE p.id =(
                    SELECT p2.id
                    FROM b2_traitements p2
                    WHERE p.titre_id = p2.titre_id
                    ORDER BY traite_at DESC
                    LIMIT 1
                    )
                    AND c.reference = ?                    
                    ORDER BY p.traite_at DESC LIMIT 1";
        $query = $entityManager->getConnection()->prepare($sql);
        $query->bindValue(1, $reference);

        $result = $query->executeQuery()->fetchAssociative();

        return $result;
    }

/*
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
*/
    public function titreWithSameIep($iep, $reference)
    {
        $em = $this->getEntityManager();
            $sql = "SELECT t.reference
                    FROM b2_titre t
                    WHERE t.iep = ?
                    EXCEPT
                    SELECT t.reference
                    FROM b2_titre t
                    WHERE t.reference = ?
            ";
        $query = $em->getConnection()->prepare($sql);
        $query->bindValue(1, $iep);
        $query->bindValue(2, $reference);

        $result = $query->executeQuery()->fetchAllAssociative();

        return $result;
    }
    public function titreWithSameIpp($ipp, $reference)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT t.iep
                    FROM b2_titre t
                    WHERE t.ipp = ?
                    EXCEPT
                    SELECT t.iep
                    FROM b2_titre t
                    WHERE t.reference = ?
            ";
        $query = $em->getConnection()->prepare($sql);
        $query->bindValue(1, $ipp);
        $query->bindValue(2, $reference);

        $result = $query->executeQuery()->fetchAllAssociative();

        return $result;
    }


    public function historiqueByTitreJson($reference)
    {
        $entityManager = $this->getEntityManager();
        $sql = "    SELECT
                     h.*,
                     o.*,
                     u.username
                    FROM
                    b2_titre t
                    INNER JOIN b2_traitements h ON h.titre_id = t.id
                    INNER JOIN b2_observations o ON  o.id = h.observation_id
                    INNER JOIN users u ON u.id = h.user_id
                    WHERE t.reference = ?
                    ORDER BY h.traite_at DESC";
        $query = $entityManager->getConnection()->prepare($sql);
        $query->bindValue(1, $reference);

        $result = $query->executeQuery()->fetchAllAssociative();

        return $result;
    }

    public function countSumByObs($rapproche, $observation, $type = null)
    {
        $entityManager = $this->getEntityManager();
        if($type && $type !== 'Total'){
            $sql = "    SELECT COUNT(t.id) AS countItem,
                    SUM(t.montant) AS sumItem,
                    o.name AS observation
                    FROM b2_titre t
                    LEFT JOIN b2_traitements ttt
                    ON ttt.titre_id = t.id
                    LEFT JOIN b2_observations o
                    ON o.id = ttt.observation_id
                    WHERE ttt.id = (
                    SELECT ttt2.id
                    FROM b2_traitements ttt2
                    WHERE ttt.titre_id = ttt2.titre_id
                    ORDER BY traite_at DESC LIMIT 1
                    )
                    AND t.is_rapproche = ? 
                    AND o.name = ?
                    AND t.type = ?";
        }else{
            $sql = "    SELECT COUNT(t.id) AS countItem,
                    SUM(t.montant) AS sumItem,
                    o.name AS observation
                    FROM b2_titre t
                    LEFT JOIN b2_traitements ttt
                    ON ttt.titre_id = t.id
                    LEFT JOIN b2_observations o
                    ON o.id = ttt.observation_id
                    WHERE ttt.id = (
                    SELECT ttt2.id
                    FROM b2_traitements ttt2
                    WHERE ttt.titre_id = ttt2.titre_id
                    ORDER BY traite_at DESC LIMIT 1
                    )
                    AND t.is_rapproche = ? 
                    AND o.name = ?";
        }

        $query = $entityManager->getConnection()->prepare($sql);
        $query->bindValue(1, $rapproche);
        $query->bindValue(2, $observation);
        if($type && $type !== 'Total'){
            $query->bindValue(3, $type);
        }

        $result = $query->executeQuery()->fetchAssociative();

        return $result;
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
