<?php

namespace App\DataFixtures;

use App\Entity\Goal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class GoalFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $goal = new Goal();
        $goal->setTitle('Apprendre le fonctionnement des Framework'); 
        $manager->persist($goal);

        $goal = new Goal();
        $goal->setTitle('Réussir sa formation'); 
        $manager->persist($goal);

        $goal = new Goal();
        $goal->setTitle('Trouver rapidement des informations'); 
        $manager->persist($goal);

        $manager->flush();
    }
}
