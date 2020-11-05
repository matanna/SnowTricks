<?php 

namespace App\EventListener;

use App\Event\RegisterUserEvent;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
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
        dump($this->_mailer);
        $activeAccountUrl = $this->_url.'/'.$event->getUser()->getActivationToken(); 
        dump($activeAccountUrl);
        $email = (new Email())
            ->from($this->_sender)
            ->to($event->getUser()->getEmail())
            ->subject('Valider votre compte SnowTricks !!!')
            ->text('Suivez ce lien pour activer votre compte : ')
            ->html($this->render('register/accountValidation.html.twig', [
                "activeAccountUrl" => $activeAccountUrl
            ]));
        
            $this->_mailer->send($email);
    }

    
}