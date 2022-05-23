<?php
namespace App\Controller\B2;

use App\Entity\B2\Extractions;
use App\Entity\B2\Titre;
use App\Entity\B2\Uh;
use App\Form\FileUploadType;
use App\Repository\B2\ExtractionsRepository;
use App\Repository\B2\TitreRepository;
use App\Repository\B2\UhRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class B2Controller extends AbstractController
{

    #[Route('/B2/titres', name: 'b2_titres')]
    public function titres(TitreRepository $titreRepository): Response
    {
        return $this->render('B2/titres.html.twig', [
            'titres' => $titreRepository->findAll([],
                ['montant' => 'desc'])
        ]);
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
                [$value => $order] ),
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
    #[Route('/B2/titres/{filter}/{value}', name:'b2_ipp')]
    public function show_filter(Request $request, TitreRepository $titreRepository): Response
    {
        $filter = $request->get('filter');
        return $this->render('B2/titres.html.twig', [
            'titres' => $titreRepository->findBy([
                $filter => $request->get('value')
            ],
                ['montant' => 'desc'])
        ]);
    }


    #[Route('/B2/extractions', name: 'b2_extractions')]
    public function extractions(ExtractionsRepository $extractionsRepository): Response
    {
        return $this->render('B2/extract_list.html.twig', [
          'extractions' => $extractionsRepository->findAll([],
              ['import_at' => 'desc'])
        ]);
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
                    $splitSize = 1000;
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
    public function lecture(Request $request, FileUploader $file_uploader, EntityManagerInterface $em, UhRepository $uhRepository, ExtractionsRepository $extractionsRepository)
    {
        $file_name = $request->get('file') . '.csv';
        $directory = $file_uploader->getTargetDirectory();
        $full_path = $directory.'/'.$file_name;
        $file_number = explode('part-', $file_name)[0];
        dump($file_number . ' et ' . explode('part-', $file_name)[1]);



        $openfile = fopen($full_path, "r");
        $cont = fread($openfile, filesize($full_path));
        $csv = array_map('str_getcsv', file($full_path));
        if(explode('part-', $file_name)[0] == 1){ $i = 0; }else{ $i=1; }
        $now  = new DateTimeImmutable();
        $extraction = $extractionsRepository->findOneBy(['name' => explode('part-', $file_name)[1]]);

        foreach($csv as $ligne => $value)
        {
            if($i > 0){

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
                $titre->setEncaissement($this->priceToFloat($value[8]));
                $titre->setRestantdu($this->priceToFloat($value[8]));
                $titre->setReference($value[11]);
                $titre->setPec($value[12]);
                $titre->setLot($value[13]);
                $titre->setPayeur($value[14]);
                $titre->setCodeRejet($value[15]);
                $titre->setDescRejet($value[16]);
                $titre->setCreeAt(\DateTime::createFromFormat('d/m/Y', $value[17]));
                $titre->setRejetAt(\DateTime::createFromFormat('d/m/Y', $value[18]));
                $titre->setDesignation($value[19]);
                $titre->setInsee((int)$value[21]);
                $titre->setRang((int)$value[22]);
                $titre->setNaissanceAt(\DateTime::createFromFormat('d/m/Y', $value[23]));
                $titre->setContrat($value[24]);
                $titre->setNaissanceHf($value[25]);
                // RPRS est un boolean si RPRS est écrit
                $rprs = ($value[26] == 'RPRS') ? 1 : 0;
                $titre->setRprs($rprs);
                $titre->setExtractionAt($now);
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
                $em->persist($titre);
                $em->flush();
            }
            $i++;
        }
        $extraction->setVerify($extraction->getVerify() + 1);
        $em->persist($extraction);
        $em->flush();
        $this->addFlash('success', 'Lecture du fichier');
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