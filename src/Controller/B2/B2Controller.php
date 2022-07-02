<?php

namespace App\Controller\B2;

use App\Entity\B2\Historique;
use App\Entity\B2\Postit;
use App\Entity\B2\Traitements;
use App\Entity\B2\Uh;
use App\Form\B2\TraitementFormType;
use App\Repository\B2\ObservationsRepository;
use App\Repository\B2\PostitRepository;
use App\Repository\B2\TitreRepository;
use App\Repository\B2\TraitementsRepository;
use App\Repository\B2\UhRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use function array_diff;


class B2Controller extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * Affichages des titres
     *
     * @param TitreRepository $titreRepository
     * @param TraitementsRepository $traitementsRepository
     * @param ObservationsRepository $observationsRepository
     * @param UhRepository $uhRepository
     * @param PostitRepository $postitRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/B2/titres', name: 'b2_titres')]
    #[IsGranted('ROLE_USER')]
    public function titres(TitreRepository        $titreRepository,
                           TraitementsRepository  $traitementsRepository,
                           ObservationsRepository $observationsRepository,
                           UhRepository           $uhRepository,
                           PostitRepository       $postitRepository,
        //EntityManagerInterface $em,
                           Request                $request): Response
    {
        // affichage des débiteurs
        $debiteursListe = $titreRepository->createQueryBuilder('c')
            ->select('c.name')
            ->where('c.is_rapproche = 0')
            ->groupBy('c.name')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
        // affichage des descriptions
        $descriptionsListe = $titreRepository->createQueryBuilder('c')
            ->select('c.desc_rejet, c.code_rejet')
            ->where('c.is_rapproche = 0')
            ->groupBy('c.desc_rejet')
            ->orderBy('c.desc_rejet', 'ASC')
            ->getQuery()
            ->getResult();

        // affichage des descriptions
        $designationsListe = $titreRepository->createQueryBuilder('c')
            ->select('c.designation')
            ->where('c.is_rapproche = 0')
            ->groupBy('c.designation')
            ->orderBy('c.designation', 'ASC')
            ->getQuery()
            ->getResult();
        // Affichage des UH
        $UhsListe = $uhRepository->createQueryBuilder('c')
            ->select('c.id, c.numero, c.designation, c.antenne')
            ->groupBy('c.numero')
            ->orderBy('c.numero', 'ASC')
            ->getQuery()
            ->getResult();

        if ($request->isMethod('POST')) {
            // On vérifie si un type de filtre est appelé
            $typesSelect = null;
            if (!empty($request->get('typesSelect'))) {
                $typesSelect = explode('_|_', $request->get('typesSelect'));
            }
            $typesRequest = ($typesSelect != null ? $typesSelect : $request->get('types'));

            $debiteursSelect = null;
            if (!empty($request->get('debiteursSelect'))) {
                $debiteursSelect = explode('_|_', $request->get('debiteursSelect'));
            }
            $namesRequest = ($debiteursSelect != null ? $debiteursSelect : $request->get('debiteurs'));

            $descriptionsSelect = null;
            if (!empty($request->get('descriptionsSelect'))) {
                $descriptionsSelect = explode('_|_', $request->get('descriptionsSelect'));
            }
            $descriptionsRequest = ($descriptionsSelect != null ? $descriptionsSelect : $request->get('descriptions'));

            $designationsSelect = null;
            if (!empty($request->get('designationsSelect'))) {
                $designationsSelect = explode('_|_', $request->get('designationsSelect'));
            }
            $designationsRequest = ($designationsSelect != null ? $designationsSelect : $request->get('designations'));

            $uhsSelect = null;
            if (!empty($request->get('uhsSelect'))) {
                $uhsSelect = explode('_|_', $request->get('uhsSelect'));
            }
            $UhsRequest = ($uhsSelect != null ? $uhsSelect : $request->get('uhs'));

            // Renommer les name Request si global
            if ($namesRequest) {
                $namesRequestRebuild = $namesRequest;
                $classeFilter = [];
                foreach ($namesRequest as $nameRequest) {
                    switch ($nameRequest) {
                        case 'zr':
                            $classeFilter[] = 'zr';
                            $namesRequest = array_diff($namesRequest, ['zr']);
                            break;
                        case 'zs':
                            $classeFilter[] = 'zs';
                            $namesRequest = array_diff($namesRequest, ['zs']);
                            break;
                        case 'zx':
                            $classeFilter[] = 'zx';
                            $namesRequest = array_diff($namesRequest, ['zx']);
                            break;
                        case 'zm':
                            $classeFilter[] = 'zm';
                            $namesRequest = array_diff($namesRequest, ['zm']);
                            break;
                    }
                }
            }
            if (empty($namesRequest)) {
                $namesRequest = null;
            }

            $qb = $titreRepository->createQueryBuilder('t')
                ->select('t');
            if (isset($UhsRequest)) {
                $qb->innerJoin(Uh::class, 'u')
                    ->where('u.id = t.uh')
                    ->andWhere('t.is_rapproche = 0');
            } else {
                $qb->where('t.is_rapproche = 0');
            }
            if (isset($typesRequest)) {
                $qb->andWhere("t.type IN (:typeRequest)");
            }
            if (isset($namesRequest)) {
                if (empty($classeFilter)) {
                    $qb->andWhere("t.name IN (:namesRequest)");
                } else {
                    $qb->andWhere("t.name IN (:namesRequest) OR t.classe IN (:classe)");
                }
            }
            if (isset($descriptionsRequest)) {
                $qb->andWhere("t.desc_rejet IN (:descriptionRequest)");
            }
            if (isset($designationsRequest)) {
                $qb->andWhere("t.designation IN (:designationRequest)");
            }
            if (isset($UhsRequest)) {
                $qb->andWhere("u.numero IN (:uhRequest)");
            }
            if (isset($typesRequest)) {
                $qb->setParameter('typeRequest', $typesRequest);
            }
            if (isset($namesRequest)) {
                if (empty($classeFilter)) {
                    $qb->setParameter('namesRequest', $namesRequest);
                } else {
                    $qb->setParameter('namesRequest', $namesRequest)
                        ->setParameter('classe', $classeFilter);
                }
            }
            if (isset($descriptionsRequest)) {
                $qb->setParameter('descriptionRequest', $descriptionsRequest);
            }
            if (isset($designationsRequest)) {
                $qb->setParameter('designationRequest', $designationsRequest);
            }
            if (isset($UhsRequest)) {
                $qb->setParameter('uhRequest', $UhsRequest);
            }

            $qb->orderBy('t.montant', 'DESC');
            $titres2 = $qb->getQuery()
                ->getResult();

        } else {
            $titres2 = $titreRepository->findBy(['is_rapproche' => 0], ['montant' => 'DESC']);

        }//Fin filtre des débiteurs

        // Modal Traitement
        $observations = $observationsRepository->findBy([], ['name' => 'ASC']);

        $traitement = new Traitements();
        $form = $this->createForm(TraitementFormType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $titre = $titreRepository->findOneBy([
                'reference' => $request->get('form_ref')
            ]);
            $now = new DateTimeImmutable();
            $user = $this->getUser();

            // mis à jour du traitement
            $traitement_last = $traitementsRepository->findOneBy(['titre' => $titre->getId()], ['traite_at' => 'DESC']);
            $traitement->setTitre($titre);
            $traitement->setUser($user);
            $traitement->setTraiteAt($now);

            $observation = $observationsRepository->findOneBy([
                'id' => $form->get('observation')->getData()
            ]);
            $traitement->setObservation($observation);
            $traitement->setPrecisions($form->get('precisions')->getData());

            if ($traitement_last &&
                (
                    ($traitement_last->getPrecisions() !== $traitement->getPrecisions())
                    ||
                    ($traitement_last->getObservation() !== $traitement->getObservation()))) {
                $this->em->persist($traitement);
                $this->addHistorique($user, 'Nouvelle observation pour le titre ' . $titre->getReference(), $observation);
            }
            //postit
            $postit_exist = $postitRepository->findOneBy(['ipp' => $titre->getIpp()]);
            if ($postit_exist && ($postit_exist->getPostit() !== $request->get('postit'))) {
                $postit_exist->setPostit($request->get('postit'));
                $postit_exist->setPostitAt($now);
                $this->em->persist($postit_exist);
                $this->addHistorique($user, 'Mise à jour post-it pour IPP ' . $titre->getIpp());

            } elseif (!$postit_exist && $request->get('postit')) {
                $postit = new Postit();
                $postit->setIpp($titre->getIpp());
                $postit->setPostit($request->get('postit'));
                $postit->setPostitAt($now);
                $this->em->persist($postit);
                $this->addFlash('info', 'Titre n°' . $titre->getReference() . ' mis à jour.');
                $this->addHistorique($user, 'Nouveau post-it pour IPP ' . $titre->getIpp());
            }

            // Vérification du rprs pour le Titre
            $rprs = !($request->request->get('rprs') !== 'on');
            if ($rprs !== $titre->getRprs()) {
                $titre->setRprs($rprs);
                $this->em->persist($titre);
                $this->addFlash('info', 'RPRS mis à jour pour le titre n° ' . $titre->getReference() . '.');
                $this->addHistorique($user, 'Mise à jour du rprs titre ' . $titre->getReference());
            }
            $this->em->flush();


        }
        // Fin modal
        return $this->renderForm('B2/titresV2.html.twig', [
            'titres2' => $titres2,
            'observations' => $observations,
            'form' => $form,
            'debiteurs' => $debiteursListe,
            'descriptions' => $descriptionsListe,
            'designations' => $designationsListe,
            'uhs' => $UhsListe,
            'typesSelect' => ($typesRequest ?? ''),
            'debiteursSelect' => ($namesRequestRebuild ?? ''),
            'descriptionsSelect' => ($descriptionsRequest ?? ''),
            'designationsSelect' => ($designationsRequest ?? ''),
            'uhsSelect' => ($UhsRequest ?? ''),

        ]);
    }

    /**
     * Ajout d'historique dans la modal
     *
     * @param $user_id
     * @param $message
     * @param null $observation
     * @return void
     */
    private function addHistorique($user_id, $message, $observation =null): void
    {
        $now = new DateTimeImmutable();
        $historique = new Historique();
        $historique->setUser($user_id);
        $historique->setContext($message);
        $historique->setDateAt($now);
        $historique->setObservation($observation);
        $this->em->persist($historique);
        $this->em->flush();
    }
}