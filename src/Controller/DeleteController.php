<?php

namespace App\Controller;

use App\Repository\PhotoRepository;
use App\Repository\VideoRepository;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class DeleteController extends AbstractController
{
    /**
     * @Route("/supprimer/tricks/{id}", name="delete_tricks")
     */
    public function deleteTricks(UserInterface $user, Request $request, 
        TricksRepository $tricksRepository, EntityManagerInterface $manager, $id)
    {
        //We check if the user has activated his account 
        if ($user->getActivationToken() != '') {
            throw new \Exception('Vous devez activez votre compte !!');
        }

        $tricks = $tricksRepository->findOneBy(array('id' => $id));

        if ($tricks) {
            $manager->remove($tricks);
            $manager->flush();
        }

        return $this->redirectToRoute('home');

    }

    /**
     * @Route("/supprimer/photo/{tricksId}/{photoId}", name="delete_photo")
     */
    public function deletePhoto(UserInterface $user, Request $request, 
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
        
        $tricks->removePhoto($photo);
        $manager->persist($photo);
        $manager->flush();

        return $this->redirectToRoute('member_editTricks', [
            'id' => $tricksId
        ]);

    }

    /**
     * @Route("/supprimer/video/{tricksId}/{videoId}", name="delete_video")
     */
    public function deleteVideo(UserInterface $user, Request $request, 
        TricksRepository $tricksRepository, VideoRepository $videoRepository,
        EntityManagerInterface $manager, $tricksId, $videoId
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
        $video = $videoRepository->findOneBy(['id' => $videoId]);
        if (!$video) {
            throw new \Exception('Cette video n\'existe pas');
        }
        
        $tricks->removeVideo($video);
        $manager->persist($video);
        $manager->flush();

        return $this->redirectToRoute('member_editTricks', [
            'id' => $tricksId
        ]);
    }
}
