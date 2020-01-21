<?php

namespace App\Command;

use League\Csv\Reader;
use App\Entity\Company;
use App\Entity\Trainee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CsvImportCommand extends Command
{
    protected static $defaultName = 'CsvImportCommand';

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;

    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
            ->setHelp('This command allows you to import a CSV file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting to import the feed') ;

        $reader = Reader::createFromPath('%kernel.root_dir%/../public/data.csv');
        
        // $keys = ['last_name','first_name','email','corporate_name','street','postal_code','city','siret','phone'];

        $results = $reader->fetchAssoc();


        foreach ($results as $row) {

            $trainee = (new Trainee())
                ->setLastName($row['last_name'])
                ->setFirstName($row['first_name'])
                ->setEmail($row['email'])          
            ;


            $this->em->persist($trainee);

            $company = (new Company())
                ->setCorporateName($row['corporate_name'])
                ->setStreet($row['street'])
                ->setPostalCode($row['postal_code'])
                ->setCity($row['city'])
                ->setSiretNumber($row['siret'])
                ->setPhoneNumber($row['phone'])
            ;

            $this->em->persist($company);

            $trainee->setCompany($company);

        }
        
        $this->em->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
