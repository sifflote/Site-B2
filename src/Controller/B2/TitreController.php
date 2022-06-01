<?php
namespace App\Controller\B2;

use App\Entity\B2\Observations;
use App\Entity\B2\Traitements;
use App\Form\B2\TraitementFormType;
use App\Repository\B2\ObservationsRepository;
use App\Repository\B2\TitreRepository;
use App\Repository\B2\TraitementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class TitreController extends AbstractController
{

    #[Route('/B2/t/{ref}/{historique?}', name: 'b2_titre_details')]
    public function titres(TitreRepository $titreRepository, Request $request, UserInterface $user,
                           ObservationsRepository $observationsRepository,
                            TraitementsRepository $traitementsRepository,
                            EntityManagerInterface $em): Response
    {
        $titre = $titreRepository->findOneBy([
            'reference' => $request->get('ref')
        ]);


        $now  = new \DateTimeImmutable();
        $nbJours = $titre->getEnterAt()->diff($titre->getExitAt())->days;

        $traitement = new Traitements();
        $form = $this->createForm(TraitementFormType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $traitement->setTitre($titre);
            $traitement->setUser($user);
            $traitement->setTraiteAt($now);
            $titre->setRprs($request->request->get('rprs'));

            $observation = $observationsRepository->findOneBy([
                'id' => $form->get('observation')->getData()
                ]);
            $traitement->setObservation($observation);

            $traitement->setPrecisions($form->get('precisions')->getData());
            $em->persist($traitement);
            $em->persist($titre);
            $em->flush();
        }


        $historiques = $traitementsRepository->findBy([
            'titre' => $titre->getId()
        ],
            [
                'traite_at' => 'DESC'
            ]);
        $last_statut = null;
        if(!empty($historiques)){
            $last_statut = $historiques[0];
        }
        $ieps = $titreRepository->findBy([
            'iep' => $titre->getIep()
            ]);
        $ipps = $titreRepository->findBy([
            'ipp' => $titre->getIpp()
        ]);
        return $this->renderForm('B2/details.html.twig', [
            'titre' => $titre,
            'nb_jours' => $nbJours + 1,
            'form' => $form,
            'historiques' => $historiques,
            'last' => $last_statut,
            'ieps' => $ieps,
            'ipps' => $ipps
            ]);
    }
}