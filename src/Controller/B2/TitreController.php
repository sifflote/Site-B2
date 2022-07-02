<?php

namespace App\Controller\B2;

use App\Repository\B2\PostitRepository;
use App\Repository\B2\TitreRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TitreController extends AbstractController
{
    /**
     *
     * Affichage du titre dans la Modal
     *
     * @param TitreRepository $titreRepository
     * @param PostitRepository $postitRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/B2/titre_json/{reference}', name: 'b2_titre_json')]
    #[IsGranted('ROLE_USER')]
    public function titre_json(TitreRepository $titreRepository, PostitRepository $postitRepository, Request $request): Response
    {
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $reference = $request->get('reference');
        $titreJson = $titreRepository->findOneJson($reference);
        $historiques = $titreRepository->historiqueByTitreJson($reference);
        $titre = $titreRepository->findOneBy(['reference' => $reference]);
        $postit = $postitRepository->findOneBy(['ipp' => $titre->getIpp()]);
        $titreWithSameIep = $titreRepository->titreWithSameIep($titre->getIep(), $titre->getReference());
        $titreWithSameIpp = $titreRepository->titreWithSameIpp($titre->getIpp(), $titre->getReference());

        $user = $this->getUser();
        if (!$user) return $this->json([
            'code' => 403,
            'titre' => $titreJson
        ], 403);

        // Le retour Json possède un paramètre par défaut de 200
        return $this->json(['data' => $titreJson, 'historiques' => $historiques, 'postit' => $postit, 'ieps' => $titreWithSameIep, 'ipps' => $titreWithSameIpp]);

    }

    /*
    /**
     *
     * ancien affichage désactiver
     *
     * @param TitreRepository $titreRepository
     * @param Request $request
     * @param UserInterface $user
     * @param ObservationsRepository $observationsRepository
     * @param TraitementsRepository $traitementsRepository
     * @param EntityManagerInterface $em
     * @return Response
     *
     */
    /*
    #[Route('/B2/t/{ref}/{historique?}', name: 'b2_titre_details')]
    public function titres(TitreRepository $titreRepository, Request $request, UserInterface $user,
                           ObservationsRepository $observationsRepository,
                            TraitementsRepository $traitementsRepository,
                            EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
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
    */
}