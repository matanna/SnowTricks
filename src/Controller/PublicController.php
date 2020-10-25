<?php

namespace App\Controller;

use App\Repository\TricksRepository;
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
     * @Route("/tricks/{id}", name="show_tricks")
     */
    public function show(TricksRepository $tricksRepository, $id)
    {
       
        $tricks = $tricksRepository->find($id);

        return $this->render('public/show.html.twig', [
            'tricks' => $tricks
        ]);
    }
}
