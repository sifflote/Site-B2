<?php
namespace App\Controller\B2;

use App\Entity\B2\Extractions;
use App\Entity\B2\Titre;
use App\Entity\B2\Traitements;
use App\Entity\B2\Uh;
use App\Entity\Users;
use App\Factory\JsonResponseFactory;
use App\Form\FileUploadType;
use App\Repository\B2\ExtractionsRepository;
use App\Repository\B2\ObservationsRepository;
use App\Repository\B2\TitreRepository;
use App\Repository\B2\TraitementsRepository;
use App\Repository\B2\UhRepository;
use App\Repository\UsersRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;


class B2Controller extends AbstractController
{
    public function __construct(private JsonResponseFactory $jsonResponseFactory)
    {
    }

    #[Route('/B2/titres/{page?1}/{order?}/{sens?}', name: 'b2_titres', methods: ['GET','POST'])]
    public function titres(TitreRepository $titreRepository, Request $request): Response
    {

        $perPage = ($this->getUser()->getB2limitPage() == 0 ? 10000 : $this->getUser()->getB2limitPage());
        $page = (int)$request->get('page', 1);

        $order = ($request->get('order') !== null) ? $request->get('order') : 'montant';
        $sens = ($request->get('sens') !== null) ? $request->get('sens') : 'DESC';
        //$titres = $titreRepository->findWithTraitement(0, 'montant', 'ASC');
        //$titres = $titreRepository->findBy(['is_rapproche' => 0], [$order => $sens]);
        $titres = $titreRepository->findByPaginated($page, $perPage, 0, $order, $sens);
        $total = $titreRepository->getTotalRejetsNonRapproche();
        $json = json_encode($titres);
        return $this->render('B2/titres.html.twig', [
            'titres' => $titres,
            'json' => str_replace("'", "\'", $json),
            'sort' => $request->get('order').'/'.$request->get('sens'),
            'order_value' => $order,
            'sens_value' => $sens,
            'page' => $page,
            'limit' => $perPage,
            'total' => $total

        ]);
    }

    #[Route('B2/changePage', name: 'b2_changepage')]
    public function changePage(EntityManagerInterface $em, Request $request, UsersRepository $usersRepository)
    {
        $user = $usersRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $user->setB2LimitPage((int)$request->get('nbpage'));
        $em->persist($user);
        $em->flush();


        $this->addFlash('info', 'Affichage du nombre d\'éléments par page bien modifié');
        return $this->redirectToRoute('b2_titres', ['page' => 1, 'order' => $request->get('order'), 'sens' => $request->get('sens')]);
    }

    #[Route('/B2/titres_json', name: 'b2_json')]
    public function view_json(TitreRepository $titreRepository) :Response
    {
        $titres = $titreRepository->findWithTraitement(1, 'montant', 'ASC');
        $json = json_encode($titres);
        $str_json = (str_replace("\\", "", $json));

        $user = $this->getUser();
        if(!$user) return $this->json([
            'code' => 403,
            'message' => 'Non Autorisé'
        ], 403);


        return $this->json(['message' => 'Chargement Réussi', 'titres' => $titres], 200);

    }

    /**
     * @param Request $request
     * @param TitreRepository $titreRepository
     * @return Response
     * pour classer par colonne
     */
    #[Route('/B2/by/{value}/{order}', name:'b2_by')]
    public function show_by(Request $request, TitreRepository $titreRepository): Response
    {
        $value = $request->get('value');
        $order = $request->get('order');
        return $this->render('B2/titres.html.twig', [
            'titres' => $titreRepository->findby([],
                [   $value => $order,
                    'is_rapproche' => 0
                ]
            ),
            'value' => $value,
            'order' => $order

        ]);
    }

    /**
     * @param Request $request
     * @param TitreRepository $titreRepository
     * @return Response
     * Pour chercher plusieurs tous les dossiers par données
     */
    #[Route('/B2/titres/{filter}/{value}', name:'b2_group')]
    public function show_filter(Request $request, TitreRepository $titreRepository): Response
    {
        $filter = $request->get('filter');
        return $this->render('B2/titres.html.twig', [
            'titres' => $titreRepository->findBy([
                $filter => $request->get('value'),
                'is_rapproche' => 0
            ],
                ['montant' => 'desc'])
        ]);
    }


    #[Route('/B2/extractions', name: 'b2_extractions')]
    public function extractions(ExtractionsRepository $extractionsRepository): Response
    {
        return $this->render('B2/extract_list.html.twig', [
          'extractions' => $extractionsRepository->findby([],
              ['import_at' => 'desc'])
        ]);
    }

    #[Route('B2/purge', name: 'b2_purge')]
    public function purge(ExtractionsRepository $extractionsRepository, TitreRepository $titreRepository, EntityManagerInterface $em): Response
    {
        $lastExtraction = $extractionsRepository->findOneBy([], ['import_at' => 'desc']);
        $titres = $titreRepository->findBy(['is_rapproche' => false]);
        $nbRapproche = 0;
        $aRapprocher = [];
        foreach ($titres as $titre){
            // si n'est pas (date de dernière extraction égale à date d'extraction, ou date de dernière extraction date de maj
            if(!(($titre->getExtractionAt() == $lastExtraction->getImportAt()) || ($titre->getMajAt() == $lastExtraction->getImportAt()))){

                $nbRapproche++;
                $aRapprocher[] = $titre->getId();

            }

        }
        $sql = $titreRepository->createQueryBuilder('');
        $sql->update(Titre::class, 't')
            ->set('t.is_rapproche', ':key')
            ->setParameter('key', 1)
            ->where('t.id IN (:value)')
            ->setParameter('value', $aRapprocher, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->execute();
        $lastExtraction->setIsPurge(1);
        $lastExtraction->setRapproche($nbRapproche);
        $em->persist($lastExtraction);
        $em->flush();

        $this->addFlash('info', 'Purge des '.$nbRapproche.' titres rapprochés par annulation ou encaissement réussie');
        return $this->redirectToRoute('b2_extractions');
    }

    #[Route('/B2/upload', name: 'b2_upload')]
    public function csvImport(Request $request, FileUploader $file_uploader, EntityManagerInterface $em, UhRepository $uhRepository)
    {
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form['upload_file']->getData();
            if ($file)
            {
                $file_name = $file_uploader->upload($file);
                if (null !== $file_name) // for example
                {
                    $directory = $file_uploader->getTargetDirectory();
                    $full_path = $directory.'/'.$file_name;

                    $outputFile = 'part-' . $file_name;
                    $splitSize = 400;
                    $in = fopen($full_path, 'r');

                    $count_row = 0;
                    $fileCount = 0;
                    $out = null;

                    while (!feof($in,)){
                        if(($count_row % $splitSize) == 0) {
                            if($count_row > 0) {
                                fclose($out);
                            }
                            $fileCount++;

                            $fileCounterDisplay = sprintf("%04d", $fileCount);

                            //$fileName = "$fileCounterDisplay$outputFile";
                            $fileName = "$directory/$fileCount$outputFile";
                            $out = fopen($fileName, 'w');
                        }
                        $data = fgetcsv($in);

                        if($data)
                            fputcsv($out, $data);

                        $count_row++;
                    }
                    fclose($out);

                    // Do what you want with the full path file...
                    // Why not read the content or parse it !!!
                    $extraction = New Extractions();
                    $extraction->setName($file_name);
                    $now  = new DateTimeImmutable();
                    $extraction->setImportAt($now);
                    $extraction->setFiles($fileCount);
                    $extraction->setVerify(0);
                    $extraction->setVerify2(0);
                    $extraction->setCountLine($count_row - 1);
                    $extraction->setIsPurge(0);
                    $extraction->setRapproche(0);
                    $em->persist($extraction);
                    $em->flush();

                    $this->addFlash('success', "Upload réussi Fichier: $file_name découpé en $fileCount fichier(s).");
                    return $this->redirectToRoute('b2_extractions');

                }
                else
                {
                    // Oups, an error occured !!!
                    $this->addFlash('danger', 'Problème lors de l\'upload');
                    return $this->redirectToRoute('b2_extractions');
                }
            }
        }

        //vérifier si $csv transmis
        if(!isset($csv)){$csv = "";}
        return $this->render('B2/upload.html.twig', [
            'form' => $form->createView(),
            'csv' => ($csv ? $csv : null)
        ]);
    }
    // ...
    #[Route('/B2/upload/lecture/{file}', name: 'b2_lecture')]
    public function lecture(Request $request, FileUploader $file_uploader, EntityManagerInterface $em,
                            UhRepository $uhRepository, ExtractionsRepository $extractionsRepository,
                            TitreRepository $titreRepository, ObservationsRepository $observationsRepository,
                            UserInterface $user)
    {
        $file_name = $request->get('file') . '.csv';
        $directory = $file_uploader->getTargetDirectory();
        $full_path = $directory.'/'.$file_name;
        $file_number = explode('part-', $file_name)[0];



        $openfile = fopen($full_path, "r");
        $cont = fread($openfile, filesize($full_path));
        $csv = array_map('str_getcsv', file($full_path));
        if(explode('part-', $file_name)[0] == 1){ $i = 0; }else{ $i=1; }
        $now  = new DateTimeImmutable();
        $extraction = $extractionsRepository->findOneBy(['name' => explode('part-', $file_name)[1]]);
        $newLine = 0;
        foreach($csv as $ligne => $value)
        {
            if($i > 0){

                $verif_titre = $titreRepository->findOneBy(['reference' => $value[11]]);
                // RPRS est un boolean si RPRS est écrit
                $rprs = ($value[26] == 'RPRS') ? 1 : 0;
                //dd((float)$this->priceToFloat($value[8]));
                if(empty($verif_titre)){

                    $titre = new Titre();
                    $titre->setType($value[0]);
                    $titre->setClasse($value[1]);
                    $titre->setIep($value[2]);
                    $titre->setIpp($value[3]);
                    $titre->setFacture($value[4]);
                    $titre->setName($value[5]);
                    $titre->setEnterAt(\DateTime::createFromFormat('d/m/Y', $value[6]));
                    $titre->setExitAt(\DateTime::createFromFormat('d/m/Y', $value[7]));
                    $titre->setMontant($this->priceToFloat($value[8]));
                    $titre->setEncaissement($this->priceToFloat($value[9]));
                    $titre->setRestantdu($this->priceToFloat($value[10]));
                    $titre->setReference($value[11]);
                    $titre->setPec($value[12]);
                    $titre->setLot($value[13]);
                    $titre->setPayeur($value[14]);
                    $titre->setCodeRejet($value[15]);
                    $titre->setDescRejet($value[16]);
                    $titre->setCreeAt(\DateTime::createFromFormat('d/m/Y', $value[17]));
                    $titre->setRejetAt(\DateTime::createFromFormat('d/m/Y', $value[18]));
                    $titre->setDesignation($value[19]);
                    // pour les Uh on doit vérifier qu'il existe dans la base de donnée
                    $verif_uh = $uhRepository->findOneBy(['numero' => $value[20]]);
                    if(isset($verif_uh)){
                        $titre->setUh($verif_uh);
                    }else{
                        $uh = new Uh();
                        $uh->setNumero($value[20]);
                        $uh->setDesignation('');
                        $uh->setAntenne('');
                        $em->persist($uh);
                        $em->flush();
                        $titre->setUh($uh);
                    }
                    $titre->setInsee((int)$value[21]);
                    $titre->setRang((int)$value[22]);
                    $titre->setNaissanceAt(\DateTime::createFromFormat('d/m/Y', $value[23]));
                    $titre->setContrat($value[24]);
                    $titre->setNaissanceHf($value[25]);
                    $titre->setRprs($rprs);
                    $titre->setExtractionAt($extraction->getImportAt());
                    //$titre->setMajAt($now);
                    $newLine++;
                    $em->persist($titre);
                    $em->flush();
                }else{
                    //si le titre est déjà présent on change la date de mise à jour
                    $verif_titre->setMajAt($extraction->getImportAt());
                    $verif_titre->setRprs($rprs);
                    $em->persist($verif_titre);
                    $em->flush();
                }


            }
            $i++;
        }
        $extraction->setVerify($extraction->getVerify() + 1);
        $extraction->setNewLine($extraction->getNewLine() + $newLine);
        $em->persist($extraction);
        $em->flush();
        $this->addFlash('success', "Vérification Titre du fichier: '.$file_name.' OK" );
        return $this->redirectToRoute('b2_extractions');
    }

    /**
     *
     * Vérification si déjà un traitement pour les titres / ajout ou maj de ce dernier
     *
     * @param Request $request
     * @param FileUploader $file_uploader
     * @param EntityManagerInterface $em
     * @param UhRepository $uhRepository
     * @param ExtractionsRepository $extractionsRepository
     * @param TitreRepository $titreRepository
     * @param ObservationsRepository $observationsRepository
     * @param UserInterface $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route('/B2/upload/verify/{file}', name: 'b2_verify')]
    public function verify(Request $request, FileUploader $file_uploader, EntityManagerInterface $em,
                            UhRepository $uhRepository, ExtractionsRepository $extractionsRepository,
                            TraitementsRepository $traitementsRepository,
                            TitreRepository $titreRepository, ObservationsRepository $observationsRepository,
                            UserInterface $user)
    {
        // On récupère le fichier
        $file_name = $request->get('file') . '.csv';
        $directory = $file_uploader->getTargetDirectory();
        $full_path = $directory.'/'.$file_name;
        $file_number = explode('part-', $file_name)[0];

        $openfile = fopen($full_path, "r");
        $cont = fread($openfile, filesize($full_path));
        $csv = array_map('str_getcsv', file($full_path));
        if(explode('part-', $file_name)[0] == 1){ $i = 0; }else{ $i=1; }
        $now  = new DateTimeImmutable();
        $extraction = $extractionsRepository->findOneBy(['name' => explode('part-', $file_name)[1]]);
        $maj_obs = 0;
        // Vérification de chaque ligne
        foreach($csv as $ligne => $value)
        {

            if($i > 0){
                // On récupère le titre existant dans la BDD
                $titre = $titreRepository->findOneBy(['reference' => $value[11]]);
                // Récupération de l'intitulé d'observation
                $observation = $observationsRepository->findOneBy(['name' => $value[28]]);
                //Vérifier si traitement existant
                $traitement = $traitementsRepository->findBy(['titre' => $titre->getId()], ['traite_at' => 'DESC'], 1 ,0);

                // si l'observation du fichier n'est pas vide et si il n'y a pas de traitement
                // OU
                // si observation est differente du traitement
                if((!empty($observation) && (empty($traitement))) || ($traitement[0]->getObservation()->getId() !== $observation->getId())){

                    $ttt = new Traitements();
                    $ttt->setTitre($titre);
                    $ttt->setObservation($observation);
                    $ttt->setUser($user);
                    $ttt->setPrecisions($value[29]);
                    if($value[30] == ""){
                        $valeur30 = \DateTimeImmutable::createFromFormat('d/m/Y', $value[18]);
                    }else{
                        $valeur30 = \DateTimeImmutable::createFromFormat('d/m/Y', $value[30]);
                    }

                    $ttt->setTraiteAt($valeur30);
                    $em->persist($ttt);
                    $em->flush();
                    $maj_obs++;
                }

            }
            $i++;
        }
        $extraction->setVerify2($extraction->getVerify2() + 1);
        $extraction->setCountObs($extraction->getCountObs() + $maj_obs);
        $em->persist($extraction);
        $em->flush();
        $this->addFlash('success', "Vérification Observation du fichier: '.$file_name.' OK" );
        return $this->redirectToRoute('b2_extractions');
    }


    private function priceToFloat($s): float
    {
        // convert "," to "."
        $s = str_replace(',', '.', $s);

        // remove everything except numbers and dot "."
        $s = preg_replace("/[^0-9\.]/", "", $s);

        // remove all seperators from first part and keep the end
        $s = str_replace('.', '',substr($s, 0, -3)) . substr($s, -3);

        // return float
        return (float) $s;
    }
}