<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Video;
use App\Entity\Tricks;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
* @Route("/membre", name="member_")
*/
class MemberController extends AbstractController
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

        if ($formTricks->isSubmitted() && $formTricks->isValid()) {
            //We get the images and the videos iframe from the form fields in array
            $photos = $formTricks->get('photos')->getData();
            $videos = $formTricks->get('videos')->getData();

            if ($photos) {
                foreach ($photos as $photo) {
                    //We call private method in this controller for copy images on the server
                    $file = $this->copyTricksPhotosOnServer($photo);
                    //Name photo is placed in Photo entity and add in Photo collection in Tricks entity
                    $photoTricks = new Photo();
                    $photoTricks->setNamePhoto($file);
                    $tricks->addPhoto($photoTricks);
                }
            }

            if ($videos) {
                foreach ($videos as $video) {
                    //iframe video is placed in Video entity and add in Video collection in Tricks entity
                    $videoTricks = new Video();
                    $videoTricks->setLink($video);
                    $tricks->addVideo($videoTricks);
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
    
    /**
     * This function is use for copy images on server
     */
    private function copyTricksPhotosOnServer($photo) 
    {
        $file = md5(uniqid()) . '.' . $photo->guessExtension();
        try {
            $photo->move(
                $this->getParameter('images_directory'),
                $file
            ); 
            return $file;
        } catch (FileException $e){
            echo $e->getMessage();
        }
    }
}
