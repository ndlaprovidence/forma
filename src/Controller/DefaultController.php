<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use Psr\Log\LoggerInterface;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpKernel\Kernel;
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

        // Font name
        $phpWord->setDefaultFontName('Arial');
        // Font size
        $phpWord->setDefaultFontSize(11.5);
        // LineHeight
        $phpWord->setDefaultParagraphStyle(['lineHeight' => 1.4]);

        $section = $phpWord->addSection();
        // Header
        $header = $section->addHeader();
        // Footer
        $footer = $section->addFooter();

        // Content header
        $header->addText("OGEC Notre Dame de la Providence <w:br/> 
                          Service de Formation professionnelle continue <w:br/>
                          9 , rue  chanoine Bérenger BP 340 <w:br/>
                          50300 AVRANCHES <w:br/>", ['size' => 10], ['align' => 'right']);
        // Content footer 
        $footer->addText("FC PRO service de formation professionnelle Continue de OGEC Notre Dame de la Providence <w:br/>9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 <w:br/>mail fcpro@ndlaprovidence.org <w:br/>N° activité 25500040250 référençable DataDocks", ['size' => 10], ['align' => 'left']);                             


        // PAGE 1 --> Convocation
        $section->addText(htmlspecialchars("CONVOCATION À UNE FORMATION"), ['bold' => true, 'size' => 16 ], ['align' => 'center']);
        
        $page1 = $section->addTextRun();

        $page1->addTextBreak();
        $page1->addText(htmlspecialchars("A l'attention de"));
        $page1->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page1->addText(htmlspecialchars("Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B."), ['bold' => true]);
        $page1->addTextBreak();
        $page1->addTextBreak();
        $page1->addText(htmlspecialchars("Vous voudrez bien vous présenter à la session de la formation :"));
        $page1->addTextBreak();
        // Données issues de la BDD
        $page1->addText(htmlspecialchars("Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants"), ['bold' => true]);
        $page1->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page1->addText(htmlspecialchars("identifiée par le numéro PN060428 d'une durée de 6 heures (six heures) qui aura lieu"));
        $page1->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page1->addText(htmlspecialchars("mercredi 22 janvier 2020"), ['bold' => true]); 
        $page1->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page1->addText(htmlspecialchars("de 9h00 à 12h00 et de 14h00 à 17h00 dans les locaux de Ecole Notre Dame, 2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES."));
        $page1->addTextBreak();
        $page1->addTextBreak();
        $page1->addText(htmlspecialchars("Objectifs de la formation :"), ['bold' => true]);
        $page1->addText(htmlspecialchars(" ")); 
        $page1->addText(htmlspecialchars("(pour plus de détails, se rapporter au programme transmis précédemment) :"));
        $page1->addTextBreak();
        // Données issues de la BDD
        $page1->addText(htmlspecialchars("Se positionner en tant qu'acteur d'éducation au sein d'une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d'information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs »."));
        $page1->addTextBreak();
        $page1->addTextBreak();
        $page1->addText(htmlspecialchars("Votre arrivée dans les locaux est souhaitée un quart d'heure avant le début de la session."));
        $page1->addTextBreak();
        $page1->addTextBreak();
        $page1->addText(htmlspecialchars("Je vous souhaite une bonne formation."));
        $page1->addTextBreak();
        $page1->addTextBreak();
        // Afficher la date du jour
        $page1->addText(htmlspecialchars("À Avranches, le 29/01/2020."));
        $page1->addTextBreak();
        $page1->addImage("../public/resources/signature.png", [
            'height' => 100,
            'width' => 170
        ]);


        // PAGE 2 --> Attestation
        $section = $phpWord->addSection();

        $section->addText(htmlspecialchars("ATTESTATION DE FORMATION"), ['bold' => true, 'size' => 16 ], ['align' => 'center']);
        
        $page2 = $section->addTextRun();

        $page2->addTextBreak();
        $page2->addText(htmlspecialchars("Je soussigné, Philippe LECOUVREUR, responsable de FC PRO service de formation professionnelle continue du lycée Notre Dame de la Providence, atteste que :"));
        $page2->addText(htmlspecialchars(" ")); 
        // Données issues de la BDD
        $page2->addText(htmlspecialchars("Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B"), ['bold' => true]);
        $page2->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page2->addText(htmlspecialchars("a suivi  la prestation de formation décrite ci-dessous dans les locaux de Ecole Notre Dame , 2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES."));
        $page2->addTextBreak();
        $page2->addTextBreak();
        $page2->addText(htmlspecialchars("Prestation de formation :"));
        $page2->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page2->addText(htmlspecialchars("Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants"), ['bold' => true]);
        $page2->addText(htmlspecialchars(" "));
        $page2->addText(htmlspecialchars("N° de prestation :"));
        $page2->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page2->addText(htmlspecialchars("PN060428 en date de mercredi 22 janvier 2020"), ['bold' => true]);
        $page2->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page2->addText(htmlspecialchars("pendant une durée de 6 heures (six heures)."));
        $page2->addTextBreak();
        $page2->addTextBreak();
        $page2->addText(htmlspecialchars("Objectifs de la formation :"), ['bold' => true]);
        $page2->addTextBreak();
        // Données issues de la BDD
        $page2->addText(htmlspecialchars("Se positionner en tant qu'acteur d'éducation au sein d'une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d'information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs »."));
        $page2->addTextBreak();
        $page2->addTextBreak();
        $page2->addText(htmlspecialchars("Votre arrivée dans les locaux est souhaitée un quart d'heure avant le début de la session."));
        $page2->addTextBreak();
        $page2->addTextBreak();
        $page2->addText(htmlspecialchars("Fait pour servir et valoir ce que de droit"));
        $page2->addTextBreak();
        $page2->addTextBreak();
        // Afficher la date du jour
        $page2->addText(htmlspecialchars("À Avranches, le 29/01/2020."));
        $page2->addTextBreak();
        $page2->addImage("../public/resources/signature.png", [
            'height' => 100,
            'width' => 170
        ]);


        // PAGE 3 --> Inscription 
        $section = $phpWord->addSection();

        $section->addText(htmlspecialchars("INSCRIPTION À UNE FORMATION"), ['bold' => true, 'size' => 16 ], ['align' => 'center']);
        
        $page3 = $section->addTextRun();

        $page3->addTextBreak();
        $page3->addText(htmlspecialchars("À l'attention de"));
        $page3->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page3->addText(htmlspecialchars("Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B."), ['bold' => true]);
        $page3->addTextBreak();
        $page3->addText(htmlspecialchars("J'accuse réception de votre inscription à la formation :"));
        $page3->addTextBreak();
        // Données issues de la BDD
        $page3->addText(htmlspecialchars("Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants"), ['bold' => true]);
        $page3->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page3->addText(htmlspecialchars("identifiée par le numéro PN060428  d'une durée de 6 heures (six heures) qui aura lieu"));
        $page3->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page3->addText(htmlspecialchars("mercredi 22 janvier 2020"), ['bold' => true]);
        $page3->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page3->addText(htmlspecialchars("de 9h00 à 12h00 et de 14h00 à 17h00 dans les locaux de Ecole Notre Dame , 2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES."));
        $page3->addTextBreak();
        $page3->addTextBreak();
        $page3->addText(htmlspecialchars("Objectifs de la formation :"), ['bold' => true]);
        $page3->addText(htmlspecialchars(" ")); 
        $page3->addText(htmlspecialchars("(pour plus de détails, se rapporter au programme transmis précédemment)"));
        $page3->addTextBreak();
        // Données issues de la BDD
        $page3->addText(htmlspecialchars("Se positionner en tant qu'acteur d'éducation au sein d'une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d'information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs »."));
        $page3->addTextBreak();
        $page3->addTextBreak();
        $page3->addText(htmlspecialchars("Cette formation pourra être annulée si le nombre d'inscrits n'atteint pas un effectif minimum."));
        $page3->addTextBreak();
        $page3->addTextBreak();
        $page3->addText(htmlspecialchars("Vous recevrez une convocation 10 jours avant le début de la session."));
        $page3->addTextBreak();
        $page3->addTextBreak();
        // Afficher la date du jour
        $page3->addText(htmlspecialchars("À Avranches, le 29/01/2020."));
        $page3->addTextBreak();
        $page3->addImage("../public/resources/signature.png", [
            'height' => 100,
            'width' => 170
        ]);


        //PAGE 4 --> Accusé de réception
        $section = $phpWord->addSection();

        $section->addText(htmlspecialchars('ACCUSÉ DE RÉCEPTION'), ['bold' => true, 'size' => 16 ], ['align' => 'center']);

        $page4 = $section->addTextRun();

        $page4->addTextBreak(); 
        $page4->addText(htmlspecialchars("Je soussigné,"));
        $page4->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page4->addText(htmlspecialchars("Madame Bouvet Orlanne, Notre Dame VILLEDIEU LES POELES ROUFF 0501401B"), ['bold' => true]);
        $page4->addText(htmlspecialchars(" "));
        $page4->addText(htmlspecialchars("confirme avoir reçu une attestation pour la  formation que j'ai suivie"));
        $page4->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page4->addText(htmlspecialchars("mercredi 22 janvier 2020"), ['bold' => true]);
        $page4->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page4->addText(htmlspecialchars("pendant une durée de 6 heures (six heures) dans les locaux de Ecole Notre Dame, 2 et 26 rue Pierre PARIS 50800 VILLEDIEU LES POELES"));
        $page4->addTextBreak();
        $page4->addTextBreak();
        $page4->addText(htmlspecialchars("Prestation de la formation :"));
        $page4->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page4->addText(htmlspecialchars("Les stratégies et supports pédagogiques adaptés aux attentes et fonctionnements mentaux des nouvelles générations d'enfants"), ['bold' => true]);
        $page4->addText(htmlspecialchars(" "));
        // Données issues de la BDD
        $page4->addText(htmlspecialchars("PN060428."));
        $page4->addTextBreak();
        $page4->addTextBreak();
        $page4->addText(htmlspecialchars("Objectifs de la formation :"), ['bold' => true]);
        $page4->addTextBreak();
        // Données issues de la BDD
        $page4->addText(htmlspecialchars("Se positionner en tant qu'acteur d'éducation au sein d'une société actuelle, connaître les liens établis en neuropsychologie entre motivation, stimulation et développement cognitif chez le jeune enfant, , se documenter et expérimenter les approches pédagogiques promues au sein du réseau d'information pour la « réussite éducative » notamment à destination des élèves dits « décrocheurs »."));
        $page4->addTextBreak();
        $page4->addTextBreak();
        $page4->addText(htmlspecialchars("Fait pour servir et valoir ce que de droit"));
        $page4->addTextBreak();
        $page4->addTextBreak();
        // Afficher la date du jour
        $page4->addText(htmlspecialchars("À Avranches, le 29/01/2020."));        

        // Saving the document as OOXML file...
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        // Path of saved file
        $filePath = '../public/test.docx';

        // Write file into path
        $objWriter->save($filePath);

        return new Response("File succesfully written at $filePath");
    }

}
