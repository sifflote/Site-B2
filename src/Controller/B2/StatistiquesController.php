<?php

namespace App\Controller\B2;

use App\Repository\B2\ObservationsRepository;
use App\Repository\B2\TitreRepository;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatistiquesController extends AbstractController
{
    protected TitreRepository $titreRepository;
    protected ObservationsRepository $observationsRepository;

    public function __construct(TitreRepository $titreRepository, ObservationsRepository $observationsRepository)
    {
        $this->titreRepository = $titreRepository;
        $this->observationsRepository = $observationsRepository;

    }

    #[Route('/b2/statistiques', name: 'b2_statistiques')]
    public function index(): Response
    {
        //Statistiques Par Observation
        $observations = [];
        $typeList = ['FS', 'FJ', 'ME', 'TE', 'Total'];
        $obsList = $this->observationsRepository->createQueryBuilder('o')
            ->select('o.name')
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();
        foreach ($obsList as $obs) {
                foreach($typeList as $item){
                    $countSum = $this->titreRepository->countSumByObs(0, $obs['name'], $item);

                    $observations[$obs['name']][$item] = $countSum;

                }
        }
        dump($observations);
        //Statistiques globales
        foreach($typeList as $item){
            $global[$item] = $this->CountSumByType(0, $item)->getArrayResult()[0];
        }
        return $this->render('B2/statistiques/index.html.twig', [
            'global' => $global,
            'observations' => $observations,
            'typeList' => $typeList
        ]);
    }

    public function CountSumByType(
        int $rapproche,
            $type = null
    ): Query
    {
        $qb = $this->titreRepository->createQueryBuilder('t')
            ->select('COUNT(t.id) as countItem, SUM(t.montant) as sumItem');
        $qb->where('t.is_rapproche = :rapproche');

        if ($type !== 'Total') {
            $qb->andWhere("t.type = :type");
        }
        $qb->setParameter('rapproche', $rapproche);
        if ($type !== 'Total') {
            $qb->setParameter('type', $type);
        }

        return $qb->getQuery();
    }
}
