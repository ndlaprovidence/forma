<?php

namespace App\Command;


use App\Entity\Company;
use App\Entity\Trainee;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;

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

        Cell::setValueBinder(new AdvancedValueBinder());
        
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting to import the feed') ;

        $inputFileType = 'Csv';
        $inputFileName = './public/data_formiris_1.csv';

        /**  Create a new Reader of the type defined in $inputFileType  **/
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        /**  Set the delimiter to a TAB character  **/
        $reader->setDelimiter(";");
        $spreadsheet = $reader->load($inputFileName);
      
        $loadedSheetNames = $spreadsheet->getSheetNames();
        
        /**  Load the file to a Spreadsheet Object  **/

        $i = 1;
        // $helper->log($spreadsheet->getSheetCount() . ' worksheet' . (($spreadsheet->getSheetCount() == 1) ? '' : 's') . ' loaded');
        foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
            // $helper->log('<b>Worksheet #' . $sheetIndex . ' -> ' . $loadedSheetName . ' (Formatted)</b>');
            $spreadsheet->setActiveSheetIndexByName($loadedSheetName);            
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            for ($i = 1; $i<= sizeof($sheetData)-1; $i++)
            {
                $trainee = (new Trainee())
                    ->setLastName($sheetData[$i][4])
                    ->setFirstName($sheetData[$i][4])
                    ->setEmail($sheetData[$i][7])          
                ;
                $this->em->persist($trainee);

                $company = (new Company())
                ->setCorporateName($sheetData[$i][6])
                ;

                if ($company->getCorporateName($sheetData[$i][6]))
                {
                    $output->writeln('erreur');
                }
                else
                {
                    $this->em->persist($company);
                    $trainee->setCompany($company);

                    $this->em->flush();
                }

                
                // $this->em->persist($company);
            
                
            }
            // if ($row['UP'] != $sql ) {
            //     $this->em->persist($company);
            // }

            

            
            
            // var_dump($sheetData);
        }
        // foreach ($spreadsheet as $row) {

        //     $trainee = (new Trainee())
        //         ->setLastName($row["Nom de l'enseignant"])
        //         ->setFirstName($row["Nom de l'enseignant"])
        //         ->setEmail($row["Email de l'établissement"])          
        //     ;

            
            // $this->em->persist($trainee);
            
            // $sql = '
            //     SELECT corporate_name FROM company c
            //     WHERE c.id = 12 ;';
            

            // $query = $qb->getQuery();

            // return $query->execute();



            // $company = (new Company())
            //     ->setCorporateName($row['UP'])
                
            // ;
                
            // if ($row['UP'] != $sql ) {
            //     $this->em->persist($company);
            // }
            
            // $output->writeln($qb);

            // $this->em->persist($company);
            
            // $trainee->setCompany($company);
        // }
        
        // $this->em->flush();

        // $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}