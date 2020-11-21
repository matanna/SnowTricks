<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Category;
use App\Form\CategoryType;
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
        
        //We call this method for change name of category when the user click on edit icon
        $this->changeNameCategory($request);

        //We create form for add a new category
        $categoryForm = $this->addNewCategory($request);
        
        //We get all users in array
        $users = $userRepository->findAll();

        //For each user, we get value of role checkbox and button for valid checkbox
        $this->manageUserRoles($users, $request);

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

    /**
     * This method is use for change name of category 
     * when the user click on the edit icon
     */
    private function changeNameCategory($request)
    {
        $newName = $request->request->get('category-name');
        $categoryId = $request->request->get('category-id');

        if ($newName != null && $categoryId != null) {
            $categoryRepository = $this->getDoctrine()
                                       ->getRepository(Category::class);
            $category = $categoryRepository->find($categoryId);

            if ($category) {
                $category->setName($newName);
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($category);
                $manager->flush();
            }
        }
    }

    /**
     * This method is use for add a new category
     */
    private function addNewCategory($request)
    {
        $categoryForm = $this->createForm(CategoryType::class);

        $categoryForm->handleRequest($request);
        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $newCategoryName = $categoryForm->get('name')->getData();
            $newCategory = new Category();
            $newCategory->setName($newCategoryName);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($newCategory);
            $manager->flush();

            return $this->redirectToRoute('admin_admin-home');
        }
        return $categoryForm;
    }

    /**
     * This method is use for change the user roles on the user list in admin page
     */
    private function manageUserRoles($users, $request)
    {
        $manager = $this->getDoctrine()->getManager();

        foreach ($users as $user) {
            $checkboxValue = $request->request->get('admin'.$user->getId());
            $validRole = $request->request->get('valid'.$user->getId());

            if ($checkboxValue == "on" && isset($validRole)) {
                $user->setRoles(['ROLE_ADMIN']);
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('admin_admin-home');
            }

            if (isset($validRole)) {
                $user->setRoles([]);
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('admin_admin-home');
            }
        }
    }
}
