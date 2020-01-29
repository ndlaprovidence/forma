<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use Psr\Log\LoggerInterface;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Alignment;
use Symfony\Component\HttpKernel\Kernel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
     * @Route("/test", name="test_default")
     */
    public function createWord()
    {
        // Create a new Word document
        $phpWord = new PhpWord();


        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);
        /* Note: any element you append to a document must reside inside of a Section. */

        // Adding an empty Section to the document...
        $page1 = $phpWord->addSection();
        $header = $page1->addHeader();
        $footer = $page1->addFooter();

        $header->addText('OGEC Notre Dame de la Providence
                          Service de Formation professionnelle continue
                          9 , rue  chanoine Bérenger BP 340	
                          50300 AVRANCHES');
        $footer->addText('FC PRO  service de formation professionnelle Continue de OGEC Notre Dame de la Providence 
                          9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 
                          mail fcpro@ndlaprovidence.org
                          N° activité 25500040250 référençable DataDocks');

        // Adding Text element to the Section having font styled by default...
        $page1->addText('CONVOCATION À UNE FORMATION', ['bold' => true ], ['align' => 'center']);
        
        $page1->addText('A l’attention de  Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B.');
        $page1->addText('Vous voudrez bien vous présenter à la session de la formation :');
        $page1->addText("Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants identifiée par le numéro PN060428  d’une durée de 6 heures (six heures) qui aura lieu mercredi 22 janvier 2020  de 9h00 à 12h00 et de 14h00 à 17h00 dans les locaux de Ecole Notre Dame,2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES.");
        $page1->addText('Objectifs de la formation :  (pour plus de détails, se rapporter au programme transmis précédemment)');
        $page1->addText('Se positionner en tant qu’acteur d’éducation au sein d’une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d’information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs ».');
        $page1->addText('Votre arrivée dans les locaux est souhaitée un quart d’heure avant le début de la session.');
        $page1->addText('Je vous souhaite une bonne formation.');
        $page1->addText('A Avranches, le 29/01/2020.');
        $page1->addImage('../public/resources/signature.png', [
            'height' => 100,
            'width' => 170
        ]);

        $page2 = $phpWord->addSection();
        // Adding Text element to the Section having font styled by default...
        $page2->addText('ATTESTATION DE FORMATION', ['bold' => true ], ['align' => 'center']);
        
        $page2->addText('Je soussigné, Philippe LECOUVREUR, responsable de FC PRO service de formation professionnelle continue du lycée Notre Dame de la Providence, atteste que :');
        $page2->addText('Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B a suivi  la prestation de formation décrite ci-dessous dans les locaux de Ecole Notre Dame , 2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES.');
        $page2->addText("Prestation de formation : Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants N° de prestation :  PN060428 en date de mercredi 22 janvier 2020 pendant une durée de 6 heures (six heures).");
        $page2->addText('Objectifs de la formation :');
        $page2->addText('Se positionner en tant qu’acteur d’éducation au sein d’une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d’information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs ».');
        $page2->addText('Fait pour servir et valoir ce que de droit.');
        $page2->addText('A Avranches, le 29/01/2020.');
        $page2->addImage('../public/resources/signature.png', [ 
            'height' => 100,
            'width' => 170
        ]);

        $page3 = $phpWord->addSection();
        // Adding Text element to the Section having font styled by default...
        $page3->addText('INSCRIPTION À UNE FORMATION', ['bold' => true ], ['align' => 'center']);
        
        $page3->addText('A l’attention de  Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B');
        $page3->addText('J’accuse réception de votre inscription à la formation :');
        $page3->addText("Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants  identifiée par le numéro PN060428  d’une durée de 6 heures (six heures) qui aura lieu mercredi 22 janvier 2020  de 9h00 à 12h00 et de 14h00 à 17h00 dans les locaux de Ecole Notre Dame, 2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES.");
        $page3->addText('Objectifs de la formation : (pour plus de détails, se rapporter au programme transmis précédemment)');
        $page3->addText('Se positionner en tant qu’acteur d’éducation au sein d’une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d’information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs ».');
        $page3->addText('Cette formation pourra être annulée si le nombre d’inscrits n’atteint pas un effectif minimum.');
        $page3->addText('Vous recevrez une convocation 10 jours avant le début de la session.');
        $page3->addText('A Avranches, le 29/01/2020');
        $page3->addImage('../public/resources/signature.png', [
            'height' => 100,
            'width' => 170
        ]);

        $page4 = $phpWord->addSection();
        // Adding Text element to the Section having font styled by default...
        $page4->addText('ACCUSÉ DE RÉCEPTION', ['bold' => true ], ['align' => 'center']);
        
        $page4->addText('Je soussigné, Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B, confirme avoir reçu une attestation pour la formation que j’ai suivie mercredi 22 janvier 2020 pendant une durée de 6 heures (six heures) dans les locaux de Ecole Notre Dame , 2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES.');
        $page4->addText("Prestation de formation : Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants, N° de prestation PN060428.");
        $page4->addText('Objectifs de la formation :');
        $page4->addText('Se positionner en tant qu’acteur d’éducation au sein d’une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d’information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs ».');
        $page4->addText('Fait pour servir et valoir ce que de droit.');
        $page4->addText('Je vous souhaite une bonne formation.');
        $page4->addText('A Avranches, le 29/01/2020');

  
        
        

        // Saving the document as OOXML file...
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        $filePath = '../public/test.docx';
        // Write file into path
        $objWriter->save($filePath);

        return new Response("File succesfully written at $filePath");
    }
}
