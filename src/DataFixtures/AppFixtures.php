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

                $paragraphs = $faker->paragraphs(4, false);
                $description = '<p>'.$paragraphs[0].'</p>';

                for ($y=1; $y<=3; $y++) {
                    $description .= '<p>'.$paragraphs[$y].'</p>';
                }
    
                for ($j=1; $j<=rand(4, 10); $j++) {
                    $tricks = new Tricks();

                    $dateTricks = $faker->dateTimeBetween('-3 months', 'now', 'Europe/Paris');

                    $tricks -> setName($faker->sentence(3, true))
                            -> setDescription($description)
                            -> setCategory($category)
                            -> setUser($user)
                            -> setDateAtCreated($dateTricks);

                    $manager->persist($tricks);
                    
                    for ($l=1; $l<=rand(2, 6); $l++) {
                        $photo = new Photo();
                        $photo -> setName("https://loremflickr.com/640/360")
                               -> setTricks($tricks);

                        $manager->persist($photo);
                    }

                    for ($m=1; $m<=rand(2, 6); $m++) {
                        $video = new Video();
                        $video -> setLink($faker->youtubeEmbedUri())
                               -> setTricks($tricks);
                        
                        $manager->persist($video);
                    }

                    for ($n=1; $n<=rand(15, 35); $n++) {
                        $message = new Message();
                        $message -> setContent($faker->paragraph(rand(1, 3), true))
                                 -> setDateMessage($faker->dateTimeBetween($dateTricks, 'now', 'Europe/Paris'))
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
