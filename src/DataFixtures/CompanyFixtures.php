<?php

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $company = new Company();
        $company->setCorporateName('Notre Dame de la Providence'); 
        $company->setStreet('9 rue Chanoine Béranger');
        $company->setPostalCode('50300');
        $company->setCity('AVRANCHES');
        $company->setSiretNumber('00000');
        $company->setPhoneNumber('0655889966');
        $manager->persist($company);

        $company = new Company();
        $company->setCorporateName('Lycée Jean-Francois Millet'); 
        $company->setStreet('17 rue de la Consitution');
        $company->setPostalCode('50100');
        $company->setCity('CHERBOURG');
        $company->setSiretNumber('00000');
        $company->setPhoneNumber('0611223344');
        $manager->persist($company);

        $manager->flush();
    }
}
