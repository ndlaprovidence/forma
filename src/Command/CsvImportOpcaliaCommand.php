<?php

namespace App\Command;


use League\Csv\Reader;
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
        Cell::setValueBinder(new AdvancedValueBinder());
        
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting to import the feed') ;

    
        $inputFileType = 'Csv';
        $inputFileName = './public/data_opcalia_1.csv';

        /**  Create a new Reader of the type defined in $inputFileType  **/
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        /**  Set the delimiter to a TAB character  **/
        $reader->setDelimiter(";");
        $spreadsheet = $reader->load($inputFileName);
      
        $loadedSheetNames = $spreadsheet->getSheetNames();
        
        /**  Load the file to a Spreadsheet Object  **/

        $output->writeln($loadedSheetNames);
        
        $i = 1;
        // $helper->log($spreadsheet->getSheetCount() . ' worksheet' . (($spreadsheet->getSheetCount() == 1) ? '' : 's') . ' loaded');

        foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
            // $helper->log('<b>Worksheet #' . $sheetIndex . ' -> ' . $loadedSheetName . ' (Formatted)</b>');
            $spreadsheet->setActiveSheetIndexByName($loadedSheetName);            
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            for ($i = 1; $i<= sizeof($sheetData)-1; $i++)
            {
                $trainee = (new Trainee())
                    ->setLastName($sheetData[$i][2])
                    ->setFirstName($sheetData[$i][1])
                    ->setEmail($sheetData[$i][5])          
                ;

                $this->em->persist($trainee);

                $company = (new Company())
                    ->setCorporateName($sheetData[$i][7])
                    ->setStreet($sheetData[$i][9])
                    ->setPostalCode($sheetData[$i][11])
                    ->setCity($sheetData[$i][12])
                    ->setSiretNumber($sheetData[$i][8])
                    ->setPhoneNumber($sheetData[$i][4])
                
                ;

                $this->em->persist($company);
            
                $trainee->setCompany($company);

            }

            

            $this->em->flush();
            
            // var_dump($sheetData);
        }
    
    }
    
}