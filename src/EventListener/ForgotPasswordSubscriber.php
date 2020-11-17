<?php 

namespace App\EventListener;

use App\Event\RegisterUserEvent;
use Symfony\Component\Mime\Email;
use App\Event\ForgotPasswordEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ForgotPasswordSubscriber implements EventSubscriberInterface
{
    const NAME = 'user.forgot-password';

    private $_mailer;
    private $_sender;
    private $_url;

    public function __construct(MailerInterface $mailer, $sender, $url)
    {
        $this->_mailer = $mailer;
        $this->_sender = $sender;
        $this->_url = $url;
    }

    public static function getSubscribedEvents()
    {
        return [
            ForgotPasswordEvent::NAME => 'resetPasswordMailSended',
        ];
    }

    public function resetPasswordMailSended(ForgotPasswordEvent $event)
    {
        $resetPasswordUrl = $this->_url.'/reinitialiser-password/'
                            .$event->getUser()->getResetPasswordToken();

        $email = (new TemplatedEmail())
            ->from($this->_sender)
            ->to($event->getUser()->getEmail())
            ->subject('Réinitialiser votre mot de passe sur Snowtricks !!!')
            ->text('Suivez ce lien pour réinitialiser votre mot de passe :')
            ->htmlTemplate('security/mailForgotPassword.html.twig')
            ->context([
                'resetPasswordUrl' => $resetPasswordUrl,
                'username' => $event->getUser()->getUsername()
            ]);
            
        $this->_mailer->send($email);
    }

    
}