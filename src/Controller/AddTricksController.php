<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Video;
use App\Entity\Tricks;
use App\Form\TricksType;
use App\Utils\ManageImageOnServer;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        EntityManagerInterface $manager, TricksRepository $tricksRepository = null, 
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
        $formTricks->handleRequest($request);

        //Use ajax for get id photo or video in confirm delete modal
        if ($request->isXmlHttpRequest()) { 
            $photoId = $request->request->get('photoId');
            $videoId = $request->request->get('videoId');

            if ($photoId != null) {
                $jsonResponse =$this->render('modal/confirmDelete.html.twig', [
                    'tricks' => $tricks,
                    'photoId' => $photoId
                ]);

            } elseif ($videoId != null) {
                $jsonResponse =$this->render('modal/confirmDelete.html.twig', [
                    'tricks' => $tricks,
                    'videoId' => $videoId
                ]);

            } else {
                throw new \Exception('Cet élément n\'existe pas');
            }

            return new JsonResponse(['modal' => $jsonResponse->getContent()]);
        }

        
        if ($formTricks->isSubmitted() && $formTricks->isValid()) {
            //We get the images and the videos iframe from the form fields in array
            $photos = $formTricks->get('photos')->getData();
            $videos = $formTricks->get('videos')->getData();

            if ($photos) {
                foreach ($photos as $photo) {
                    //We copy the image on server
                    $copyPhoto = new ManageImageOnServer($photo, $tricks, $this->getParameter('images_directory'));
                    $newNamePhoto = $copyPhoto->copyImageOnServer();
                    //We add photo in Photo collection
                    $photoTricks = new Photo();
                    $photoTricks->setNamePhoto($newNamePhoto);
                    $tricks->addPhoto($photoTricks);
                }
            }

            if ($videos) {
                foreach ($videos as $video) {
                    //iframe video is placed in Video entity and add in Video collection in Tricks entity
                    $videoTricks = new Video();
                    //If video field is emty, we do anything
                    if ($video != null) {
                        $videoTricks->setLink($video);
                        $tricks->addVideo($videoTricks);
                    }
                }
            }
            
            $tricks->setDateAtCreated(new \Datetime())
                   ->setUser($user);

            $manager->persist($tricks);
            $manager->flush();
            
            return $this->redirectToRoute('show_tricks', ['id' => $tricks->getId()]);
        }

        return $this->render('member/addTricks.html.twig', [
            'formTricks' => $formTricks->createView(),
            'tricks' => $tricks
        ]);
    }
}
