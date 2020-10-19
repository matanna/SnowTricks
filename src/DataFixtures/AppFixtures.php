<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\User;
use App\Entity\Photo;
use App\Entity\Video;
use App\Entity\Tricks;
use App\Entity\Message;
use App\Entity\Category;
use Faker\Provider\Youtube;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        $faker->addProvider(new Faker\Provider\Youtube($faker));

        for ($i=1; $i<=4; $i++) {
            $category = new Category();
            $category -> setName($faker->word());

            $manager->persist($category);

            for ($k=1; $k<=rand(1, 3); $k++) {
                $user = new User();
                $user -> setUsername($faker->userName())
                  -> setPassword($faker->password())
                  -> setEmail($faker->email())
                  -> setFullName($faker->firstName() . ' ' . $faker->lastName())
                  -> setProfilPicture('http://placehold.it/100x100');
            
                $manager->persist($user);

                $paragraphs = $faker->paragraphs(2, false);
                $description = '<p>'.$paragraphs[0].'</p>';
                $description .= '<p>'.$paragraphs[1].'</p>';
    
                for ($j=1; $j<=rand(1, 5); $j++) {
                    $tricks = new Tricks();
                    $tricks -> setName($faker->sentence(3, true))
                            -> setDescription($description)
                            -> setCategory($category)
                            -> setUser($user);

                    $manager->persist($tricks);
                    
                    for ($l=1; $l<=rand(1, 4); $l++) {
                        $photo = new Photo();
                        $photo -> setName($faker->imageUrl(640, 480))
                               -> setTricks($tricks);

                        $manager->persist($photo);
                    }

                    for ($m=1; $m<=rand(1, 3); $m++) {
                        $video = new Video();
                        $video -> setLink($faker->youtubeEmbedCode())
                               -> setTricks($tricks);
                        
                        $manager->persist($video);
                    }

                    for ($n=1; $n<=rand(1, 10); $n++) {
                        $message = new Message();
                        $message -> setContent($faker->paragraph(rand(1, 3), true))
                                 -> setDateMessage($faker->dateTimeBetween('-3 months', 'now', 'Europe/Paris'))
                                 -> setTricks($tricks)
                                 -> setUser($user);
                        
                        $manager->persist($message);
                    }
                }
            } 
        }
        $manager->flush();
    }
}
