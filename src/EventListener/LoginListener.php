<?php

// src/EventListener/LoginListener.php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class LoginListener
{
    private $manager;

    private $security;

    public function __construct(EntityManagerInterface $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;    
    }

    public function onKernelResponse(ResponseEvent $event) 
    {
        $user = $this->security->getUser();
        $token = md5(uniqid());
        $request = $event->getRequest();

        if ($user && $request->getMethod() == "POST" && $request->getRequestUri() == "/connexion") {
            
            $user->setToken($token);
            $this->manager->persist($user);
            $this->manager->flush(); 

            $cookie = Cookie::create('Authorization')
                    ->withValue($token)
                    ->withExpires(new \DateTime('+1 hour'))
                    ->withSecure(false)
                    ->withHttpOnly(true);

            $response = $event->getResponse();
            $response->headers->setCookie($cookie);
            $event->setResponse($response);
        }
        
    }
}