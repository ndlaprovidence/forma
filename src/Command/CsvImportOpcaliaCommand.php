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

class CsvImportOpcaliaCommand extends Command
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
            ->setHelp('This command allows you to import a CSV file from Opcalia')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting to import the feed') ;

        $reader = Reader::createFromPath('%kernel.root_dir%/../public/data_opcalia.csv');

        $results = $reader->fetchAssoc();


        foreach ($results as $row) {

            $trainee = (new Trainee())
                ->setLastName($row['Prénom'])
                ->setFirstName($row['NOM'])
                ->setEmail($row['Email'])          
            ;


            $this->em->persist($trainee);

            $company = (new Company())
                ->setCorporateName($row['Raison sociale'])
                ->setStreet($row['Adresse 1'])
                ->setPostalCode($row['Code Postal'])
                ->setCity($row['Ville'])
                ->setSiretNumber($row['Siret'])
                ->setPhoneNumber($row['Téléphone'])
            ;

            $this->em->persist($company);

            $trainee->setCompany($company);

        }
        
        $this->em->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}