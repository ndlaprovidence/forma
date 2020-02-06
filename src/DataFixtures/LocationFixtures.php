<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $location = new Location();
        $location->setName('Notre Dame de la Providence');
        $location->setStreet('9 rue Chanoine Béranger');
        $location->setPostalCode('50300');
        $location->setCity('AVRANCHES');
        $manager->persist($location);

        $location = new Location();
        $location->setName('Lycée Jean-François Millet');
        $location->setStreet('17 rue de la Consitution');
        $location->setPostalCode('50100');
        $location->setCity('CHERBOURG');
        $manager->persist($location);

        $manager->flush();
    }
}
