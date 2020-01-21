<?php

namespace App\DataFixtures;

use App\Entity\TrainingCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TrainingCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $trainingCategory = new TrainingCategory();
        $trainingCategory->setTitle('Informatique'); 
        $manager->persist($trainingCategory);

        $trainingCategory = new TrainingCategory();
        $trainingCategory->setTitle('Sanitaire'); 
        $manager->persist($trainingCategory);

        $trainingCategory = new TrainingCategory();
        $trainingCategory->setTitle('Education'); 
        $manager->persist($trainingCategory);

        $manager->flush();
    }
}
