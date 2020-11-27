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
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class AddTricksController extends AbstractController
{
    /**
     * @Route("/ajout",       name="addTricks")
     * @Route("/edit/{name}", name="editTricks")
     */
    public function addTricks(UserInterface $user, Request $request, 
        NotifierInterface $notifier, $name = null
    ) {
        $session = new Session();

        //If we want to create a new tricks
        if ($name == null) {
            $tricks = new Tricks();
        //If we want to edit a tricks
        } else {
            //$id = $tricksRepository->findTricksIdByName($name);
            $tricksRepository = $this->getDoctrine()->getRepository(Tricks::class);
            $tricks = $tricksRepository->findOneBy(['name' => $name]);
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
                    'photoId' => explode("-", $photoId)[0],
                    'action' => $action,
                    'changePhotoForm' => $changePhotoForm->createView()
                ]);
                //When the modal open for change one photo, we create session videoId
                $session->set('photoId', $photoId);

            } elseif ($videoId != null) {
                $jsonResponse =$this->render('modal/modifyElement.html.twig', [
                    'tricks' => $tricks,
                    'videoId' => explode("-", $videoId)[0],
                    'action' => $action,
                    'changeVideoForm' => $changeVideoForm->createView()
                ]);
                //When the modal open for change one video, we create session videoId
                $session->set('videoId', $videoId);

            } else {
                throw new \Exception('Cet élément n\'existe pas');
            }
            return new JsonResponse(['modal' => $jsonResponse->getContent()]);
        }

        //If the user want to change only one photo - The form is in the modal
        $this->changeOnePhoto($request, $changePhotoForm, $tricks);

        //if the user want to change only one video - The form is in the modal
        $this->changeOneVideo($request, $changeVideoForm, $tricks);

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

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($tricks);
            $manager->flush();
            
            $notifier->send(new Notification('Le tricks a été ajouté avec succès !', ['browser']));

            return $this->redirectToRoute('show_tricks', ['name' => $tricks->getName()]);
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
        EntityManagerInterface $manager, $tricksId, $photoId
    ) {
        $tricks = $tricksRepository->find($tricksId);

        if ($tricks) {
            $photoRepository = $this->getDoctrine()->getRepository(Photo::class);
            $photo = $photoRepository->findOneBy([
                'id' => $photoId
            ]);

            $tricks->setPrincipalPhoto($photo->getNamePhoto());
            $manager->persist($tricks);
            $manager->flush();

            return $this->redirectToRoute('member_editTricks', [
                'name' => $tricks->getName()]
            );
        }
    }

    /**
     * This method is use for change only one photo in the page for modify one tricks
     */
    private function changeOnePhoto(Request $request, $changePhotoForm, $tricks)
    {
        $changePhotoForm->handleRequest($request);
        if ($changePhotoForm->isSubmitted() && $changePhotoForm->isValid()) {
            $photo = $changePhotoForm->get('photo')->getData();

            $session = $request->getSession();

            if (!$photo && $session->get('photoId') == null) {
                return;
            }

            $photoRepository = $this->getDoctrine()->getRepository(Photo::class);
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
                $photo, $this->getParameter('images_directory')
            );
            //We change the photoName in the entity
            $photoToEdit->setNamePhoto($newNamePhoto);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($photoToEdit);

            $tricks->setDateAtUpdate(new \Datetime());
            $manager->persist($tricks);

            $manager->flush();
            $session->remove('photoId');
        }
    }

    /**
     * This method is use for change only one video in the page for modify one tricks
     */
    private function changeOneVideo(Request $request, $changeVideoForm, $tricks) 
    {
        $changeVideoForm->handleRequest($request);
        if ($changeVideoForm->isSubmitted() && $changeVideoForm->isValid()) {
            $videoLink = $changeVideoForm->get('video')->getData();
            
            $session = $request->getSession();

            if ($videoLink == null || $session->get('videoId') == null) {
                return;
            }

            $videoRepository = $this->getDoctrine()->getRepository(Video::class);
            $videoToEdit = $videoRepository->find($session->get('videoId'));

            $newVideo = new ManageVideoUrl();
            $newVideo = $newVideo->getParametersOnUrl(
                $videoLink, 
                $this->getParameter('host_accepted_videos')
            );
            //We change the video link in the entity
            $videoToEdit->setLink($newVideo);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($videoToEdit);

            $tricks->setDateAtUpdate(new \Datetime());
            $manager->persist($tricks);

            $manager->flush();
            $session->remove('videoId');
        
        }
    }
}
