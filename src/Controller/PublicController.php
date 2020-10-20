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
        $tricks = $tricksRepository->findAll();

        return $this->render('public/index.html.twig', [
            'tricks' => $tricks
        ]);
    }
}
