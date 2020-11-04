<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Form\TricksType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class MemberController extends AbstractController
{
    /**
     * @Route("/ajout", name="addTricks")
     */
    public function addTricks()
    {
        $tricks = new Tricks();

        $formTricks = $this->createForm(TricksType::class, $tricks);

        return $this->render('member/addTricks.html.twig', [
            'formTricks' => $formTricks->createView()
        ]);
    }
}
