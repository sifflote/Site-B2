<?php

namespace App\Controller\B2;

use App\Entity\B2\Uh;
use App\Repository\B2\TitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatistiquesController extends AbstractController
{
    protected $titreRepository;

    public function __construct(TitreRepository $titreRepository)
    {
        $this->titreRepository = $titreRepository;

    }

    #[Route('/b2/statistiques', name: 'b2_statistiques')]
    public function index(TitreRepository $titreRepository): Response
    {
        //Statistiques Global
        $typeList = ['FS', 'FJ', 'ME', 'TE', 'Total'];

        foreach($typeList as $item){
            $global[$item] = $this->CountSumByType(0, $item)->getScalarResult()[0];
        }

dump($global);
        return $this->render('B2/statistiques/index.html.twig', [
            'global' => $global,
        ]);
    }


    public function CountSumByType(
                        int $rapproche,
                        $type = null
    )
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
