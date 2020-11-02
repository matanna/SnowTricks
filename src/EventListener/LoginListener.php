<?php

// src/EventListener/LoginListener.php

namespace App\EventListener;

use App\Entity\User;
use DateTime;
use Firebase\JWT\JWT;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class LoginListener
{
    private $manager;

    private $token;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        $token = md5(uniqid());
        
        $user->setToken($token);
        $this->token = $token;
        
        $this->manager->persist($user);
        $this->manager->flush(); 

        $this->sendToken();
    }
    
    public function sendToken() 
    {
        $response = new RedirectResponse("/membre/ajout");

        $cookie = Cookie::create('Authorization')
                    ->withValue($this->token)
                    ->withExpires(new \DateTime('+1 hour'))
                    ->withSecure(true)
                    ->withHttpOnly(true);

        $response->headers->setCookie($cookie);

        $response->send();
        
    }
}