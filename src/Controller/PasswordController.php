<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Event\RegisterUserEvent;
use App\Form\ForgotPasswordType;
use App\Event\ForgotPasswordEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class PasswordController extends AbstractController
{
    /**
     * @Route("/password-perdu", name="forgot_password")
     */
    public function forgotPassword(Request $request, UserRepository $userRepository,
        EntityManagerInterface $manager, EventDispatcherInterface $dispatcher
    ) {

        $forgotPasswordForm = $this->createForm(ForgotPasswordType::class);
        $forgotPasswordForm -> handleRequest($request);

        if ($forgotPasswordForm->isSubmitted() && $forgotPasswordForm->isValid()) {
            $username = $forgotPasswordForm->get('username')->getData();

            $user = $userRepository->findOneBy(['username' => $username]);

            if (!$user) {
                return $this->render('security/forgotPassword.html.twig', [
                    'forgotPasswordForm' => $forgotPasswordForm->createView(),
                    'message' => 'Cet identifiant n\'existe pas !'
                ]);
            }

            //We create token for reset password 
            $resetPasswordToken = md5(uniqid());
            $user->setResetPasswordToken($resetPasswordToken);

            $manager->persist($user);
            $manager->flush();

            //we create an event and dispatch it for the listener - ForgotPasswordSubscriber
            $event = new ForgotPasswordEvent($user);
            $dispatcher->dispatch($event, ForgotPasswordEvent::NAME);

            return $this->render('security/forgotPassword.html.twig', [
                'forgotPasswordForm' => $forgotPasswordForm->createView(),
                'message' => 'Le lien de réinitialisation de mot de passe a été envoyé !'
            ]);
        }
        
        return $this->render('security/forgotPassword.html.twig', [
            'forgotPasswordForm' => $forgotPasswordForm->createView()
        ]);
    }

    /**
     * @Route("/reinitialiser-password/{resetPasswordToken}", name="reset_password")
     */
    public function resetPassword(UserRepository $userRepository, 
        Request $request,EntityManagerInterface $manager, 
        UserPasswordEncoderInterface $encoder, $resetPasswordToken
    ) {
        //We get the user tha match this token
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findOneBy([
            'resetPasswordToken' => $resetPasswordToken
        ]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $resetPasswordForm = $this->createForm(ResetPasswordType::class, $user);

        $resetPasswordForm->handleRequest($request);
        if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {
            
            $user->setResetPasswordToken(null);

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            
            return $this->redirectToRoute('login');
        }

        return $this->render('security/resetPassword.html.twig', [
            'resetPasswordForm' => $resetPasswordForm->createView(),
            'username' => $user->getUsername()
        ]);

    }
}
