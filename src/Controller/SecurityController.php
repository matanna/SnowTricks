<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\EventListener\LogoutListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
    * @Route("/inscription", name="registration")
    */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $registrationForm = $this->createForm(RegistrationType::class, $user);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('security/registration.html.twig', [
            'registrationForm' => $registrationForm->createView()
        ]);
    }

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
