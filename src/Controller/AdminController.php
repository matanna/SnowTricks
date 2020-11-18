<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Form\UserRoleType;
use App\Form\UsersCollectionRoleType;
use App\Repository\UserRepository;
use App\Repository\TricksRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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


        //We create form for roles of users
        $usersRolesForm = $this->createForm(UsersCollectionRoleType::class, [
            'rolesCollection' => $users
        ]);
        

        return $this->render('admin/admin.html.twig', [
            'categories' => $categories,
            'categoryForm' => $categoryForm->createView(),
            'users' => $users,
            'usersRolesForm' => $usersRolesForm->createView()
        ]);
    }
}
