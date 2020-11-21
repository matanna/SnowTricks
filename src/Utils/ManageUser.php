<?php 

namespace App\Utils;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ManageUser
{
    private $_manager;

    public function __construct(EntityManagerInterface $manager) 
    {
        $this->_manager = $manager;
    }
    
    /**
     * This method is use for change the user roles on the user list in admin page
     */
    public function manageUserRoles($user, $request)
    {
        $checkboxValue = $request->request->get('admin'.$user->getId());
        $validRole = $request->request->get('valid'.$user->getId());
        
        
        //For each user, we get value of role checkbox and button for valid checkbox
        if ($checkboxValue == "on" && isset($validRole)) {
           
            $user->setRoles(['ROLE_ADMIN']);
            dump($this->_manager);
            $this->_manager->persist($user);
            
            $this->_manager->flush();

            $response = new RedirectResponse("/admin/home");
            $response->send();
            
        } elseif (isset($validRole)) {

            $user->setRoles([]);
            dump($this->_manager);
            $this->_manager->persist($user);
            $this->_manager->flush();

            $response = new Response("/admin/home");
            $response->send();
        }
    }
}
