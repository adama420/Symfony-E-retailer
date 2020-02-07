<?php


namespace App\Service;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{
    private $uploadDir;

    public function __construct($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    /**
     * $this->uploader->upload($file)
     */
    public function upload(UploadedFile $image)
    {
        $fileName = uniqid().'.'.$image->guessExtension();
        $image->move($this->uploadDir, $fileName);

        return $fileName;
    }

    public function remove($fileName)
    {
        $fs = new Filesystem();
        $file = $this->uploadDir.'/'.$fileName;

        if($fs->exists($file)){
            $fs->remove($file);
        }
    }
}