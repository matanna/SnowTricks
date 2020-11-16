<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\RegisterUserEvent;
use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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

            $resetPasswordToken = md5(uniqid());
            $user->setResetPasswordToken($resetPasswordToken);

            $manager->persist($user);
            $manager->flush();

            $event = new RegisterUserEvent($user);
            $dispatcher->dispatch($event, RegisterUserEvent::NAME);

            return $this->render('security/forgotPassword.html.twig', [
                'forgotPasswordForm' => $forgotPasswordForm->createView(),
                'message' => 'Le lien de réinitialisation de mot de passe a été envoyé !'
            ]);
        }
        

        return $this->render('security/forgotPassword.html.twig', [
            'forgotPasswordForm' => $forgotPasswordForm->createView()
        ]);
    }
}
