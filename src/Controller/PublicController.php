<?php

namespace App\Controller;

use App\Repository\TricksRepository;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function show(TricksRepository $tricksRepository, MessageRepository $messageRepository, $name, Request $request)
    {
        $id = $tricksRepository->findTricksIdByName($name);
        dump($id);

        if ($request->isXmlHttpRequest()) {

            $offset = (($request->request->get('numPage'))-1)*10;
            
            $nextMessages = $messageRepository->findByTricks($id, 10, $offset);
            $jsonResponse =$this->render('public/nextMessages.html.twig', [
                'nextMessages' => $nextMessages
            ]);

            return new JsonResponse(['body' => $jsonResponse->getContent()]);
           
        }

        $tricks = $tricksRepository->find($id);

        $numberMessages = $messageRepository->countByTricks($id);
        $messages = $messageRepository->findByTricks($id, 10, 0);

        return $this->render('public/show.html.twig', [
            'tricks' => $tricks,
            'messages' => $messages,
            'numberMessages' => (int)$numberMessages
        ]);
    }
}
