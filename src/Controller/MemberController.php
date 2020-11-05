<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Form\TricksType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class MemberController extends AbstractController
{
    /**
     * @Route("/ajout", name="addTricks")
     */
    public function addTricks(UserInterface $user)
    {
        if ($user->getActivationToken() != '') {
            throw new \Exception('Vous devez activez votre compte !!');
        }

        $tricks = new Tricks();

        $formTricks = $this->createForm(TricksType::class, $tricks);

        return $this->render('member/addTricks.html.twig', [
            'formTricks' => $formTricks->createView()
        ]);
    }
}
