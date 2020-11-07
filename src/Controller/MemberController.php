<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Tricks;
use App\Form\TricksType;
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
     * @Route("/ajout", name="addTricks")
     */
    public function addTricks(UserInterface $user, Request $request, EntityManagerInterface $manager)
    {
        //We check if the user has activated his account 
        if ($user->getActivationToken() != '') {
            throw new \Exception('Vous devez activez votre compte !!');
        }

        $tricks = new Tricks();

        $formTricks = $this->createForm(TricksType::class, $tricks);

        $formTricks->handleRequest($request);

        if ($formTricks->isSubmitted() && $formTricks->isValid()) {

            //We copy the images to the server
            $photos = $formTricks->get('photos')->getData();
            
            if ($photos) {
                foreach ($photos as $photo) {

                    //We call private method in this controller
                    $file = $this->copyTricksPhotosOnServer($photo);

                    //Name photo is placed in Photo entity and add in Photo collection in Tricks entity
                    $photoTricks = new Photo();
                    $photoTricks->setNamePhoto($file);
                    $tricks->addPhoto($photoTricks);
                    
                }
            }
            $tricks->setDateAtCreated(new \Datetime())
                   ->setUser($user);

            $manager->persist($tricks);
            $manager->flush();
            
            return $this->render('public/show.html.twig', [
                'tricks' => $tricks
            ]);
        }

        return $this->render('member/addTricks.html.twig', [
            'formTricks' => $formTricks->createView()
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
