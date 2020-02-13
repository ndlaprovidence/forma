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
        $instructor->setProfession('Formateur'); 
        $manager->persist($instructor);

        $instructor = new Instructor();
        $instructor->setLastName('DUPONT'); 
        $instructor->setFirstName('Mathieu'); 
        $instructor->setProfession('Professeur'); 
        $manager->persist($instructor);

        $instructor = new Instructor();
        $instructor->setLastName('DORIAUX'); 
        $instructor->setFirstName('Florian'); 
        $instructor->setProfession('Formateur'); 
        $manager->persist($instructor);

        $manager->flush();
    }
}
