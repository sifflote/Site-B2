<?php
namespace App\Service;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
class FileUploader
{
    private $targetDirectory;
    private $slugger;
    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }
    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        /*
        if($safeFilename ='rejetB2')
        {
            $fileName = $safeFilename. '.' .$file->guessExtension();
        }else{
        */
            //$fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $fileName = $safeFilename.'-'.uniqid().'.csv';

        //}
        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
            return null; // for example
        }
        return $fileName;
    }
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}