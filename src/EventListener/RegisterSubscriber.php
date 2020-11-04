<?php 

namespace App\EventListener;

use App\Event\RegisterUserEvent;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
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
            RegisterUserEvent::NAME => 'onRegisterUser'
        ];
    }

    public function onRegisterUser(RegisterUserEvent $event)
    {
        dump($this->_mailer);
        $activeAccountUrl = $this->_url.'?active='.$event->getUser()->getToken(); 
        $email = (new Email())
            ->from($this->_sender)
            ->to($event->getUser()->getEmail())
            ->subject('Valdier votre compte SnowTricks !!!')
            ->text('Suivez ce lien pour activer votre compte : ')
            ->html('<p>Cliquez sur ce lien pour <a href="'.$activeAccountUrl.'">activer votre compte</a>');
        
            $this->_mailer->send($email);
    }

    
}