<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Video;
use App\Entity\Tricks;
use App\Form\TricksType;
use App\Form\ChangePhotoType;
use App\Form\ChangeVideoType;
use App\Utils\ManageVideoUrl;
use App\Utils\ManageImageOnServer;
use App\Repository\PhotoRepository;
use App\Repository\VideoRepository;
use App\Repository\TricksRepository;
use App\Form\ChangePrincipalPhotoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class AddTricksController extends AbstractController
{
    /**
     * @Route("/ajout",     name="addTricks")
     * @Route("/edit/{id}", name="editTricks")
     */
    public function addTricks(UserInterface $user, Request $request, 
        Session $session, EntityManagerInterface $manager, 
        TricksRepository $tricksRepository = null,
        PhotoRepository $photoRepository, VideoRepository $videoRepository,
        $id = null
    ) {
        //We check if the user has activated his account 
        if ($user->getActivationToken() != '') {
            throw new \Exception('Vous devez activez votre compte !!');
        }
        //If we want to create a new tricks
        if ($id == null) {
            $tricks = new Tricks();
        //If we want to edit a tricks
        } else {
            $tricks = $tricksRepository->findOneBy(['id' => $id]);
        }
        
        $formTricks = $this->createForm(TricksType::class, $tricks);

        //We create form for the modal for change only one photo or one video
        $changePhotoForm = $this->createForm(ChangePhotoType::class);
        $changeVideoForm = $this->createForm(ChangeVideoType::class);

        //Use ajax for get id photo or video in confirm delete modal
        if ($request->isXmlHttpRequest()) { 
            $photoId = $request->request->get('photoId');
            $videoId = $request->request->get('videoId');
            $action = $request->request->get('action');

            if ($photoId != null) {
                $jsonResponse =$this->render('modal/modifyElement.html.twig', [
                    'tricks' => $tricks,
                    'photoId' => $photoId,
                    'action' => $action,
                    'changePhotoForm' => $changePhotoForm->createView()
                ]);
                $session->set('photoId', $photoId);

            } elseif ($videoId != null) {
                $jsonResponse =$this->render('modal/modifyElement.html.twig', [
                    'tricks' => $tricks,
                    'videoId' => $videoId,
                    'action' => $action,
                    'changeVideoForm' => $changeVideoForm->createView()
                ]);
                $session->set('videoId', $videoId);

            } else {
                throw new \Exception('Cet élément n\'existe pas');
            }
            return new JsonResponse(['modal' => $jsonResponse->getContent()]);
        }

        //If the user want to change only one photo - The form is in the modal
        $changePhotoForm->handleRequest($request);
        if ($changePhotoForm->isSubmitted() && $changePhotoForm->isValid()) {
            $photo = $changePhotoForm->get('photo')->getData();

            if ($photo) {
                $photoToEdit = $photoRepository->find($session->get('photoId'));
                $manageImageOnServer = new ManageImageOnServer();
                $manageImageOnServer->removeImageOnServer(
                    $photoToEdit->getNamePhoto(),
                    $this->getParameter('images_directory')
                );

                if ($photoToEdit->getNamePhoto() == $tricks->getPrincipalPhoto()) {
                    $tricks->setPrincipalPhoto(null);
                }

                $newNamePhoto = $manageImageOnServer->copyImageOnServer(
                    $photo, 
                    $this->getParameter('images_directory')
                );
                //We change the photoName in the entity
                $photoToEdit->setNamePhoto($newNamePhoto);
                $manager->persist($photoToEdit);

                $tricks->setDateAtUpdate(new \Datetime());
                $manager->persist($tricks);

                $manager->flush();
                $session->remove('photoId');
            }
        }

        //if the user want to change only one video - The form is in the modal
        $changeVideoForm->handleRequest($request);
        if ($changeVideoForm->isSubmitted() && $changeVideoForm->isValid()) {
            $videoLink = $changeVideoForm->get('video')->getData();
            dump($videoLink);
            if ($videoLink != null) {
                $videoToEdit = $videoRepository->find($session->get('videoId'));

                $newVideo = new ManageVideoUrl();
                $newVideo = $newVideo->getParametersOnUrl(
                    $videoLink, 
                    $this->getParameter('host_accepted_videos')
                );
                //We change the video link in the entity
                $videoToEdit->setLink($newVideo);

                $manager->persist($videoToEdit);

                $tricks->setDateAtUpdate(new \Datetime());
                $manager->persist($tricks);

                $manager->flush();
                $session->remove('VideoId');
            }
        }

        //Form for update a tricks
        $formTricks->handleRequest($request);
        if ($formTricks->isSubmitted() && $formTricks->isValid()) {
            //We get the images and the videos link from the form fields in array
            $photos = $formTricks->get('photos')->getData();
            $videos = $formTricks->get('videos')->getData();

            if ($photos) {
                foreach ($photos as $photo) {
                    //We copy the image on server
                    $copyPhoto = new ManageImageOnServer();
                    $newNamePhoto = $copyPhoto->copyImageOnServer(
                        $photo, 
                        $this->getParameter('images_directory')
                    );
                    //We add photo in Photo collection
                    $photoTricks = new Photo();
                    $photoTricks->setNamePhoto($newNamePhoto);
                    $tricks->addPhoto($photoTricks);
                }
            }

            if ($videos) {
                foreach ($videos as $videoLink) {
                    $videoTricks = new Video();

                    if ($videoLink != null) {

                        $video = new ManageVideoUrl();
                        $video = $video->getParametersOnUrl(
                            $videoLink, 
                            $this->getParameter('host_accepted_videos')
                        );

                        $videoTricks->setLink($video);
                        $tricks->addVideo($videoTricks);
                    }
                }
            }
            
            if ($tricks->getDateAtCreated() == null) {
                $tricks->setDateAtCreated(new \Datetime());
            }
            $tricks->setDateAtUpdate(new \Datetime())
                   ->setUser($user);

            $manager->persist($tricks);
            $manager->flush();
            
            return $this->redirectToRoute('show_tricks', ['id' => $tricks->getId()]);
        }

        return $this->render('member/addTricks.html.twig', [
            'formTricks' => $formTricks->createView(),
            'tricks' => $tricks,
        ]);
    }

    /**
     * @Route("/edit-photo-principal/{tricksId}/{photoId}", name="change-principal-photo")
     */
    public function modifyPrincipalPhoto(TricksRepository $tricksRepository, 
        PhotoRepository $photoRepository, EntityManagerInterface $manager,
        $tricksId, $photoId
    ) {
        $tricks = $tricksRepository->find($tricksId);

        if ($tricks) {
            $photo = $photoRepository->findOneBy([
                'id' => $photoId
            ]);

            $tricks->setPrincipalPhoto($photo->getNamePhoto());
            $manager->persist($tricks);
            $manager->flush();

            return $this->redirectToRoute('member_editTricks', ['id' => $tricksId]);
        }
    }
}
