<?php

namespace App\Utils;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ManageCategory
{
    private $_manager;

    private $_categoryRepository;

    public function __construct(EntityManagerInterface $manager, 
        CategoryRepository $categoryRepository
    ) {
        $this->_manager = $manager;
        $this->_categoryRepository = $categoryRepository;
    }

    /**
     * This method is use for change name of category 
     * when the user click on the edit icon
     */
    public function changeNameCategory($request)
    {
        $newName = $request->request->get('category-name');
        $categoryId = $request->request->get('category-id');

        if ($newName != null && $categoryId != null) {
            
            $category = $this->_categoryRepository->find($categoryId);

            if ($category) {
                $category->setName($newName);
                
                $this->_manager->persist($category);
                $this->_manager->flush();
                
                $response = new RedirectResponse("/admin/home");
                $response->send();
            }
        } 
    }

    /**
     * This method is use for add a new category
     */
    public function addNewCategory($request, $categoryForm)
    {
        $categoryForm->handleRequest($request);
        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            
            $newCategoryName = $categoryForm->get('name')->getData();

            $newCategory = new Category();
            $newCategory->setName($newCategoryName);

            $this->_manager->persist($newCategory);
            $this->_manager->flush();
            
            $response = new RedirectResponse("/admin/home");
            $response->send();
        }  
    }
}
