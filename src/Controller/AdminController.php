<?php

namespace App\Controller;

use App\Entity\User;
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
        CategoryRepository $categoryRepository, TricksRepository $tricksRepository,
        UserRepository $userRepository, Request $request
    ) {
        //We get all category in array
        $categories = $categoryRepository->findAll();

        //For each category, we get the tricks in array
        foreach ($categories as $category) {
            $tricks = $tricksRepository->findTricksByCategory($category->getId());

            //For each trick, we add it in collection of category
            foreach ($tricks as $trick) {
                $category->addTrick($trick);
            }
        }
        
        //We get new name of category when the user want to change this with edit icon
        $newName = $request->request->get('category-name');
        $categoryId = $request->request->get('category-id');

        if ($newName != null && $categoryId != null) {
            $category = $categoryRepository->find($categoryId);

            if ($category) {
                $category->setName($newName);
                $manager->persist($category);
                $manager->flush();
            }
        }

        //We create form for add a new category
        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);
        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $newCategoryName = $categoryForm->get('name')->getData();
            $newCategory = new Category();
            $newCategory->setName($newCategoryName);
            $manager->persist($newCategory);
            $manager->flush();

            return $this->redirectToRoute('admin_admin-home');
        }
        
        //We get all users in array
        $users = $userRepository->findAll();

        //For each user, we get value of role checkbox and button for valid checkbox
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
