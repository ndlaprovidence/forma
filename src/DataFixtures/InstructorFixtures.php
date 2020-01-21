<?php

namespace App\DataFixtures;

use App\Entity\Instructor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class InstructorFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $instructor = new Instructor();
        $instructor->setLastName('MAUX'); 
        $instructor->setFirstName('Daniel'); 
        $manager->persist($instructor);

        $instructor = new Instructor();
        $instructor->setLastName('DUPONT'); 
        $instructor->setFirstName('Mathieu'); 
        $manager->persist($instructor);

        $instructor = new Instructor();
        $instructor->setLastName('DORIAUX'); 
        $instructor->setFirstName('Florian'); 
        $manager->persist($instructor);

        $manager->flush();
    }
}
