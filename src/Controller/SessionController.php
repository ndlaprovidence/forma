<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Entity\Company;
use App\Entity\Session;
use App\Entity\Trainee;
use App\Entity\Location;
use App\Entity\Training;
use App\Form\SessionType;
use App\Entity\TrainingCategory;
use PhpOffice\PhpWord;
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
use Symfony\Component\Validator\Constraints\DateTime;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
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
        if ( $request->query->has('file_name') ) {
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
    public function show(Session $session, Request $request,UploadRepository $ur, SessionRepository $sr, EntityManagerInterface $em, TraineeRepository $tr): Response
    {       
        $sessionStartDate = $session->getStartDate()->format('Y-m-d');
                            setlocale(LC_TIME, "fr_FR");
                            $sessionStartDate = strftime("%A %d %B %G", strtotime($sessionStartDate));
        var_dump($sessionStartDate);

        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    /**
     * @Route("/{id}/emargement", name="session_emargement", methods={"GET"})
     */
    public function emargement(Session $session, UploadRepository $ur, SessionRepository $sr, EntityManagerInterface $em, TraineeRepository $tr)
    {
        $this->em = $em;
        $idSession = $session->getId(intval($idSession));
        
        $upload = $ur->findOneById($session->getUpload()->getId());
        $this->em->persist($upload);

        $sessionsCollection = $upload->getSessions();
        $nbSessions = 0;

        foreach ($sessionsCollection as $session) {
            $nbSessions++;
        }

        $sessionDate = $session->getStartDate()->format('d-m-Y');
                            // setlocale(LC_TIME, "fr_FR");
                            // $sessionStartDate = strftime("%A %d %B %G", strtotime($sessionStartDate));
        $titleTraining = $session->getTraining()->getTitle();
        $sessionLocation = $session->getLocation()->getCity();

        $styleTable = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $styleFirstRow = ['borderBottomColor' => '0000FF', 'bgColor' => 'cccccc'];
        $timeDay = ['align' => 'center', 'bgColor' => 'cccccc'];
        $styleHeader = ['size' => 18, 'bold' => true];
        $nameTraining = ['size' => 15, 'bold' => true];
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

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        if ($nbSessions == 1)
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
        $section->addText("Feuille d'émargement", $styleHeader, $textRight);
        
        
        $section->addTextBreak();
        $section->addText(htmlspecialchars($titleTraining), $nameTraining, $textCenter);
        $section->addText(htmlspecialchars($sessionDate ." de 9h00 à 12h30 et de 13h30 à 17h00 à " . $sessionLocation ), $fontBold);

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

        for ($i = 1; $i <= $nbSessions; $i++) {
            $firstRowDate = $table->addCell(4000, $cellColSpan);
            $textrun2 = $firstRowDate->addTextRun($textCenter);
            switch ($i) {
                // Première session
                case 1:
                    $textrun2->addText(htmlspecialchars($sessionStartDate), $fontTitle2, $textCenter);
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
            
        for($i = 1; $i <= $nbSessions; $i++)
        {
            if($i == 1)
            {
                $table->addCell(null, $cellRowContinue);
                $table->addCell(null, $cellRowContinue);
            }
            $table->addCell(2000)->addText(htmlspecialchars('De 9h00 à 12h30'), $fontTitle2, $timeDay);
            $table->addCell(2000)->addText(htmlspecialchars('De 13h30 à 17h00'), $fontTitle2, $timeDay);
        }
        
        $traineesCollection = $session->getTrainees();
        foreach ($traineesCollection as $trainee) {
            $lastNameTrainee = $trainee->getLastName();
            $firstNameTrainee = $trainee->getFirstName();
            $companyTrainee = $trainee->getCompany();
            $table->addRow(750);
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars($lastNameTrainee . " " . $firstNameTrainee));
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars($companyTrainee));

            for ($j = 1; $j <= $nbSessions; $j++) {
                $table->addCell(2000)->addText(htmlspecialchars(" "));
                $table->addCell(2000)->addText(htmlspecialchars(" "));
            }
        }    

        $instructorCollection = $session->getInstructors();
        foreach ($instructorCollection as $instructor) {
            $firstNameInstructor = $instructor->getFirstName();
            $lastNameInstructor = $instructor->getLastName();
        
            $table->addRow(750);
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars("Formateur : " . $firstNameInstructor . " " . $lastNameInstructor), $textCenter);
        }
        // $section->addTextBreak();
        $section->addText("Cachet et signature du prestataire de formation:");

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        // Path of saved file
        $filePath = '../public/documents/emargement.docx';

        // Write file into path
        $objWriter->save($filePath);

        return $this->redirectToRoute('session_show', [
            'id' => $session->getId(),
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
