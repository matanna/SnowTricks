<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Security $security)  
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        
        if ($security->getUser()) {
            return $this->redirectToRoute('logout');
        }

        return $this->render('security/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error'        => $error,
        ]);
    }

    /**
     * @Route("/deconnexion", name="logout")
     */
    public function logout():void
    {
        throw new \Exception('Ce message ne doit pas apparaitre');
    }

}
