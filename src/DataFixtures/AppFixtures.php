<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Tricks;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i=1; $i<=4; $i++) {
            $category = new Category();
            $category -> setName($faker->word());
            
            $manager->persist($category);
        }

        for ($j=1; $j<=20; $j++) {
            $tricks = new Tricks();
            $tricks -> setName($faker->sentence(3, true))
                    -> setDescription($faker);
        }

        $manager->flush();
    }
}
