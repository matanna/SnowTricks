<?php

// src/EventListener/LoginListener.php

namespace App\EventListener;

use App\Entity\User;
use Firebase\JWT\JWT;
use Doctrine\ORM\EntityManagerInterface;
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
    
    private $params;

    private $token;

    public function __construct(EntityManagerInterface $manager, ContainerBagInterface $params)
    {
        $this->manager = $manager;
        $this->params = $params;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        $payload = [
            "user" => $user->getUsername(),
            "exp"  => (new \DateTime())->modify("+5 minutes")->getTimestamp(),
        ];
        
        $token = JWT::encode($payload, $this->params->get('jwt_secret'), 'HS256');
        
        $user->setToken($token);
        $this->token = $token;
        
        $this->manager->persist($user);
        $this->manager->flush(); 

        $this->onAuthentificationSuccess();
    }
    
    public function onAuthentificationSuccess() 
    {
        $json = array('message' => 'success!', 'token' => sprintf('Bearer %s', $this->token));
        dump(json_encode($json));
        $response = new JsonResponse();
        $response->setData($json);

        /*$response->headers->set(
            'Authorization', sprintf('Bearer %s', $this->token)
        );
        $event->setResponse($response);*/
        
    }
}