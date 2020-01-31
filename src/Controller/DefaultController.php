<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use Psr\Log\LoggerInterface;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
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
    public function index(Request $request)
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
            ]);
    }

    /**
     * @Route ("/test", name="default_test")
     */
    public function tallySheetExcel()
    {
        echo date('H:i:s'), ' Create new PhpWord object';
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $header = ['size' => 30, 'bold' => true];

        // 2. Advanced table

        $section->addTextBreak(1);
        $section->addText(htmlspecialchars("Feuille d'émargement"), $header, ['align' => 'center']);

        $nbTrainee = 8;

        $styleTable = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $styleFirstRow = ['borderBottomColor' => '0000FF', 'bgColor' => 'cccccc'];
        $styleCell = ['valign' => 'center'];
        
        $section->addText(htmlspecialchars("Formateur :"));
        $section->addText(htmlspecialchars("Intilulé de la formation :"));
        $section->addText(htmlspecialchars("Lieu de la formation :"));
        $section->addText(htmlspecialchars("Session :"));
        $section->addTextBreak();

        $phpWord->addTableStyle('Fancy Table', $styleTable, $styleFirstRow);
        $table = $section->addTable('Fancy Table');
        $table->addRow(90);
        $table->addCell(4000, $styleCell)->addText(htmlspecialchars('Nom et prénom'), ['bold' => true, 'size' => 12], ['align' => 'center']);
        $table->addCell(2000, $styleCell)->addText(htmlspecialchars('Date'), ['bold' => true, 'size' => 12], ['align' => 'center']);
        $table->addCell(6000, $styleCell)->addText(htmlspecialchars('Signature'), ['bold' => true, 'size' => 12], ['align' => 'center']);
        for ($i = 1; $i <= $nbTrainee; $i++) {
            $table->addRow();
            $table->addCell(2000)->addText(htmlspecialchars(" "));
            $table->addCell(2000)->addText(htmlspecialchars(" "));
            $table->addCell(2000)->addText(htmlspecialchars(" "));
        }
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        // Path of saved file
        $filePath = '../public/documents/emargement.docx';

        // Write file into path
        $objWriter->save($filePath);

        return new Response("File succesfully written at $filePath");
    }
}
