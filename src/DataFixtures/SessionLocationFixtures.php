<?php

namespace App\DataFixtures;

use App\Entity\SessionLocation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SessionLocationFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $sessionLocation = new SessionLocation();
        $sessionLocation->setStreet('9 rue Chanoine Béranger');
        $sessionLocation->setPostalCode('50300');
        $sessionLocation->setCity('AVRANCHES');
        $manager->persist($sessionLocation);

        $sessionLocation = new SessionLocation();
        $sessionLocation->setStreet('17 rue de la Consitution');
        $sessionLocation->setPostalCode('50100');
        $sessionLocation->setCity('CHERBOURG');
        $manager->persist($sessionLocation);

        $manager->flush();
    }
}
