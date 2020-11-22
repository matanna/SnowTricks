<?php 

namespace App\EventListener;

use App\Event\RegisterUserEvent;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegisterSubscriber implements EventSubscriberInterface
{
    const NAME = 'user.registered';

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
            RegisterUserEvent::NAME => 'activationAccountMailSended'
        ];
    }

    public function activationAccountMailSended(RegisterUserEvent $event)
    {
        $activeAccountUrl = $this->_url.'/activation/'.$event->getUser()->getActivationToken(); 
    
        $email = (new TemplatedEmail())
            ->from($this->_sender)
            ->to($event->getUser()->getEmail())
            ->subject('Valider votre compte SnowTricks !!!')
            ->text('Suivez ce lien pour activer votre compte :')
            ->htmlTemplate('register/accountValidation.html.twig')
            ->context([
                'activeAccountUrl' => $activeAccountUrl
            ]);
            
        $this->_mailer->send($email);
    } 
}