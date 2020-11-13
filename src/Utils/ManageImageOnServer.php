<?php

namespace App\Utils;

use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


/**
 * This class manage the image on server : Add image, remove Image
 */
class ManageImageOnServer
{
    /**
     * This function copy image on server in the correct folder depending on the entity 
     */
    public function copyImageOnServer(UploadedFile $image, $directory)
    {  
        $file = md5(uniqid()) . '.' . $image->guessExtension();
        try {
            $image->move($directory, $file); 
            return $file;
        } catch (FileException $e){
            echo $e->getMessage();
        }
    }

    /**
     * This function remove image on server in the correct folder depending on the entity 
    */
    public function removeImageOnServer($imageName, $directory)
    {
        try {
            unlink($directory . '/' . $imageName);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}