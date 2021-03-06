<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Repository\UserRepository;
use App\Utils\ManageImageOnServer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
* @Route("/membre", name="member_")
*/
class ProfilController extends AbstractController
{
    /**
     * @Route("/profil/{id}", name="profil")
     */
    public function profil(UserRepository $userRepository, 
        Request $request, EntityManagerInterface $manager, 
        $id
    ) {
        $user = $userRepository->findOneBy(['id' => $id]);

        if (!$user) { 
            throw $this->createNotFoundException(); 
        }
        
        $profilForm = $this->createForm(ProfilType::class, $user);

        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {

            $manageImage = new ManageImageOnServer();
            
            
            $newProfilPicture = $profilForm->get('profilPicture')->getData();
            
            if ($newProfilPicture) {
                //We get and delete old profil picture
                $oldProfilPicture = $user->getProfilPicture();
                $manageImage->removeImageOnServer(
                    $oldProfilPicture, $this->getParameter('images_directory')
                );
                $nameProfilPicture = $manageImage->copyImageOnServer(
                    $newProfilPicture, $this->getParameter('images_directory')
                );

                $user->setProfilPicture($nameProfilPicture);
            } 
            
            $manager->persist($user);
            $manager->flush();
        }

        return $this->render('member/profil.html.twig', [
            'id' => $id, 'profilForm' => $profilForm->createView()
        ]);
    }

    /**
     * @Route("/Supprimer-photo-profil/{userId}", name="delete_profilPicture")
     */
    public function deletePhotoProfil(UserRepository $userRepository,
        EntityManagerInterface $manager, $userId
    ) {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException(); 
        }

        $manageImage = new ManageImageOnServer();
        //We get and delete old profil picture
        $oldProfilPicture = $user->getProfilPicture();
        $manageImage->removeImageOnServer(
            $oldProfilPicture, 
            $this->getParameter('images_directory')
        );

        $user->setProfilPicture(null);

        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('member_profil', [
            'id' => $user->getId()
        ]);
    }
}
