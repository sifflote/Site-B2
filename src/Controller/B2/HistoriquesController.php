<?php
namespace App\Controller\B2;

use App\Repository\B2\HistoriqueRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HistoriquesController extends AbstractController
{
    /**
     * Historiques des modifications
     *
     * @param HistoriqueRepository $historiqueRepository
     * @return Response
     */
    #[Route('/B2/historiques', name: 'b2_historiques')]
    #[IsGranted('ROLE_USER')]
    public function historiques(HistoriqueRepository $historiqueRepository): Response
    {
        $historiques = $historiqueRepository->findBy([], ['dateAt' => 'DESC']);
        return $this->renderForm('B2/historiques.html.twig', [
            'historiques' => $historiques
        ]);
    }
}