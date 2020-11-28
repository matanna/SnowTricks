<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Category;
use App\Utils\ManageUser;
use App\Form\CategoryType;
use App\Utils\ManageCategory;
use App\Repository\UserRepository;
use App\Repository\TricksRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/home", name="admin-home")
     */
    public function adminHome(EntityManagerInterface $manager,
        TricksRepository $tricksRepository, UserRepository $userRepository, 
        Request $request
    ) {
        //We get all category in array
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        //For each category, we get the tricks in array
        foreach ($categories as $category) {
            $tricks = $tricksRepository->findTricksByCategory($category->getId());

            //For each trick, we add it in collection of category
            foreach ($tricks as $trick) {
                $category->addTrick($trick);
            }
        }

        $manager = $this->getDoctrine()->getManager();
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        $manageCategory = new ManageCategory($manager, $categoryRepository);

        //We call this method for change name of category when the user click on edit icon
        $manageCategory->changeNameCategory($request);

        //We create form for add a new category
        $categoryForm = $this->createForm(CategoryType::class);
        $manageCategory->addNewCategory($request, $categoryForm);
        
        //We get all users in array
        $users = $userRepository->findAll();

        //For each user, we call this method for manage roles
        foreach ($users as $user) {
            $manageUser = new ManageUser($manager);
            $manageUser->manageUserRoles($user, $request);
        } 

        return $this->render('admin/admin.html.twig', [
            'categories' => $categories,
            'categoryForm' => $categoryForm->createView(),
            'users' => $users,   
        ]);
    }

    /**
     * @Route("/delete/user/{id}", name="delete_user")
     */
    public function deleteUser(EntityManagerInterface $manager, 
        TricksRepository $tricksRepository, NotifierInterface $notifier, $id
    ) {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->find($id);

        if (!$user) {
            throw new \Exception('Cet utilisateur n\'existe pas !');
        }
        $tricks = $tricksRepository->findBy(['user' => $user]);
        if ($tricks) {
            $notifier->send(new Notification("Vous ne pouvez pas supprimer " . $user->getUsername() ." . Des tricks sont lui sont liés !", ['browser']));
            return $this->redirectToRoute('admin_admin-home', ['_fragment' => 'table-category']);
        }
        
        //We remove user messages
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $messages = $messageRepository->findBy(['user' => $user]);
        if ($messages) {
            foreach ($messages as $message) {
                $manager->remove($message);
            }
        }

        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute('admin_admin-home');
    }

    /**
     * @Route("/delete/category/{id}", name="delete_category")
     */
    public function deleteCategory(TricksRepository $tricksRepository, 
        EntityManagerInterface $manager,NotifierInterface $notifier, $id
    ) {
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw new \Exception('Cette categorie n\'existe pas !');
        }
        $tricks = $tricksRepository->findBy(['category' => $category]);
        if ($tricks) {
            $notifier->send(new Notification("Vous ne pouvez pas supprimer la categorie : " . $category->getName() ." . Des tricks sont lui sont liés !", ['browser']));
            return $this->redirectToRoute('admin_admin-home', ['_fragment' => 'table-category']);
        }

        $manager->remove($category);
        $manager->flush();

        return $this->redirectToRoute('admin_admin-home');
    }
}
