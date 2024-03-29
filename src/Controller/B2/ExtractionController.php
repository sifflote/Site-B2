<?php

namespace App\Controller\B2;

use App\Entity\B2\Config;
use App\Entity\B2\Extractions;
use App\Entity\B2\Titre;
use App\Entity\B2\Traitements;
use App\Entity\B2\Uh;
use App\Form\FileUploadType;
use App\Repository\B2\ConfigRepository;
use App\Repository\B2\ExtractionsRepository;
use App\Repository\B2\ObservationsRepository;
use App\Repository\B2\TitreRepository;
use App\Repository\B2\TraitementsRepository;
use App\Repository\B2\UhRepository;
use App\Service\FileUploader;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ExtractionController extends AbstractController
{
    /**
     * Page d'accueil d'extraction
     *
     * @param ExtractionsRepository $extractionsRepository
     * @return Response
     */
    #[Route('/B2/extractions', name: 'b2_extractions')]
    #[IsGranted('ROLE_USER')]
    public function extractions(ExtractionsRepository $extractionsRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('B2/extract_list.html.twig', [
            'extractions' => $extractionsRepository->findby([],
                ['import_at' => 'desc'])
        ]);
    }

    /**
     * Importer un nouveau fichier
     *
     * @param Request $request
     * @param FileUploader $file_uploader
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    #[Route('/B2/upload', name: 'b2_upload')]
    #[IsGranted('ROLE_USER')]
    public function csvImport(Request $request, FileUploader $file_uploader, EntityManagerInterface $em): RedirectResponse|Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);
        $range = (int)$request->request->get('range');
        $extractionDate = $request->request->get('extractionDate');
        $outputDate = date('Y-m-d H:i:s', strtotime($extractionDate));
        $output2 = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $outputDate);
        $withObs = (boolean)$request->request->get('withObs');


        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form['upload_file']->getData();

            if ($file) {
                $file_name = $file_uploader->upload($file);
                //$file_name = 'B2-'.$output2->format('d-m-Y-His').'.csv';

                if (null !== $file_name) {
                    $directory = $file_uploader->getTargetDirectory();
                    $full_path = $directory . '/' . $file_name;

                    $outputFile = 'part-' . $file_name;
                    $splitSize = $range;
                    $in = fopen($full_path, 'r');

                    $count_row = 0;
                    $fileCount = 0;
                    $out = null;

                    while (!feof($in)) {
                        if (($count_row % $splitSize) == 0) {
                            if ($count_row > 0) {
                                fclose($out);
                            }
                            $fileCount++;

                            $fileName = "$directory/$fileCount$outputFile";
                            $out = fopen($fileName, 'w');
                        }
                        $data = fgetcsv($in);

                        if ($data)
                            fputcsv($out, $data);

                        $count_row++;
                    }
                    fclose($out);

                    // Enregistrement de l'extraction
                    $extraction = new Extractions();
                    $extraction->setName($file_name);
                    $extraction->setImportAt($output2);
                    $extraction->setFiles($fileCount);
                    $extraction->setVerify(0);
                    $extraction->setVerify2(0);
                    $extraction->setCountLine($count_row - 1);
                    $extraction->setIsPurge(0);
                    $extraction->setRapproche(0);
                    $extraction->setWithObs($withObs);
                    $em->persist($extraction);
                    $em->flush();

                    $this->addFlash('success', "Upload réussi Fichier: $file_name découpé en $fileCount fichier(s).");

                } else {
                    // Oups, an error !!!
                    $this->addFlash('danger', 'Problème lors de l\'upload');
                }
                return $this->redirectToRoute('b2_extractions');
            }
        }

        //vérifier si $csv transmis
        if (!isset($csv)) {
            $csv = "";
        }
        return $this->render('B2/upload.html.twig', [
            'form' => $form->createView(),
            'csv' => ($csv ?: null)
        ]);
    }

    /**
     * Lecture du fichier pour ajout des nouveaux titres
     * Passe en rapproché si les titres n'apparaissent plus
     *
     * @param Request $request
     * @param FileUploader $file_uploader
     * @param EntityManagerInterface $em
     * @param UhRepository $uhRepository
     * @param ExtractionsRepository $extractionsRepository
     * @param TitreRepository $titreRepository
     * @return RedirectResponse
     */
    #[Route('/B2/upload/lecture/{file}', name: 'b2_lecture')]
    #[IsGranted('ROLE_USER')]
    public function lecture(Request         $request, FileUploader $file_uploader, EntityManagerInterface $em,
                            UhRepository    $uhRepository, ExtractionsRepository $extractionsRepository,
                            TitreRepository $titreRepository,
                            ConfigRepository $configRepository): RedirectResponse
    {
        $file_name = $request->get('file') . '.csv';
        $directory = $file_uploader->getTargetDirectory();
        $full_path = $directory . '/' . $file_name;
        //$openfile = fopen($full_path, "r");
        //$cont = fread($openfile, filesize($full_path));
        // Si délimiteur est , de googleSheet
        $csv = array_map('str_getcsv', file($full_path));
        // Si délimiteur est ; de excel
        //$csv = array_map(function($v) { return str_getcsv($v, ';'); }, file($full_path));
        if (explode('part-', $file_name)[0] == 1) {
            $i = 0;
        } else {
            $i = 1;
        }
        $extraction = $extractionsRepository->findOneBy(['name' => explode('part-', $file_name)[1]]);
        $newLine = 0;
        // Passé tous les titres en non présent last extractions
        $options = $configRepository->active($extraction->getImportAt()->format('Y-m-d H:i'));
        foreach($options as $option){
            $config[$option['name']] = $this->lettersToNumber($option['field']) - 1;
        }
        foreach ($csv as $value) {

            if ($i > 0) {
                $verif_titre = $titreRepository->findOneBy(['reference' => $value[$config['Reference']]]);
                // RPRS est un boolean si RPRS est écrit
                $rprs = ($value[$config['Rprs']] == 'RPRS') ? 1 : 0;
                if (empty($verif_titre)) {

                    $titre = new Titre();
                    foreach($config as $cle => $data){
                        $property = 'set'.$cle;
                        if(in_array($cle, ['EnterAt', 'ExitAt', 'CreeAt', 'RejetAt', 'NaissanceAt'])) {
                            $date = DateTime::createFromFormat('d/m/Y', $value[$data]);
                            $titre->{$property}($date);
                        }elseif(in_array($cle, ['Encaissement', 'Montant', 'Restantdu'])) {
                            $prix = $this->priceToFloat($value[$data]);
                            $titre->{$property}($prix);
                        }elseif(in_array($cle, ['Uh'])) {
                            $verif_uh = $uhRepository->findOneBy(['numero' => $value[$data]]);
                            if (isset($verif_uh)) {
                                $titre->setUh($verif_uh);
                            } else {
                                $uh = new Uh();
                                $uh->setNumero($value[$data]);
                                $uh->setDesignation('');
                                $uh->setAntenne('');
                                $em->persist($uh);
                                $em->flush();
                                $titre->setUh($uh);
                            }
                        }elseif(in_array($cle, ['Observation', 'Precision', 'TraiteAt'])) {
                            ;
                        }
                        elseif(in_array($cle, ['Rang', 'Insee'])){
                            $titre->{$property}((int)$value[$data]);
                        }
                        else{
                            $titre->{$property}($value[$data]);
                        }
                    }
                    /*
                    $titre->setType($value[0]);
                    $titre->setClasse($value[1]);
                    $titre->setIep($value[2]);
                    $titre->setIpp($value[3]);
                    $titre->setFacture($value[4]);
                    $titre->setName($value[5]);
                    $titre->setEnterAt(DateTime::createFromFormat('d/m/Y', $value[6]));
                    $titre->setExitAt(DateTime::createFromFormat('d/m/Y', $value[7]));
                    $titre->setMontant($this->priceToFloat($value[8]));
                    $titre->setEncaissement($this->priceToFloat($value[9]));
                    $titre->setRestantdu($this->priceToFloat($value[10]));
                    $titre->setReference($value[11]);
                    $titre->setPec($value[12]);
                    $titre->setLot($value[13]);
                    $titre->setPayeur($value[14]);
                    $titre->setCodeRejet($value[15]);
                    $titre->setDescRejet($value[16]);
                    $titre->setCreeAt(DateTime::createFromFormat('d/m/Y', $value[17]));
                    $titre->setRejetAt(DateTime::createFromFormat('d/m/Y', $value[18]));
                    $titre->setDesignation($value[19]);
                    // pour les Uh on doit vérifier qu'il existe dans la base de donnée
                    $verif_uh = $uhRepository->findOneBy(['numero' => $value[20]]);
                    if (isset($verif_uh)) {
                        $titre->setUh($verif_uh);
                    } else {
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
                    $titre->setNaissanceAt(DateTime::createFromFormat('d/m/Y', $value[23]));
                    $titre->setContrat($value[24]);
                    $titre->setNaissanceHf($value[25]);
                    $titre->setRprs($rprs);
                    */
                    $titre->setExtractionAt($extraction->getImportAt());
                    $titre->setMajAt($extraction->getImportAt());
                    $newLine++;
                    $titre->setIsInLastExtraction(1);
                    $em->persist($titre);
                } else {
                    //si le titre est déjà présent on change la date de mise à jour
                    $verif_titre->setMajAt($extraction->getImportAt());
                    $verif_titre->setRprs($rprs);
                    $verif_titre->setIsInLastExtraction(1);
                    $em->persist($verif_titre);
                }
                $em->flush();
            }
            $i++;
        }
        $extraction->setVerify($extraction->getVerify() + 1);
        $extraction->setNewLine($extraction->getNewLine() + $newLine);
        $em->persist($extraction);
        $em->flush();
        $this->addFlash('success', "Vérification Titre du fichier: '.$file_name.' OK");
        return $this->redirectToRoute('b2_extractions');
    }

    private function priceToFloat($s): float
    {
        // convert "," to "."
        $s = str_replace(',', '.', $s);
        // remove everything except numbers and dot "."
        $s = preg_replace("/[^0-9\.]/", "", $s);
        // remove all seperators from first part and keep the end
        $s = str_replace('.', '', substr($s, 0, -3)) . substr($s, -3);
        // return float
        return (float)$s;
    }

    /**
     *
     * Vérification si déjà un traitement pour les titres / ajout ou maj de ce dernier
     *
     * @param Request $request
     * @param FileUploader $file_uploader
     * @param EntityManagerInterface $em
     * @param ExtractionsRepository $extractionsRepository
     * @param TraitementsRepository $traitementsRepository
     * @param TitreRepository $titreRepository
     * @param ObservationsRepository $observationsRepository
     * @param UserInterface $user
     * @return RedirectResponse
     */
    #[Route('/B2/upload/verify/{file}', name: 'b2_verify')]
    #[IsGranted('ROLE_USER')]
    public function verify(Request               $request, FileUploader $file_uploader, EntityManagerInterface $em,
                           ExtractionsRepository $extractionsRepository,
                           TraitementsRepository $traitementsRepository,
                           TitreRepository       $titreRepository, ObservationsRepository $observationsRepository,
                           ConfigRepository      $configRepository,
                           UserInterface         $user): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // On récupère le fichier
        $file_name = $request->get('file') . '.csv';
        $directory = $file_uploader->getTargetDirectory();
        $full_path = $directory . '/' . $file_name;
        //$file_number = explode('part-', $file_name)[0];

        //$openfile = fopen($full_path, "r");
        //$cont = fread($openfile, filesize($full_path));
        $csv = array_map('str_getcsv', file($full_path));
        if (explode('part-', $file_name)[0] == 1) {
            $i = 0;
        } else {
            $i = 1;
        }
        $extraction = $extractionsRepository->findOneBy(['name' => explode('part-', $file_name)[1]]);
        $maj_obs = 0;
        $options = $configRepository->active($extraction->getImportAt()->format('Y-m-d H:i'));
        foreach($options as $option){
            $config[$option['name']] = $this->lettersToNumber($option['field']) - 1;
        }
        // Vérification de chaque ligne
        foreach ($csv as $value) {

            if ($i > 0) {
                // On récupère le titre existant dans la BDD
                $titre = $titreRepository->findOneBy(['reference' => $value[$config['Reference']]]);

                //Vérifier si traitement existant
                $traitement = $traitementsRepository->findBy(['titre' => $titre->getId()], ['traite_at' => 'DESC'], 1, 0);

                if ($extraction->getWithObs()) {
                    //if($value[28] === ''){
                    if($value[$config['Observation']] === ''){
                        $recherche = 'NOUVEAU';
                    }else{
                        //$recherche = $value[28];
                        $recherche = $value[$config['Observation']];
                    }
                    $observation = $observationsRepository->findOneBy(['name' => $recherche]);
                }else{
                    $observation = null;
                }
                // si l'observation du fichier n'est pas vide et s'il n'y a pas de traitement
                // OU
                // si observation est différente du traitement
                $tttExist = ($traitement ? $traitement[0]->getObservation()->getId() : null);
                if ($extraction->getWithObs()) {
                    if ((!empty($observation) && (empty($traitement))) || ($tttExist) !== $observation->getId()) {
                        // Récupération de l'intitulé d'observation

                        $ttt = new Traitements();
                        $ttt->setTitre($titre);
                        $ttt->setObservation($observation);
                        $ttt->setUser($user);
                        //$ttt->setPrecisions($value[29]);
                        $ttt->setPrecisions($value[$config['Precision']]);
                        if ($value[$config['TraiteAt']] == "") {
                            $valeur30 = DateTimeImmutable::createFromFormat('d/m/Y', $value[$config['CreeAt']]);
                        } else {
                            $valeur30 = DateTimeImmutable::createFromFormat('d/m/Y', $value[$config['TraiteAt']]);
                        }

                        $ttt->setTraiteAt($valeur30);
                        $em->persist($ttt);
                        $em->flush();
                        $maj_obs++;
                    }
                } elseif (empty($traitement)) {
                    $ttt = new Traitements();
                    $newObservation = $observationsRepository->findOneBy(['name' => 'NOUVEAU']);
                    $ttt->setTitre($titre);
                    $ttt->setObservation($newObservation);
                    $ttt->setUser($user);
                    $ttt->setTraiteAt($extraction->getImportAt());
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
        $this->addFlash('success', "Vérification Observation du fichier: '.$file_name.' OK");
        return $this->redirectToRoute('b2_extractions');
    }


    /**
     * Purge des fichiers rapprochés
     *
     * @param ExtractionsRepository $extractionsRepository
     * @param TitreRepository $titreRepository
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('B2/purge', name: 'b2_purge')]
    #[IsGranted('ROLE_USER')]
    public function purge(ExtractionsRepository $extractionsRepository, TitreRepository $titreRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $lastExtraction = $extractionsRepository->findOneBy([], ['import_at' => 'desc']);
        $titres = $titreRepository->findBy(['isInLastExtraction' => false, 'is_rapproche' => false]);
        $nbRapproche = 0;
        $aRapprocher = [];
        foreach ($titres as $titre) {
            // si n'est pas (date de dernière extraction égale à date d'extraction ou date de dernière extraction date de maj
            $nbRapproche++;
            $aRapprocher[] = $titre->getId();

        }
        $sql = $titreRepository->createQueryBuilder('');
        $sql->update(Titre::class, 't')
            ->set('t.is_rapproche', ':key')
            ->setParameter('key', 1)
            ->where('t.id IN (:value)')
            ->setParameter('value', $aRapprocher, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->execute();
        $sql2 = $titreRepository->createQueryBuilder('');
        $sql2->update(Titre::class, 't')
            ->set('t.isInLastExtraction', ':key')
            ->setParameter('key', 0)
            ->getQuery()
            ->execute();

        $lastExtraction->setIsPurge(1);
        $lastExtraction->setRapproche($nbRapproche);
        $em->persist($lastExtraction);
        $em->flush();

        $this->addFlash('info', 'Purge des ' . $nbRapproche . ' titres rapprochés par annulation ou encaissement réussie');
        return $this->redirectToRoute('b2_extractions');
    }


    /**
     * Purge des traitements
     *
     * @param ExtractionsRepository $extractionsRepository
     * @param TitreRepository $titreRepository
     * @param TraitementsRepository $traitementsRepository
     * @param ObservationsRepository $observationsRepository
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('B2/verif_ttt', name: 'b2_purge_ttt')]
    #[IsGranted('ROLE_USER')]
    public function reloadTTT(ExtractionsRepository  $extractionsRepository,
                              TitreRepository        $titreRepository,
                              TraitementsRepository  $traitementsRepository,
                              ObservationsRepository $observationsRepository,
                              EntityManagerInterface $em): Response
    {
        $titres = $titreRepository->findBy(['is_rapproche' => true]);

        // Supprimer les traitements des titres rapprochés
        $aRapprocher = [];
        foreach ($titres as $titre) {
            $aRapprocher[] = $titre->getId();
        }
        if (!empty($aRapprocher)) {
            $sql = $traitementsRepository->deleteTraitementRapproche($aRapprocher);

            $em->flush();
            $this->addFlash('info', 'Purge des ' . $sql . ' traitements rapprochés par annulation ou encaissement réussie');
        }

        // Passer en extraction précédente les titre ne provenant pas de la dernière extraction
        $lastExtraction = $extractionsRepository->findOneBy([], ['import_at' => 'DESC']);
        $idNouveau = $observationsRepository->findOneBy(['name' => 'EXTRACTION PRECEDENTE'])->getId();
        $titresExtractionPrecedente = $titreRepository->findWithTraitementExtractionAtRapproche(0, $lastExtraction->getImportAt()->format('Y-m-d H:i:s'), 'NOUVEAU');
        $i = 0;
        $listAModifier = [];
        foreach ($titresExtractionPrecedente as $aModifier) {
            $listAModifier[] = $traitementsRepository->findOneBy(['titre' => $aModifier['id']], ['traite_at' => 'DESC'])->getId();
            $i++;
        }
        if ($i > 0) {
            $sql = $traitementsRepository->createQueryBuilder('');
            $sql->update(Traitements::class, 't')
                ->set('t.observation', ':key')
                ->setParameter('key', $idNouveau)
                ->where('t.id IN (:value)')
                ->setParameter('value', $listAModifier, Connection::PARAM_INT_ARRAY)
                ->getQuery()
                ->execute();
        }
        $em->flush();
        $this->addFlash('info', 'Passage de  ' . $i . ' traitements "Nouveau" en "Extraction précédente.');

        return $this->redirectToRoute('b2_extractions');
    }


    // EXPORTER EN FICHIER CSV

    /**
     * @param Request $request
     * @param TitreRepository $titreRepository
     * @param TraitementsRepository $traitementsRepository
     * @return BinaryFileResponse
     */
    #[Route('B2/export_csv/{?type}', name: 'b2_export_csv')]
    #[IsGranted('ROLE_USER')]
    public function export(Request $request,TitreRepository $titreRepository,TraitementsRepository $traitementsRepository): BinaryFileResponse
    {
        if($request->get('type') === 'excel'){
            $separator = ';';
            $type = 'excel';
        }else{
            $separator =',';
            $type = 'sheet';
        }
        $titres = $titreRepository->findBy(['is_rapproche' => 0], ['montant' => 'DESC']);
        $export = [];
        foreach($titres as $titre)
        {
            $ttt = $traitementsRepository->findOneBy(['titre' => $titre->getId()], ['traite_at' => 'DESC']);
            $rprs = ($titre->getRprs() == 1 ? 'RPRS' : '');
            // Titre a exporter
            $export[] = [
                $titre->getType(),
                $titre->getClasse(),
                $titre->getIep(),
                $titre->getIpp(),
                $titre->getFacture(),
                $titre->getName(),
                $titre->getEnterAt()->format('d/m/Y'),
                $titre->getExitAt()->format('d/m/Y'),
                $titre->getMontant(),
                $titre->getEncaissement(),
                $titre->getRestantdu(),
                $titre->getReference(),
                $titre->getPec(),
                $titre->getLot(),
                $titre->getPayeur(),
                $titre->getCodeRejet(),
                $titre->getDescRejet(),
                $titre->getCreeAt()->format('d/m/Y'),
                $titre->getRejetAt()->format('d/m/Y'),
                $titre->getDesignation(),
                $titre->getUh()->getNumero(),
                $titre->getInsee(),
                $titre->getRang(),
                $titre->getNaissanceAt()->format('d/m/Y'),
                $titre->getContrat(),
                $titre->getNaissanceHf(),
                $rprs,
                $titre->getRejetAt()->format('d/m/Y'),
                //Obs
                $ttt->getObservation()->getName(),
                //Précisions
                $ttt->getPrecisions(),
                //date ttt
                $ttt->getTraiteAt()->format('d/m/Y')

            ];
        }
        // Nom du fichier
        $date = new DateTime();
        $date = $date->format('dmY');
        $chemin = 'export/export-'.$type.'-'.$date.'.csv';
        $delimiteur = $separator;
        // Création du fichier csv
        // fopen : Ouvre un fichier
        /*
            w+ : Ouvre en lecture et écriture ;
            Place le pointeur de fichier au début du fichier et réduit la taille du fichier à 0.
            Si le fichier n'existe pas, on tente de le créer.
        */
        $fichier_csv = fopen($chemin, 'w+');

        /*
            Si votre fichier a vocation à être importé dans Excel,
            vous devez impérativement utiliser la ligne ci-dessous pour corriger
            les problèmes d'affichage des caractères internationaux (les accents par exemple)
        */
        fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

        // On affiche une fois l'entête sans boucle
        $entetes = array("Type","Classe","Numéro d'entrée","Id. Patient (NIP ou IPP)","Facture","Nom 1","Date de début de l'acte","Date de fin de l'acte","MontantTTCfacture","TotalEncaissement","Restantdû","Référence","PEC","Lot","Payeur","Code rejet NOEMIE","Description rejet NOEMIE","Créé le","Date d'envoi","Désignation","UH","N° INSEE","Rang","Date de naissance","N° Contrat","Naissance HF","RPRS","Date de rejet","Observation","Précision","Date de traitement CTX");

        fputcsv($fichier_csv, $entetes, $delimiteur);

        // Boucle foreach sur chaque ligne du tableau
        // Boucle pour se déplacer dans les tableaux
        foreach($export as $ligneaexporter){
            // chaque ligne en cours de lecture est insérée dans le fichier
            // les valeurs présentes dans chaque ligne seront séparées par la variable $delimiteur
            fputcsv($fichier_csv, $ligneaexporter, $delimiteur);

        }
        // fermeture du fichier csv
        fclose($fichier_csv);
        //$this->addFlash('success', '<a href="{{ asset("export/export.csv") }}">Exportation prête à être téléchargée.</a>');
        $response = new BinaryFileResponse('export/export-'.$type.'-'.$date.'.csv');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'export-'.$type.'-'.$date.'.csv');
        return $response;
    }


    /**
     * @param Request $request
     * @param FileUploader $file_uploader
     * @param EntityManagerInterface $em
     * @param ExtractionsRepository $extractionsRepository
     * @param TraitementsRepository $traitementsRepository
     * @param TitreRepository $titreRepository
     * @param ObservationsRepository $observationsRepository
     * @param UserInterface $user
     * @return RedirectResponse
     */
    #[Route('/B2/upload/stat/{file}', name: 'b2_verify_stat')]
    #[IsGranted('ROLE_USER')]
    public function stat(Request               $request, FileUploader $file_uploader, EntityManagerInterface $em,
                           ExtractionsRepository $extractionsRepository,
                           TraitementsRepository $traitementsRepository,
                           TitreRepository       $titreRepository, ObservationsRepository $observationsRepository,
                           UserInterface         $user): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // On récupère le fichier
        $file_name = $request->get('file') . '.csv';
        $directory = $file_uploader->getTargetDirectory();
        $full_path = $directory . '/' . $file_name;
        //$file_number = explode('part-', $file_name)[0];

        //$openfile = fopen($full_path, "r");
        //$cont = fread($openfile, filesize($full_path));
        $csv = array_map('str_getcsv', file($full_path));
        if (explode('part-', $file_name)[0] == 1) {
            $i = 0;
        } else {
            $i = 1;
        }
        $extraction = $extractionsRepository->findOneBy(['name' => explode('part-', $file_name)[1]]);
        $maj_obs = 0;
        // Vérification de chaque ligne
        foreach ($csv as $value) {


        }
    }

    /*
     * lettersToNumber("A"); //returns 1
     * lettersToNumber("E"); //returns 5
     * lettersToNumber("Z"); //returns 26
     * lettersToNumber("AB"); //returns 28
     * lettersToNumber("AP"); //returns 42
     * lettersToNumber("CE"); //returns 83
     */
    function lettersToNumber($letters){
        $alphabet = range('A', 'Z');
        $number = 0;

        foreach(str_split(strrev($letters)) as $key=>$char){
            $number = $number + (array_search($char,$alphabet)+1)*pow(count($alphabet),$key);
        }
        return $number;
    }

}