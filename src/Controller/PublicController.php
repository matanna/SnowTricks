<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\TricksRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TricksRepository $tricksRepository, Request $request)
    {
        
        if ($request->isXmlHttpRequest()) {
            
            $allTricks = $tricksRepository->findMoreTricks(12);
            
            $jsonResponse = $this->render('public/nextTricks.html.twig', array('tricks' => $allTricks));
            return new JsonResponse(['body' => $jsonResponse->getContent()]);

        } else {
            
            $tricks = $tricksRepository->findFirstTricks(12);

            return $this->render('public/index.html.twig', ['tricks' => $tricks]);
        }  
    }

    /**
     * @Route("/tricks/{name}", name="show_tricks")
     */
    public function show(TricksRepository $tricksRepository,  
        MessageRepository $messageRepository, $name, Request $request,
        EntityManagerInterface $manager, UserInterface $user = null
    ) {
        //We get id of tricks by slug 'name' in the route
        $id = $tricksRepository->findTricksIdByName($name);

        //We create form for add a new message
        $newMessage = new Message();
        $messageForm = $this->createForm(MessageType::class, $newMessage);

        //Display more messages in ajax
        if ($request->isXmlHttpRequest()) {
            $offset = (($request->request->get('numPage'))-1)*10;
            
            $nextMessages = $messageRepository->findByTricks($id, 10, $offset);
            $jsonResponse =$this->render('public/nextMessages.html.twig', [
                'nextMessages' => $nextMessages
            ]);
            return new JsonResponse(['body' => $jsonResponse->getContent()]);
        }

        //We get the tricks and all tricks messages
        $tricks = $tricksRepository->find($id);

        $numberMessages = $messageRepository->countByTricks($id);
        $messages = $messageRepository->findByTricks($id, 10, 0);

        //Addmessage form process and check if the user is authenticate
        $messageForm->handleRequest($request);
        if ($user && $messageForm->isSubmitted() && $messageForm->isValid()) {
            
            //We check if the user account is activate
            if ($user->getActivationToken() != '') {
                throw new \Exception('Vous devez activez votre compte !!');
            }

            $newMessage->setUser($user)
                       ->setDateMessage(new \Datetime())
                       ->setTricks($tricks);
            $manager->persist($newMessage);
            $manager->flush();

            return $this->redirectToRoute('show_tricks', [
                'name' => $tricks->getName()
            ]);
        }

        return $this->render('public/show.html.twig', [
            'tricks' => $tricks,
            'messages' => $messages,
            'numberMessages' => (int)$numberMessages,
            'messageForm' => $messageForm->createView()
        ]);
    }
}
