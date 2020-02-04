<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use Psr\Log\LoggerInterface;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Repository\CompanyRepository;
use App\Repository\SessionRepository;
use App\Repository\TraineeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @Route("/", name="default")
     */
    public function index(Request $request, SessionRepository $sr, TraineeRepository $tr, CompanyRepository $cr)
    {
        $this->logger->info('Une info.');

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $originalFile = $upload->getFileName();
            $fileName = time().'-'.rand(10000,99999).'.'.$originalFile->getClientOriginalExtension();
            $originalFile->move($this->getParameter('upload_directory'), $fileName);
            $upload->setFileName($fileName);
    
            return $this->redirectToRoute('session_new', [
                'file_name' => $fileName,
            ]);
        }
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'form' => $form->createView(),
            'sessions' => $sr->findAll(),
            'trainees' => $tr->findAll(),
            'companies' => $cr->findAll()
            ]);
    }

    /**
     * @Route ("/test", name="default_test")
     */
    public function tallySheetExcel()
    {
        $styleTable = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $styleFirstRow = ['borderBottomColor' => '0000FF', 'bgColor' => 'cccccc'];
        $timeDay = ['align' => 'center', 'bgColor' => 'cccccc'];
        $header = ['size' => 18, 'bold' => true];
        $textLeft = ['align' => 'left'];
        $styleTable = ['borderSize' => 6, 'borderColor' => '000000'];
        $cellRowSpan = ['vMerge' => 'restart', 'bgColor' => 'cccccc'];
        $cellRowContinue = ['vMerge' => 'continue'];
        $cellColSpan = ['gridSpan' => 2];
        $textCenter = ['align' => 'center'];
        $textRight = ['align' => 'right'];
        $verticalCenter = ['valign' => 'center'];
        $fontBold = ['bold' => true];
        $fontTitle = ['bold' => true, 'size' => 12];
        $fontTitle2 = ['bold' => true, 'size' => 8];
        $lilText = ['size' => 9];
        $landscape = ['orientation' => 'landscape'];

        echo date('H:i:s'), ' Create new PhpWord object';
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $nbSession = 3;
        $nbTrainee = 8;

        if ($nbSession == 1)
        {
            $section = $phpWord->addSection();
        }
        else
        {
            $section = $phpWord->addSection($landscape);
        }
            
        // Create footer
        $footer = $section->addFooter();
        
        // Footer content 
        $footer->addText("FC PRO service de formation professionnelle Continue de OGEC Notre Dame de la Providence <w:br/>9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 <w:br/>mail fcpro@ndlaprovidence.org <w:br/>N° activité 25500040250 référençable DataDocks", $lilText);
        
        // Header content
        $section->addImage("../public/resources/FC-PRO-logo.png", [
            'height' => 40,
            'width' => 80,
            'positioning' => 'absolute'
            ]);
        $section->addText("Feuille d'émargement", $header, $textRight);
        
        
        $section->addTextBreak();
        
        $section->addText(htmlspecialchars("CONNAÎTRE LES BONNES PRATIQUES EN HYGIENE ET SECURITE ALIMENTAIRE HACCP"), $fontBold, $textCenter);
        $section->addText(htmlspecialchars("Jeudi 2 janvier 2020 de 9h00 à 12h30 et de 13h30 à 17h00 à AVRANCHES"), $fontBold);
        $section->addText(htmlspecialchars("Vendredi 3 janvier 2020 de 9h00 à 12h30 et de 13h30 à 17h00 à AVRANCHES"), $fontBold);

        $textrun1 = $section->addTextRun();
        $textrun1->addText(htmlspecialchars("Merci de bien vouloir émarger lors de chaque demi-journée de formation."), $lilText);

        $phpWord->addTableStyle('Fancy Table', $styleTable, $styleFirstRow);
        $table = $section->addTable('Fancy Table');
        $table->addRow(90);
        $nomPrenom = $table->addCell(4000, $cellRowSpan);
        $textrun1 = $nomPrenom->addTextRun($textCenter);
        $textrun1->addText(htmlspecialchars('Nom et prénom du stagiaire'), $fontTitle, $textCenter);
        $etablissement = $table->addCell(4000, $cellRowSpan);
        $textrun1 = $etablissement->addTextRun($textCenter);
        $textrun1->addText(htmlspecialchars('Établissement'), $fontTitle, $textCenter);

        for ($i = 1; $i <= $nbSession; $i++) {
            $firstRowDate = $table->addCell(4000, $cellColSpan);
            $textrun2 = $firstRowDate->addTextRun($textCenter);
            switch ($i) {
                // Première session
                case 1:
                    $textrun2->addText(htmlspecialchars('Jeudi 2 janvier 2020'), $fontTitle2, $textCenter);
                    break;
                // Deuxième session
                case 2:
                    $textrun2->addText(htmlspecialchars('Jeudi 3 janvier 2020'), $fontTitle2, $textCenter);
                    break;
                // Troisème session
                case 3:
                    $textrun2->addText(htmlspecialchars('Lundi 6 janvier 2020'), $fontTitle2, $textCenter);
                    break;
            }
        }
        $table->addRow();
            
        for($i = 1; $i <= $nbSession; $i++)
        {
            if($i == 1)
            {
                $table->addCell(null, $cellRowContinue);
                $table->addCell(null, $cellRowContinue);
            }
            $table->addCell(2000)->addText(htmlspecialchars('De 9h00 à 12h30'), $fontTitle2, $timeDay);
            $table->addCell(2000)->addText(htmlspecialchars('De 13h30 à 17h00'), $fontTitle2, $timeDay);
        }
        

        for ($i = 1; $i <= $nbTrainee; $i++) {
            $table->addRow(750);
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars(" "));
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars(" "));

            for ($j = 1; $j <= $nbSession; $j++) {
                $table->addCell(2000)->addText(htmlspecialchars(" "));
                $table->addCell(2000)->addText(htmlspecialchars(" "));
            }
        }    

        $table->addRow(750);
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars("Formateur :"), $textCenter);
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars(" "));

        // $section->addTextBreak();
        $section->addText("Cachet et signature du prestataire de formation :");

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        // Path of saved file
        $filePath = '../public/documents/emargement.docx';

        // Write file into path
        $objWriter->save($filePath);

        return new Response("File succesfully written at $filePath");
    }
}
