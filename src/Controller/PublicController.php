<?php

namespace App\Controller;

use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PublicController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TricksRepository $tricksRepository)
    {
        $tricks = $tricksRepository->findFirstTricks(12);

        return $this->render('public/index.html.twig', [
            'tricks' => $tricks
        ]);
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
