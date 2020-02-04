<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Entity\Company;
use App\Entity\Session;
use App\Entity\Trainee;
use App\Entity\Location;
use App\Entity\Training;
use App\Form\SessionType;
use PhpOffice\PhpWord\PhpWord;
use App\Entity\TrainingCategory;
use PhpOffice\PhpWord\IOFactory;
use App\Repository\UploadRepository;
use App\Repository\CompanyRepository;
use App\Repository\SessionRepository;
use App\Repository\TraineeRepository;
use App\Repository\LocationRepository;
use App\Repository\TrainingRepository;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TrainingCategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\DateTime;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Argument\ServiceLocator;

/**
 * @Route("/session")
 */
class SessionController extends AbstractController
{
    /**
     * @Route("/", name="session_index", methods={"GET"})
     */
    public function index(SessionRepository $sessionRepository): Response
    {
        return $this->render('session/index.html.twig', [
            'sessions' => $sessionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="session_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, CompanyRepository $cr, TraineeRepository $ter, TrainingRepository $tgr, TrainingCategoryRepository $tgcr, LocationRepository $lr, UploadRepository $ur): Response
    {
        if ( $request->query->has('file_name')) {
            $fileName = $request->query->get('file_name');
            $this->em = $em;

            // Créer un upload si il n'existe pas déjà
            $todayDate = new \DateTime('@'.strtotime('now'));
            $temp = $ur->findSameUpload($fileName);

            if ($temp)
            {
                $existingUpload = $temp;
                $upload = $ur->findOneById($existingUpload);
                $this->em->persist($upload);
            } else {
                $upload = new Upload();
                $upload
                    ->setFileName($fileName)
                    ->setDate($todayDate);
                    
                $this->em->persist($upload);
            }

            // START READING CSV
            Cell::setValueBinder(new AdvancedValueBinder());

            $inputFileType = 'Csv';
            $inputFileName = '../public/uploads/'.$fileName;

            /**  Create a new Reader of the type defined in $inputFileType  **/
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            /**  Set the delimiter to a TAB character  **/
            $reader->setDelimiter(";");
            $spreadsheet = $reader->load($inputFileName);
        
            $loadedSheetNames = $spreadsheet->getSheetNames();
            
            foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $spreadsheet->setActiveSheetIndexByName($loadedSheetName);            
                $sheetData = $spreadsheet->getActiveSheet()->toArray();

                $session = new Session();

                switch ($sheetData[0][0]) {

                    // OPCALIA CSV's
                    case 'Civilité':
                        $platformName = 'Opcalia';
                        for ($i = 1; $i< sizeof($sheetData); $i++)
                        {
                            $currentTrainee = $sheetData[$i];

                            // Créer la category de formation si elle n'existe pas déjà
                            $trainingCategoryTitle = $currentTrainee[14]; 

                            $temp = $tgcr->findSameTrainingCategory($trainingCategoryTitle);

                            if ($temp)
                            {
                                $existingTrainingCategory = $temp;
                                $trainingCategory = $tgcr->findOneById($existingTrainingCategory);
                                $this->em->persist($trainingCategory);
                            } else {
                                $trainingCategory = new TrainingCategory();
                                $trainingCategory
                                    ->setTitle($trainingCategoryTitle);
                                $this->em->persist($trainingCategory);
                            }


                            // Créer la formation si elle n'existe pas déjà
                            $trainingTitle = $currentTrainee[15]; 
                            $trainingReferenceNumber = 'Non-renseigné'; 

                            $temp = $tgr->findSameTraining($trainingTitle, $trainingReferenceNumber);

                            if ($temp)
                            {
                                $existingTraining = $temp;
                                $training = $tgr->findOneById($existingTraining);
                                $this->em->persist($training);
                            } else {
                                $training = new Training();
                                $training
                                    ->setTitle($trainingTitle)
                                    ->setPlatform('Opcalia')
                                    ->setReferenceNumber($trainingReferenceNumber)
                                    ->setTrainingCategory($trainingCategory);
                                $this->em->persist($training);
                            }


                            // Créer un trainee si il n'existe pas déjà
                            $lastName = strtoupper($currentTrainee[2]);
                            $firstName = strtolower($currentTrainee[1]); 
                            $firstName = ucfirst($firstName);
                            $email = strtolower($currentTrainee[5]); 

                            $temp = $ter->findSameTrainee($lastName,$firstName,$email);

                            if ($temp)
                            {
                                $existingTrainee = $temp;
                                $trainee = $ter->findOneById($existingTrainee);
                                $this->em->persist($trainee);
                            } else {
                                $trainee = new Trainee();
                                $trainee
                                    ->setLastName($lastName)
                                    ->setFirstName($firstName)
                                    ->setEmail($email);
                                $this->em->persist($trainee);
                            }
                            $session->addTrainee($trainee);


                            // Créer une company si il n'existe pas déjà
                            $corporateName = $currentTrainee[7];
                            $street = strtolower($currentTrainee[9]);
                            $city = strtoupper($currentTrainee[12]);
                            $postalCode = $currentTrainee[11];
                            $siretNumber = $currentTrainee[8];
                            $phoneNumber = $currentTrainee[4];

                            $temp = $cr->findSameCompany($corporateName,$city);

                            if ($temp)
                            {
                                $existingCompany = $temp;
                                $company = $cr->findOneById($existingCompany);
                                $this->em->persist($company);
                                $trainee->setCompany($company);
                                $this->em->persist($trainee);
                            } else {
                                $company = new Company();
                                $company
                                    ->setCorporateName($corporateName)
                                    ->setStreet($street)
                                    ->setPostalCode($postalCode)
                                    ->setCity($city)
                                    ->setSiretNumber($siretNumber)
                                    ->setPhoneNumber($phoneNumber);
                                $this->em->persist($company);
                                $trainee->setCompany($company);
                            }

                            // Créer une location si il n'existe pas déjà
                            $street = strtolower($currentTrainee[18]);
                            $postalCode = strtoupper($currentTrainee[20]);
                            $city = $currentTrainee[21];

                            $temp = $lr->findSameLocation($city,$postalCode,$street);

                            if ($temp)
                            {
                                $existingLocation = $temp;
                                $location = $lr->findOneById($existingLocation);
                                $this->em->persist($location);
                            } else {
                                $location = new Location();
                                $location
                                    ->setPostalCode($postalCode)
                                    ->setCity($city)
                                    ->setStreet($street);
                                $this->em->persist($location);
                            }

                            $this->em->flush();
                        }

                        $sessionsNbrTotal = 1;
                        $startDate = new \DateTime('@'.strtotime($sheetData[1][16]));
                        $session
                            ->setUpload($upload)
                            ->setTraining($training)
                            ->setLocation($location)
                            ->setStartDate($startDate);

                        break;

                    // FORMIRIS CSV's
                    case 'Prestation':
                        $platformName = 'Formiris';
                        for ($i = 1; $i< sizeof($sheetData); $i++)
                        {
                            $currentTrainee = $sheetData[$i];
    
                            // Créer la formation si elle n'existe pas déjà
                            $trainingTitle = $currentTrainee[0]; 
                            $trainingReferenceNumber = $currentTrainee[2];
    
                            $temp = $tgr->findSameTraining($trainingTitle,$trainingReferenceNumber);
    
                            if ($temp)
                            {
                                $existingTraining = $temp;
                                $training = $tgr->findOneById($existingTraining);
                                $this->em->persist($training);
                            } else {
                                $training = new Training();
                                $training
                                    ->setTitle($trainingTitle)
                                    ->setPlatform('Formiris')
                                    ->setReferenceNumber($trainingReferenceNumber);
                                $this->em->persist($training);
                            }
    
    
                            // Créer un trainee si il n'existe pas déjà
                            $names = explode(" ", $currentTrainee[4]);
                            $lastName = strtoupper($names[0]);
                            $firstName = strtolower($names[1]); 
                            $firstName = ucfirst($firstName);
                            $email = strtolower($currentTrainee[7]); 
    
                            $temp = $ter->findSameTrainee($lastName,$firstName,$email);
    
                            if ($temp)
                            {
                                $existingTrainee = $temp;
                                $trainee = $ter->findOneById($existingTrainee);
                                $this->em->persist($trainee);
                            } else {
                                $trainee = new Trainee();
                                $trainee
                                    ->setLastName($lastName)
                                    ->setFirstName($firstName)
                                    ->setEmail($email);
                                $this->em->persist($trainee);
                            }
                            $session->addTrainee($trainee);

                            
                            // Créer une company si elle n'existe pas déjà
                            $names = explode(" ", $currentTrainee[6]);
                            $count = count($names);
                            for ($j = 0; $j<=$count; $j++) {
                                $city = NULL;
                                // Si la chaine de caractère est en majuscule (c'est la ville)
                                if (ctype_upper ( $names[$j] ) == true) {
                                    $corporateName = $names[0];
                                    // On récupère toutes les précedentes infos avant la ville pour former le nom
                                    for ($k = 1; $k<$j; $k++) {
                                        $corporateName = $corporateName.' '.$names[$k];
                                    }

                                    $city = $names[$j];
                                    for ($j = 6; $j<$count-1; $j++) {
                                        $city = $city.' '.$currentTrainee[$j];
                                    }
                                    
                                    break;
                                }
                            }

                            $temp = $cr->findSameCompany($corporateName,$city);
    
                            if ($temp)
                            {
                                $existingCompany = $temp;
                                $company = $cr->findOneById($existingCompany);
                                $this->em->persist($company);
                                $trainee->setCompany($company);
                            } else {
                                $company = new Company();
                                $company
                                    ->setCorporateName($corporateName)
                                    ->setCity($city);
                                $this->em->persist($company);
                                $trainee->setCompany($company);
                            }


                            // Ajoute des dates de session et du lieu selon le nombre de sessions au total
                            $sessionsDates = explode(", ", $currentTrainee[16]);
                            $sessionsNbrTotal = count($sessionsDates);
    
                            if ( $request->query->has('current_session_number') ) {
                                $currentSessionNbr = $request->query->get('current_session_number');
                                $currentSessionNbr = intval($currentSessionNbr);
                                $currentSession = explode(" ", $sessionsDates[$currentSessionNbr]);
                            } else {
                                $currentSession = explode(" ", $sessionsDates[0]);
                            }
                            
                            $count = count($currentSession);
    
                            $startDate = new \DateTime('@'.strtotime($currentSession[1]));
                            $endDate = new \DateTime('@'.strtotime($currentSession[3]));
    
                            $this->em->flush();
                        }
                        
                        $session
                            ->setUpload($upload)
                            ->setTraining($training)
                            ->setStartDate($startDate)
                            ->setEndDate($endDate);
                        break;
                    
                    default:
                        # code...
                        break;
                };
            }
        }
        $this->em->persist($session);


        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($session);
            $this->em->flush();

            if ( $request->query->has('current_session_number') ) {
                $currentSessionNbr = $request->query->get('current_session_number');
                if ( $currentSessionNbr < $sessionsNbrTotal-1 ) {
                    $currentSessionNbr = $currentSessionNbr+1;
                    return $this->redirectToRoute('session_new', [
                        'file_name' => $fileName,
                        'current_session_number' => $currentSessionNbr
                    ]);
                }
                return $this->redirectToRoute('session_index');
            } else {
                if ( $sessionsNbrTotal != 1 ) {
                    $currentSessionNbr = 1;
                    return $this->redirectToRoute('session_new', [
                        'file_name' => $fileName,
                        'current_session_number' => $currentSessionNbr
                    ]);
                }
                return $this->redirectToRoute('session_index');
            }
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'file_name' => $fileName,
            'platform_name' => $platformName,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="session_show", methods={"GET"})
     */
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    /**
     * @Route("/{id}/word/{trainee}", name="session_word", methods={"GET"})
     */
    public function createWord(Session $session): Response
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

        return $this->render('session/show.html.twig', [
            'alert' => 'success'
        ]);
    }

    /**
     * @Route("/{id}/edit", name="session_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Session $session): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('session_index');
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="session_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Session $session): Response
    {
        if ($this->isCsrfTokenValid('delete'.$session->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($session);
            $entityManager->flush();
        }

        return $this->redirectToRoute('session_index');
    }


}
