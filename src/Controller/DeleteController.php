<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Video;
use App\Entity\Tricks;
use App\Utils\ManageImageOnServer;
use App\Repository\PhotoRepository;
use App\Repository\VideoRepository;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class DeleteController extends AbstractController
{
    /**
     * @Route("/supprimer/tricks/{id}", name="delete_tricks")
     */
    public function deleteTricks(UserInterface $user,  
        NotifierInterface $notifier, $id
    ) {
        //We get the tricks by the route parameter
        $tricksRepository = $this->getDoctrine()->getRepository(Tricks::class);
        $tricks = $tricksRepository->find($id);
        if (!$tricks) {
            throw new \Exception('Ce tricks n\'existe pas'); 
        }
        
        //We get photos if the tricks exist
        $photoRepository = $this->getDoctrine()->getRepository(Photo::class);
        $photos = $photoRepository->findBy(['tricks' => $tricks]);

        //We loop on $photos array for delete image on server
        foreach ($photos as $photo) {
            $manageImageOnServer = new ManageImageOnServer();
            $manageImageOnServer-> removeImageOnServer(
                $photo->getNamePhoto(), 
                $this->getParameter('images_directory')
            );
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($tricks);
        $manager->flush();
        
        $notifier->send(new Notification("Le tricks " . $tricks->getName() . " a bien été supprimé !", ['browser']));

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/supprimer/photo/{tricksId}/{photoId}", name="delete_photo")
     */
    public function deletePhoto(TricksRepository $tricksRepository, 
        EntityManagerInterface $manager, $tricksId, $photoId
    ) {
        //We get the tricks by the route parameter
        $tricks = $tricksRepository->findOneBy(['id' => $tricksId]);
        if (!$tricks) {
              throw new \Exception('Ce tricks n\'existe pas');
        }
        //We get the photo if the tricks exist
        $photoRepository = $this->getDoctrine()->getRepository(Photo::class);
        $photo = $photoRepository->findOneBy(['id' => $photoId]);
        if (!$photo) {
            throw new \Exception('Cette photo n\'existe pas');
        }
        
        $deletePhoto = new ManageImageOnServer();
        $deletePhoto->removeImageOnServer(
            $photo->getNamePhoto(), 
            $this->getParameter('images_directory')
        );

        if ($photo->getNamePhoto() == $tricks->getPrincipalPhoto()) {
            $tricks->setPrincipalPhoto(null);
        }

        $tricks->removePhoto($photo);
        $manager->persist($photo);

        $tricks->setDateAtUpdate(new \Datetime());
        $manager->persist($tricks);

        $manager->flush();

        return $this->redirectToRoute('member_editTricks', [
            'name' => $tricks->getName()
        ]);
    }

    /**
     * @Route("/supprimer/video/{tricksId}/{videoId}", name="delete_video")
     */
    public function deleteVideo(UserInterface $user,
        EntityManagerInterface $manager, $tricksId, $videoId
    ) {     
        //We get the tricks by the route parameter
        $tricksRepository = $this->getDoctrine()->getRepository(Tricks::class);
        $tricks = $tricksRepository->findOneBy(['id' => $tricksId]);
        if (!$tricks) {
              throw new \Exception('Ce tricks n\'existe pas');
        }
        //We get the video if the tricks exist
        $videoRepository = $this->getDoctrine()->getRepository(Video::class);
        $video = $videoRepository->findOneBy(['id' => $videoId]);
        if (!$video) {
            throw new \Exception('Cette video n\'existe pas');
        }
        
        $tricks->removeVideo($video);
        $manager->persist($video);

        $tricks->setDateAtUpdate(new \Datetime());
        $manager->persist($tricks);

        $manager->flush();

        return $this->redirectToRoute('member_editTricks', [
            'name' => $tricks->getName()
        ]);
    }
}
