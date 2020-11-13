<?php

namespace App\Controller;

use App\Repository\PhotoRepository;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class ChangePhotoVideoController extends AbstractController
{
    /**
     * @Route("/modifier/photo/{tricksId}/{photoId}", name="change_photo")
     */
    public function changePhoto(UserInterface $user, Request $request, 
        TricksRepository $tricksRepository, PhotoRepository $photoRepository,
        EntityManagerInterface $manager, $tricksId, $photoId
    ) {
        //We check if the user has activated his account 
        if ($user->getActivationToken() != '') {
            throw new \Exception('Vous devez activez votre compte !!');
        }

        //We get the tricks in the route
        $tricks = $tricksRepository->findOneBy(['id' => $tricksId]);
        if (!$tricks) {
              throw new \Exception('Ce tricks n\'existe pas');
        }
        //We get the photo if the tricks exist
        $photo = $photoRepository->findOneBy(['id' => $photoId]);
        if (!$photo) {
            throw new \Exception('Cette photo n\'existe pas');
        }

        //Delete Photo on server... To Do

        $tricks->removePhoto($photo);
        $newPhoto = $request->files->get('change-photo');
        dump($newPhoto);
    }
}
