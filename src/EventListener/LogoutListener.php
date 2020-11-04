<?php
 
 
namespace App\EventListener;
 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
 
class LogoutListener
{
    private $_manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->_manager = $manager;
    }
    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        $response = $event->getResponse();
        $response->headers->clearCookie('Authorization');

        $user = $event->getToken()->getUser()->setToken(null);
        $this->_manager->persist($user);
        $this->_manager->flush();
        
        return $event->setResponse($response);
    }
}