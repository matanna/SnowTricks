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
    private $_image;

    private $_entity;

    private $_directory;

    public function __construct(UploadedFile $image, $entity, $directory)
    {
        $this->_image = $image;
        $this->_entity = $entity;
        $this->_directory = $directory;
    }

    /**
     * This function copy image on server in the correct folder depending on the entity 
     */
    public function copyImageOnServer()
    {
        $entityName = new \ReflectionClass($this->_entity);
        $entityName->getShortName();

        if ($entityName->getShortName() == 'Tricks') {
            
            $file = md5(uniqid()) . '.' . $this->_image->guessExtension();
            try {
                $this->_image->move(
                    $this->_directory,
                    $file
                ); 
                return $file;
            } catch (FileException $e){
                echo $e->getMessage();
            }
        }  
    }
}