<?php

// src/EventListener/LoginListener.php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
        $request = $event->getRequest();

        if ($request->getMethod() == "POST" 
            && $request->attributes->get('_route') == 'login'
            && $user 
        ) {
            $token = md5(uniqid());

            $user->setToken($token);

            $this->manager->persist($user);
            $this->manager->flush(); 

            $cookie = Cookie::create('Authorization')
                    ->withValue($token)
                    ->withExpires(new \DateTime('+1 year'))
                    ->withSecure(false)
                    ->withHttpOnly(true);

            $response = $event->getResponse();
            $response->headers->setCookie($cookie);
            $event->setResponse($response);
        }

        
    }

    /*public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $token = md5(uniqid());

        $user->setToken($token);

        $this->manager->persist($user);
        $this->manager->flush(); 

        $cookie = Cookie::create('Authorization')
                    ->withValue($token)
                    ->withExpires(new \DateTime('+1 year'))
                    ->withSecure(false)
                    ->withHttpOnly(true);

        $response = $event->getResponse();
        $response->headers->setCookie($cookie);
        $event->setResponse($response);
    }*/

}