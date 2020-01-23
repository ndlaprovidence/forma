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

class CsvImportFormirisCommand extends Command
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
            ->setHelp('This command allows you to import a CSV file from Formiris')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting to import the feed') ;

        $reader = Reader::createFromPath('%kernel.root_dir%/../public/data_formiris.csv');

        $results = $reader->fetchAssoc();


        foreach ($results as $row) {

            $trainee = (new Trainee())
                ->setLastName($row["Nom de l'enseignant"])
                ->setFirstName($row["Nom de l'enseignant"])
                ->setEmail($row["Email de l'établissement"])          
            ;

            $this->em->persist($trainee);
            
            // $sql = '
            //     SELECT corporate_name FROM company c
            //     WHERE c.id = 12 ;';
            

            // $query = $qb->getQuery();

            // return $query->execute();



            $company = (new Company())
                ->setCorporateName($row['UP'])
                // ->setStreet($row[''])
                // ->setPostalCode($row[''])
                // ->setCity($row[''])
                // ->setSiretNumber($row[''])
                // ->setPhoneNumber($row[''])
            ;
                
            // if ($row['UP'] != $sql ) {
            //     $this->em->persist($company);
            // }
            
            // $output->writeln($qb);

            $this->em->persist($company);
            
            $trainee->setCompany($company);
        }
        
        $this->em->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}