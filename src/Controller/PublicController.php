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
            /*
            $allTricks = $tricksRepository->findMoreTricks(12);
            $jsonResponse = [];
            $i = 0;
            foreach ($allTricks as $oneTricks) {
                
                $photos = $oneTricks->getPhotos();

                $result = array(
                    'id' => $oneTricks->getId(),
                    'name' => $oneTricks->getName(),
                    'description' => $oneTricks->getDescription(),
                    'category' => $oneTricks->getCategory(),
                    'user' => $oneTricks->getUser(),
                    'dateAtCreated' => $oneTricks->getDateAtCreated(),
                    'dateAtUpdate' => $oneTricks->getDateAtUpdate(),
                    'photo' => $photos[0]->getName()
                );
                $jsonResponse[$i++] = $result;
            }
            
            return new JsonResponse($jsonResponse);
            */
            return $this->render('nextTricks.html.twig', array('tricks' => $allTricks));

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
