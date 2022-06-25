<?php
namespace App\Controller\B2;

use App\Entity\B2\Historique;
use App\Entity\B2\Postit;
use App\Entity\B2\Traitements;
use App\Entity\B2\Uh;
use App\Entity\Users;
use App\Form\B2\TraitementFormType;
use App\Repository\B2\HistoriqueRepository;
use App\Repository\B2\ObservationsRepository;
use App\Repository\B2\PostitRepository;
use App\Repository\B2\TitreRepository;
use App\Repository\B2\TraitementsRepository;
use App\Repository\B2\UhRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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