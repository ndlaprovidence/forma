<?php

namespace App\Controller;

use ZipArchive;
use App\Entity\Upload;
use App\Entity\Company;
use App\Entity\Session;
use App\Entity\Trainee;
use App\Entity\Location;
use App\Entity\Training;
use App\Form\SessionType;
use App\Util\FormaHelper;
use Doctrine\ORM\EntityManager;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Repository\UploadRepository;
use Symfony\Component\Finder\Finder;
use App\Repository\CompanyRepository;
use App\Repository\SessionRepository;
use App\Repository\TraineeRepository;
use App\Repository\LocationRepository;
use App\Repository\TrainingRepository;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
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
    private $formaHelper;

    public function __construct(FormaHelper $formaHelper)
    {
        $this->formaHelper = $formaHelper;
    }

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
     * @Route("/export", name="session_export", methods={"GET"})
     */
    public function export(EntityManagerInterface $em, SessionRepository $sr, TrainingRepository $tr, UploadRepository $ur)
    {
        $this->formaHelper->clearFolder('../public/temp');
        $this->em = $em;

        $uploads = $ur->findAll();

        // Vérfie si un upload à bien une session dans la BDD
        $finder = new Finder();
        $filesystem = new Filesystem();

        $path = '../public/uploads';
        $finder->files()->in($path);

        // Dans le répértoire
        foreach ($finder as $file) {
            $fileName = $file->getRelativePathname();
            $upload = $ur->findSameUpload($fileName);

            if (!$upload) {
                $filesystem->remove([$path.'/'.$fileName]);
            } else {
                $upload = $ur->findOneById($upload);
                if ( count($upload->getSessions() ) == 0 ) {
                    $this->em->persist($upload);
                    $this->em->remove($upload);
                    $this->em->flush();
                    $filesystem->remove([$path.'/'.$fileName]);
                }
            }
        }
 
        $spreadsheet = new Spreadsheet();
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");  

        $sheet = $spreadsheet->getActiveSheet();

        $cells = ['A1','B1','C1','D1','E1','F1','G1','H1','I1','J1','K1','L1','M1','N1', "O1"];
        $valuesHeader = [
            "Formateur", 
            "Prestation", 
            "N° de la prestation", 
            "Civilité", 
            "Nom stagiaire", 
            "Prénom stagiaire", 
            "Email du stagiaire", 
            "N° de l'établissement", 
            "Établissement", 
            "Durée de la formation", 
            "Nombre de sessions",
            "Date de session",
            "Lieu de la session",
            "Objectifs de la formation", 
            "Plateforme" 
        ];

        for ($i = 0; $i < sizeof($cells); $i++) {
            $sheet->getCell($cells[$i])->setValue($valuesHeader[$i]);
            $sheet->getCell($cells[$i])->getStyle()->getFont()->setBold(true);
            $sheet->getStyle($cells[$i])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $uploads = $ur->findAll();

        // BgColor cells
        $tabColor = ['EC7063', 'A3E4D7', 'F9E79F'];
        $color = 1;

        // Pour chaque upload
        $currentRow = 2;
        foreach ($uploads as $upload) {

            // Récupère toutes les sessions de l'upload
            $sessionCollection = $upload->getSessions();
            $sessionsTotal = count($sessionCollection);

            // Informations récurentes aux sessions
            $traineeCollection = $sessionCollection[0]->getTrainees();
            $traineeTotal = count($traineeCollection);
            $goalCollection = $sessionCollection[0]->getTraining()->getGoals();

                // Recupère tout les formateurs et evite d'ajouter ceux déjà présents
                $instructorList = [];
                foreach ($sessionCollection as $session) {

                    $instructorCollection = $session->getInstructors();
                    foreach ($instructorCollection as $instructor) {

                        $currentInstructor = $instructor->getLastName().' '.$instructor->getFirstName().' ('.$instructor->getProfession().')';
                        if (!in_array($currentInstructor, $instructorList)) {
                            array_push($instructorList, $currentInstructor);
                        }
                    }
                }
                $instructorLength = count($instructorList);
                $instructorRow = '';
                for ($i=0; $i < $instructorLength; $i++) {
                    if ( $i < $instructorLength-1 ) {
                        $instructorRow = $instructorRow.$instructorList[$i].', ';
                    } else {
                        $instructorRow = $instructorRow.$instructorList[$i];
                    }
                }

                // Durée de chaque session additionnées
                $trainingLength = "0";
                foreach ($sessionCollection as $session) {
                    $sessionStartTimeAm = $session->getStartTimeAm();
                    $sessionEndTimeAm   = $session->getEndTimeAm();
                    $sessionStartTimePm = $session->getStartTimePm();
                    $sessionEndTimePm   = $session->getEndTimePm();

                    $sessionLengthToAdd = $this->formaHelper->getHoursLength($sessionEndTimeAm,$sessionStartTimeAm,$sessionEndTimePm,$sessionStartTimePm);
                    $trainingLength     = $this->formaHelper->getHoursTotal($trainingLength, $sessionLengthToAdd);
                }
                $trainingLengthRow = $this->formaHelper->formatHoursTotal($trainingLength);

                // Recupère toutes les dates de la formation
                $i = 1;
                $dateRow = '';
                foreach ( $sessionCollection as $session ) {
                    if ( $i < $sessionsTotal ) {
                        $i++;
                        $dateRow = $dateRow.$session->getDate()->format('d-m-Y').', ';
                    } else {
                        $dateRow = $dateRow.$session->getDate()->format('d-m-Y');
                    }
                }
                $sessionNumberRow = $i;

                // Recupère tout les lieux de la formation
                $i = 1;
                $locationRow = '';
                foreach ( $sessionCollection as $session ) {
                    $currentLocation = 
                        $session->getLocation()->getName().' - '.
                        $session->getLocation()->getStreet().' '.
                        $session->getLocation()->getPostalCode().' '.
                        $session->getLocation()->getCity()           
                    ;

                    if ( $i < $sessionsTotal ) {
                        $i++;
                        $locationRow = $locationRow.$currentLocation.', ';
                    } else {
                        $locationRow = $locationRow.$currentLocation;
                    }
                }

                // Recupère tout les objectifs de la formation
                $goalLength = count($goalCollection);
                $goalRow = '';
                $i = 1;
                foreach ($goalCollection as $goal) {
                    if ( $i < $goalLength ) {
                        $i++;
                        $goalRow = $goalRow.$goal->getTitle().', ';
                    } else {
                        $goalRow = $goalRow.$goal->getTitle();
                    }
                }

            if ($currentRow % 3 == 0) $color = 0;
            else $color++;  

            // Ecriture des informations récurentes
            foreach ($traineeCollection as $trainee) {
                $sheet->getCell('A'. $currentRow)->setValue($instructorRow);
                $sheet->getStyle('A'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('B'. $currentRow)->setValue($sessionCollection[0]->getTraining()->getTitle());
                $sheet->getStyle('B'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('C'. $currentRow)->setValue($sessionCollection[0]->getTraining()->getReferenceNumber());
                $sheet->getStyle('c'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('D'. $currentRow)->setValue($trainee->getCivility());
                $sheet->getStyle('D'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('E'. $currentRow)->setValue($trainee->getLastName());
                $sheet->getStyle('E'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('F'. $currentRow)->setValue($trainee->getFirstName());
                $sheet->getStyle('F'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('G'. $currentRow)->setValue($trainee->getEmail());
                $sheet->getStyle('G'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('H'. $currentRow)->setValue($trainee->getCompany()->getReferenceNumber());
                $sheet->getStyle('H'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('I'. $currentRow)->setValue($trainee->getCompany()->getCorporateName());
                $sheet->getStyle('I'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('J'. $currentRow)->setValue($trainingLengthRow);
                $sheet->getStyle('J'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('K'. $currentRow)->setValue($sessionNumberRow);
                $sheet->getStyle('K'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('L'. $currentRow)->setValue($dateRow);
                $sheet->getStyle('L'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('M'. $currentRow)->setValue($locationRow);
                $sheet->getStyle('M'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('N'. $currentRow)->setValue($goalRow);
                $sheet->getStyle('N'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);
                $sheet->getCell('O'. $currentRow)->setValue($sessionCollection[0]->getTraining()->getPlatform());
                $sheet->getStyle('O'. $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($tabColor[$color]);

                $currentRow++;
            } 
        }

        $fileName = date('d-m-Y').'-'.rand(10000,99999).'.xlsx';

        $filePath = "../public/temp/".$fileName;
        $writer->save($filePath);

        return $this->redirect('/temp/'.$fileName);
    }

    /**
     * @Route("/new", name="session_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, CompanyRepository $cr, TraineeRepository $ter, TrainingRepository $tgr, LocationRepository $lr, UploadRepository $ur, SessionRepository $sr): Response
    {
        if ( $request->query->has('file_name')) {
            
            if ( $request->query->get('extension') != 'csv' ) {
                return $this->redirectToRoute('default', [
                    'training' => 'fail-file'
                ]);
            }

            $fileName = $request->query->get('file_name');
            $extension = $request->query->get('extension');
            $completeFileName = $request->query->get('file_name').'.'.$request->query->get('extension');
            $this->em = $em;

            $todayDate = new \DateTime('@'.strtotime('now'));

            // START READING CSV
            Cell::setValueBinder(new AdvancedValueBinder());

            $filesystem = new Filesystem();
            $inputFileType = 'Csv';
            $inputFileName = '../public/temp/'.$completeFileName;

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

                            // Créer la formation si elle n'existe pas déjà
                            $trainingTitle = $currentTrainee[15]; 
                            $trainingReferenceNumber = null; 

                            $temp = $tgr->findSameTraining($trainingTitle);

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
                                    ->setReferenceNumber($trainingReferenceNumber);
                                $this->em->persist($training);
                            }


                            // Créer un trainee si il n'existe pas déjà
                            $civility = ucfirst($currentTrainee[0]);
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
                                    ->setCivility($civility)
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

                        // Créer un upload si il n'existe pas déjà
                        $date = new \DateTime('@'.strtotime($sheetData[1][16]));
                        $temp = $sr->findSessionByParameters($location,$training,$date);

                        if ($temp)
                        {
                            $existingSession = $temp[0];
                            $existingUpload = $existingSession->getUpload()->getId();

                            $upload = $ur->findOneById($existingUpload);
                            $this->em->persist($upload);
                        } else {
                            $filesystem->copy('../public/temp/'.$completeFileName, '../public/uploads/'.$completeFileName);
                            $upload = new Upload();
                            $upload
                                ->setFileName($completeFileName)
                                ->setDate($todayDate);
                                
                            $this->em->persist($upload);
                        }

                        $sessionsNbrTotal = 1;
                        $session
                            ->setUpload($upload)
                            ->setTraining($training)
                            ->setLocation($location)
                            ->setDate($date)
                            ->setStartTimeAm(new \DateTime('@'.strtotime('09:00')))
                            ->setEndTimeAm(new \DateTime('@'.strtotime('12:00')))
                            ->setStartTimePm(new \DateTime('@'.strtotime('13:30')))
                            ->setEndTimePm(new \DateTime('@'.strtotime('17:30')));

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
                            $referenceNumber = $names[$count-1];
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
                                    ->setCity($city)
                                    ->setReferenceNumber($referenceNumber);
                                $this->em->persist($company);
                                $trainee->setCompany($company);
                            }


                            // Ajoute des dates de session et du lieu selon le nombre de sessions au total
                            $sessionsDates = explode(", ", $currentTrainee[16]);
                            $sessionsNbrTotal = count($sessionsDates);
    
                            if ( $request->query->has('current_session_number') ) {
                                $currentSessionNbr = intval($request->query->get('current_session_number'));
                                $currentSession = explode(" ", $sessionsDates[$currentSessionNbr]);
                            } else {
                                $currentSession = explode(" ", $sessionsDates[0]);
                            }

                            $count = count($currentSession);
                            $city = $currentSession[6];

                            for ($k = 6; $k<$count-1; $k++) {
                                $city = $city.' '.$currentSession[$k];
                            }

                            // Créer une location si il n'existe pas déjà
                            $postalCode = strtoupper($currentSession[5]);
                            $city = strtoupper($city);

                            $temp = $lr->findSameLocation($city,$postalCode,'');
                            if ($temp)
                            {
                                $existingLocation = $temp;
                                $location = $lr->findOneById($existingLocation);
                                $this->em->persist($location);
                            } else {
                                $location = new Location();
                                $location
                                    ->setStreet('')
                                    ->setPostalCode(intval($postalCode))
                                    ->setCity($city);
                                $this->em->persist($location);
                            }
    
                            $date = new \DateTime('@'.strtotime($currentSession[1]));
    
                            $this->em->flush();
                        }

                        // Créer un upload si il n'existe pas déjà
                        $temp = $ur->findSameUpload($completeFileName);

                        if ($temp)
                        {
                            $existingUpload = $temp;
                            $upload = $ur->findOneById($existingUpload);
                            $this->em->persist($upload);
                        } else {
                            $filesystem->copy('../public/temp/'.$completeFileName, '../public/uploads/'.$completeFileName);
                            $upload = new Upload();
                            $upload
                                ->setFileName($completeFileName)
                                ->setDate($todayDate);
                                
                            $this->em->persist($upload);
                        }
                        
                        $session
                            ->setLocation($location)
                            ->setUpload($upload)
                            ->setTraining($training)
                            ->setDate($date)
                            ->setStartTimeAm(new \DateTime('@'.strtotime('09:00')))
                            ->setEndTimeAm(new \DateTime('@'.strtotime('12:00')))
                            ->setStartTimePm(new \DateTime('@'.strtotime('13:30')))
                            ->setEndTimePm(new \DateTime('@'.strtotime('17:30')));
                    break;
                    
                    default:
                        return $this->redirectToRoute('default', [
                            'training' => 'fail-data'
                        ]);
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
                        'extension' => $extension,
                        'current_session_number' => $currentSessionNbr
                    ]);
                }
                $this->formaHelper->clearFolder('../public/temp');
                return $this->redirectToRoute('session_index', [
                    'new' => 'success'
                ]);
            } else {
                if ( $sessionsNbrTotal != 1 ) {
                    $currentSessionNbr = 1;
                    return $this->redirectToRoute('session_new', [
                        'file_name' => $fileName,
                        'extension' => $extension,
                        'current_session_number' => $currentSessionNbr
                    ]);
                }
                $this->formaHelper->clearFolder('../public/temp');
                return $this->redirectToRoute('session_index', [
                    'new' => 'success'
                ]);
            }
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'file_name' => $fileName,
            'extension' => $extension,
            'platform_name' => $platformName,
            'form' => $form->createView(),
            'total_sessions_number' => $sessionsNbrTotal
        ]);
    }

    /**
     * @Route("/{id}", name="session_show", methods={"GET"})
     */
    public function show(Session $session, Request $request,UploadRepository $ur, SessionRepository $sr, EntityManagerInterface $em, TraineeRepository $tr): Response
    {       
        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    /**
     * @Route("/{id}/tally_sheet", name="session_tally_sheet", methods={"GET"})
     */
    public function tallySheet(Session $session, UploadRepository $ur, SessionRepository $sr, EntityManagerInterface $em, TraineeRepository $tr)
    {
        $this->formaHelper->clearFolder('../public/temp');

        $this->em = $em;

        $idSession = $session->getId();

        $traineesCollection = $session->getTrainees();

        $nbTrainees = 0;
        
        foreach ($traineesCollection as $trainee) {
            $nbTrainees++;
        }

        $upload = $ur->findOneById($session->getUpload()->getId());
        $this->em->persist($upload);
        $sessionsCollection = $sr->findSessionsCollectionByUpload($upload);

        $nbSessions = 0;
        foreach ($sessionsCollection as $session) {
            $nbSessions++;
        }

        $trainingTitle = $session->getTraining()->getTitle();
        $sessionLocation = $session->getLocation()->getCity();

        $styleTable = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $styleFirstRow = ['borderBottomColor' => '0000FF', 'bgColor' => 'cccccc'];
        $timeDay = ['align' => 'center', 'bgColor' => 'cccccc'];
        $styleHeader = ['size' => 18, 'bold' => true];
        $nameTraining = ['size' => 15, 'bold' => true];
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

        if ($nbSessions == 1) {
            $section = $phpWord->addSection();
        } else {
            $section = $phpWord->addSection($landscape);
        }
            
        // Create footer
        $footer = $section->addFooter();
        
        // Footer content 
        $footer->addText("FC PRO service de formation professionnelle Continue de OGEC Notre Dame de la Providence <w:br/>9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 <w:br/>mail fcpro@ndlaprovidence.org <w:br/>N° activité 25500040250 référençable DataDocks", $lilText);
        
        $section->addImage("../public/images/FC-PRO-logo.png", [
            'height' => 40,
            'width' => 80,
            'positioning' => 'absolute'
            ]);
        $section->addText("Feuille d'émargement", $styleHeader, $textRight);
        
        $section->addTextBreak();
        $section->addText(htmlspecialchars($trainingTitle), $nameTraining, $textCenter);

        foreach ($sessionsCollection as $session) {

            $sessionDate = $session->getDate()->format('d-m-Y');
            setlocale(LC_TIME, "fr_FR");
            $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
    
            $startTimeAm = $session->getStartTimeAm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $startTimeAm = strftime("%Hh%M", strtotime($startTimeAm));
    
            $endTimeAm = $session->getEndTimeAm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $endTimeAm = strftime("%Hh%M", strtotime($endTimeAm));
    
            $startTimePm = $session->getStartTimePm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $startTimePm = strftime("%Hh%M", strtotime($startTimePm));
    
            $endTimePm = $session->getEndTimePm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $endTimePm = strftime("%Hh%M", strtotime($endTimePm));

            $section->addText(htmlspecialchars($sessionDate ." de " . $startTimeAm . " à " . $endTimeAm . " et de " . $startTimePm . " à " . $endTimePm . " à " . $sessionLocation ), $fontBold);
        }
        
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

        foreach ($sessionsCollection as $session) {
            $sessionDate = $session->getDate()->format('d-m-Y');
            setlocale(LC_TIME, "fr_FR");
            $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
            $firstRowDate = $table->addCell(4000, $cellColSpan);
            $textrun2 = $firstRowDate->addTextRun($textCenter);
    
            $textrun2->addText(htmlspecialchars($sessionDate), $fontTitle2, $textCenter);
        }

        $table->addRow();
            
        $i = 1;
        foreach ($sessionsCollection as $session) {

            $startTimeAm = $session->getStartTimeAm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $startTimeAm = strftime("%Hh%M", strtotime($startTimeAm));
    
            $endTimeAm = $session->getEndTimeAm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $endTimeAm = strftime("%Hh%M", strtotime($endTimeAm));
    
            $startTimePm = $session->getStartTimePm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $startTimePm = strftime("%Hh%M", strtotime($startTimePm));
    
            $endTimePm = $session->getEndTimePm()->format('H:i');
            setlocale(LC_TIME, "fr_FR");
            $endTimePm = strftime("%Hh%M", strtotime($endTimePm));

            if($i == 1) {
                $table->addCell(null, $cellRowContinue);
                $table->addCell(null, $cellRowContinue);
            }
                       
            $table->addCell(2000)->addText(htmlspecialchars($startTimeAm . " à " . $endTimeAm), $fontTitle2, $timeDay);
            $table->addCell(2000)->addText(htmlspecialchars($startTimePm . " à " . $endTimePm), $fontTitle2, $timeDay);
            
            $i++;

        }
        
        $traineesCollection = $session->getTrainees();

        foreach ($traineesCollection as $trainee) {
            $lastNameTrainee = $trainee->getLastName();
            $firstNameTrainee = $trainee->getFirstName();
            $companyTrainee = $trainee->getCompany();
            $table->addRow(750);
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars(" ".$lastNameTrainee . " " . $firstNameTrainee));
            $table->addCell(2000, $verticalCenter)->addText(htmlspecialchars(" ".$companyTrainee));

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
        
        // Name generated of file
        $fileName = 'Emargement-'.$trainingTitle."-".$sessionDate.".docx";

        // Path of saved file
        $filePath = "../public/temp/".$fileName;

        // Write file into path
        $objWriter->save($filePath);

        return $this->redirect("/temp/".$fileName, 301);
    }


    /**
     * @Route("/{id}/all-documents", name="session_all_documents", methods={"GET"})
     */
    public function createMultipleWord(Request $request, EntityManagerInterface $em, Session $session, TraineeRepository $ter, SessionRepository $sr, TrainingRepository $tgr, UploadRepository $ur): Response
    {
        $this->formaHelper->clearFolder('../public/temp');

        $this->em = $em;
        $parameters = $request->attributes->get('_route_params');

        $session = $sr->findOneById(intval($parameters['id']));
        $this->em->persist($session);

        $upload = $ur->findOneById($session->getUpload()->getId());
        $sessionCollection = $sr->findSessionsCollectionByUpload($upload);

        $traineeCollection = $session->getTrainees();

        $errors = [];
        $locationName = [];
        $locationPc = [];
        $locationCity = [];
        $locationStreet = [];

        $sessionTrainingTitle = $session->getTraining()->getTitle();
        if ( $sessionTrainingTitle == null || $sessionTrainingTitle == '' || $sessionTrainingTitle == 'Non-renseigné') {
            array_push($errors, 'training_title');
        }

        $sessionTrainingRef = $session->getTraining()->getReferenceNumber();
        if ( $sessionTrainingRef == null || $sessionTrainingRef == '' || $sessionTrainingRef == 'Non-renseigné') {
            array_push($errors, 'training_ref_number');
        }

        if ( count($session->getTraining()->getGoals()) == 0 ) {
            array_push($errors, 'training_goals');
        }

        foreach ( $sessionCollection as $currentSession ) {
            $currentSessionLocationName = $currentSession->getLocation()->getName();
            if ( $currentSessionLocationName == null || $currentSessionLocationName == '' || $currentSessionLocationName == "Nom de l'établissement non-renseigné" ) {
                array_push($locationName, $currentSession->getDate()->format('d-m-Y'));
            }

            $currentSessionLocationName = $currentSession->getLocation()->getPostalCode();
            if ( $currentSessionLocationName == null || $currentSessionLocationName == '' || $currentSessionLocationName == 'Non-renseigné' ) {
                array_push($locationPc, $currentSession->getDate()->format('d-m-Y'));
            }

            $currentSessionLocationCity = $currentSession->getLocation()->getCity();
            if ( $currentSessionLocationCity == null || $currentSessionLocationCity == '' || $currentSessionLocationCity == 'Non-renseignée' ) {
                array_push($locationCity, $currentSession->getDate()->format('d-m-Y'));
            }

            $currentSessionLocationStreet = $currentSession->getLocation()->getStreet();
            if ( $currentSessionLocationStreet == null || $currentSessionLocationStreet == '' || $currentSessionLocationStreet == 'Non-renseignée' ) {
                array_push($locationStreet, $currentSession->getDate()->format('d-m-Y'));
            }
        }

        if ( count($errors) > 0 ) {
            return $this->redirectToRoute('session_show', [
                'errors' => $errors,
                'location_name' => $locationName,
                'location_pc' => $locationPc,
                'location_city' => $locationCity,
                'location_street' => $locationStreet,
                'id' => $session->getId()
            ]);
        }


        $document = ['Convocation','Attestation','Inscription','Reception'];
        $currentDoc = 0;

        for ($currentDoc ; $currentDoc<count($document) ; $currentDoc++) {

            switch ($document[$currentDoc]) {
                
                case 'Convocation':

                    $phpWord = new PhpWord();

                    foreach ( $traineeCollection as $trainee ) {
                        $trainee = $ter->findOneById($trainee->getId());
                        $this->em->persist($trainee);
        
                        $training = $tgr->findOneById($session->getTraining()->getId());
                        $this->em->persist($training);
        
                        $traineeCivility        = $trainee->getCivility();
                        $traineeLastName        = $trainee->getLastName();
                        $traineeFirstName       = $trainee->getFirstName();
                        $traineeCompanyName     = $trainee->getCompany()->getCorporateName();
                        $traineeCompanyCity     = $trainee->getCompany()->getCity();
                        $traineeCompanyRef      = $trainee->getCompany()->getReferenceNumber();
                        $sessionTrainingTitle   = $session->getTraining()->getTitle();
                        $sessionTrainingRef     = $session->getTraining()->getReferenceNumber();
                        $sessionDate            = $session->getDate()->format('Y-m-d');
                                                setlocale(LC_TIME, "fr_FR");
                                                $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
                        $sessionStartTimeAm     = $session->getStartTimeAm()->format('H:i');
                        $sessionEndTimeAm       = $session->getEndTimeAm()->format('H:i');
                        $sessionStartTimePm     = $session->getStartTimePm()->format('H:i');
                        $sessionEndTimePm       = $session->getEndTimePm()->format('H:i');

                        $sessionHoursLength = '0';
                        foreach ( $sessionCollection as $session ) {
                            $sessionHoursLengthTmp = $this->formaHelper->getHoursLength($session->getEndTimeAm(), $session->getStartTimeAm(), $session->getEndTimePm(), $session->getStartTimePm());
                            $sessionHoursLength = $this->formaHelper->getHoursTotal($sessionHoursLength,$sessionHoursLengthTmp);
                        }
                        $sessionHoursLength = $this->formaHelper->formatHoursTotal($sessionHoursLength);
                        
                        $sessionLocationName    = $session->getLocation()->getName();
                        $sessionLocationStreet  = $session->getLocation()->getStreet();
                        $sessionLocationPostalCode = $session->getLocation()->getPostalCode();
                        $sessionLocationCity    = $session->getLocation()->getCity();
                        $sessionTrainingGoals   = $session->getTraining()->getGoals();
                        $trainingGoalsLength    = $training->getGoals();
                                                $trainingGoalsLength = count($trainingGoalsLength);
                        $todayDate              = date('d/m/Y');
                        
        
                        $phpWord->setDefaultFontName('Arial');
                        $phpWord->setDefaultFontSize(11.5);
                        $phpWord->setDefaultParagraphStyle(['lineHeight' => 1.3]);
        
                        $section = $phpWord->addSection();
                        $header = $section->addHeader();
                        $footer = $section->addFooter();
        
        
                        // Content header
                        $header->addImage("../public/images/FC-PRO-logo.png", [
                            'height' => 50,
                            'width' => 100,
                            'positioning' => 'absolute'
                        ]);
                        $header->addText("OGEC Notre Dame de la Providence <w:br/> 
                                        Service de Formation professionnelle continue <w:br/>
                                        9, rue chanoine Bérenger BP 340 <w:br/>
                                        50300 AVRANCHES <w:br/>", ['size' => 10], ['align' => 'right']);
        
                        // Content footer 
                        $footer->addText("FC PRO service de formation professionnelle Continue de OGEC Notre Dame de la Providence <w:br/>9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 <w:br/>mail fcpro@ndlaprovidence.org <w:br/>N° activité 25500040250 référençable DataDocks", ['size' => 10], ['align' => 'left']);                             
        

                        $section->addText(htmlspecialchars("CONVOCATION À UNE FORMATION"), ['bold' => true, 'size' => 16 ], ['align' => 'center']);
                        $pageContent = $section->addTextRun();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("A l'attention de "));
                        $pageContent->addText(htmlspecialchars( $traineeCivility." ".$traineeLastName." ".$traineeFirstName.", ".$traineeCompanyName." ".$traineeCompanyCity." ".$traineeCompanyRef."."), ['bold' => true]);
                        $pageContent->addTextBreak(); 
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Vous voudrez bien vous présenter à la session de la formation :"));
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars( $sessionTrainingTitle." "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("identifiée par le numéro ".$sessionTrainingRef." d'une durée de ".$sessionHoursLength." qui aura lieu :"));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $previousCurrentSession = '';
                        foreach ( $sessionCollection as $session ) {
                            $currentSession = $session->getLocation()->getName().", ".$session->getLocation()->getStreet()." ".$session->getLocation()->getPostalCode()." ".$session->getLocation()->getCity().".";
                            if ( $currentSession != $previousCurrentSession ) {
                                $pageContent->addText(htmlspecialchars("Dans les locaux de ".$sessionLocationName.", ".$sessionLocationStreet." ".$sessionLocationPostalCode." ".$sessionLocationCity." :"));
                                $pageContent->addTextBreak();
                            }
                            $sessionDate            = $session->getDate()->format('Y-m-d');
                            setlocale(LC_TIME, "fr_FR");
                            $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
        
                            $pageContent->addText(htmlspecialchars($sessionDate), ['bold' => true]);
                            $pageContent->addText(htmlspecialchars(" de ".$sessionStartTimeAm." à ".$sessionEndTimeAm." et de ".$sessionStartTimePm." à ".$sessionEndTimePm));
                            $pageContent->addTextBreak();
                            
                            $previousCurrentSession = $currentSession;
                        }

                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Objectifs de la formation "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("(pour plus de détails, se rapporter au programme transmis précédemment) :"));
                        $pageContent->addTextBreak();
                        $i = 0;
                        foreach ( $sessionTrainingGoals as $goal ) {
                            $i++;
                            if ( $trainingGoalsLength-1 >= $i ) {
                                $pageContent->addText(htmlspecialchars($goal.", "));
                            } else {
                                $pageContent->addText(htmlspecialchars($goal."."));
                            }
                        }
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Votre arrivée dans les locaux est souhaitée un quart d'heure avant le début de la session."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Je vous souhaite une bonne formation."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        // Afficher la date du jourAttestation
                        $pageContent->addText(htmlspecialchars("À Avranches, le ".$todayDate."."));
                        $pageContent->addTextBreak();
                        $pageContent->addImage("../public/images/signature.png", [
                            'height' => 100,
                            'width' => 170
                        ]);
                    }
                break;
                
                case 'Attestation':
                    $phpWord = new PhpWord();

                    foreach ( $traineeCollection as $trainee ) {
                        $trainee = $ter->findOneById($trainee->getId());
                        $this->em->persist($trainee);
        
                        $training = $tgr->findOneById($session->getTraining()->getId());
                        $this->em->persist($training);
        
                        $traineeCivility        = $trainee->getCivility();
                        $traineeLastName        = $trainee->getLastName();
                        $traineeFirstName       = $trainee->getFirstName();
                        $traineeCompanyName     = $trainee->getCompany()->getCorporateName();
                        $traineeCompanyCity     = $trainee->getCompany()->getCity();
                        $traineeCompanyRef      = $trainee->getCompany()->getReferenceNumber();
                        $sessionTrainingTitle   = $session->getTraining()->getTitle();
                        $sessionTrainingRef     = $session->getTraining()->getReferenceNumber();
                        $sessionDate            = $session->getDate()->format('Y-m-d');
                                                setlocale(LC_TIME, "fr_FR");
                                                $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
                        $sessionStartTimeAm     = $session->getStartTimeAm()->format('H:i');
                        $sessionEndTimeAm       = $session->getEndTimeAm()->format('H:i');
                        $sessionStartTimePm     = $session->getStartTimePm()->format('H:i');
                        $sessionEndTimePm       = $session->getEndTimePm()->format('H:i');
                        $sessionLocationName    = $session->getLocation()->getName();
                        $sessionLocationStreet  = $session->getLocation()->getStreet();
                        $sessionLocationPostalCode = $session->getLocation()->getPostalCode();
                        $sessionLocationCity    = $session->getLocation()->getCity();
                        $sessionTrainingGoals   = $session->getTraining()->getGoals();
                        $trainingGoalsLength    = $training->getGoals();
                                                $trainingGoalsLength = count($trainingGoalsLength);
                        $todayDate              = date('d/m/Y');
                        
        
                        $phpWord->setDefaultFontName('Arial');
                        $phpWord->setDefaultFontSize(11.5);
                        $phpWord->setDefaultParagraphStyle(['lineHeight' => 1.3]);
        
                        $section = $phpWord->addSection();
                        $header = $section->addHeader();
                        $footer = $section->addFooter();
        
        
                        // Content header
                        $header->addImage("../public/images/FC-PRO-logo.png", [
                            'height' => 50,
                            'width' => 100,
                            'positioning' => 'absolute'
                        ]);
                        $header->addText("OGEC Notre Dame de la Providence <w:br/> 
                                        Service de Formation professionnelle continue <w:br/>
                                        9, rue chanoine Bérenger BP 340 <w:br/>
                                        50300 AVRANCHES <w:br/>", ['size' => 10], ['align' => 'right']);
        
                        // Content footer 
                        $footer->addText("FC PRO service de formation professionnelle Continue de OGEC Notre Dame de la Providence <w:br/>9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 <w:br/>mail fcpro@ndlaprovidence.org <w:br/>N° activité 25500040250 référençable DataDocks", ['size' => 10], ['align' => 'left']); 
                        

                        $section->addText(htmlspecialchars("ATTESTATION DE FORMATION"), ['bold' => true, 'size' => 16 ], ['align' => 'center']);
                        $pageContent = $section->addTextRun();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Je soussigné, Philippe LECOUVREUR, responsable de FC PRO service de formation professionnelle continue du lycée Notre Dame de la Providence, atteste que : "));
                        $pageContent->addText(htmlspecialchars( $traineeCivility." ".$traineeLastName." ".$traineeFirstName.", ".$traineeCompanyName." ".$traineeCompanyCity." ".$traineeCompanyRef."."), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("a suivi la prestation de formation décrite ci-dessous dans les locaux de ".$sessionLocationName.", ".$sessionLocationStreet." ".$sessionLocationPostalCode." ".$sessionLocationCity."."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Prestation de formation : "));
                        $pageContent->addText(htmlspecialchars( $sessionTrainingTitle." "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("identifiée par le numéro ".$sessionTrainingRef." en date de "));
                        $i = 0;
                        foreach ( $sessionCollection as $session ) {
                            $i++;
                            $sessionDate = $session->getDate()->format('Y-m-d');
                            setlocale(LC_TIME, "fr_FR");
                            $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
                            $pageContent->addText(htmlspecialchars($sessionDate), ['bold' => true]);
                            if ( $i == count($sessionCollection)-1) {
                                $pageContent->addText(htmlspecialchars(" et "));
                            } else if ( $i < count($sessionCollection)) {
                                $pageContent->addText(htmlspecialchars(", "));
                            } else {
                                $pageContent->addText(htmlspecialchars(" "));
                            }
                        }
                        $pageContent->addText(htmlspecialchars("pendant une durée de ".$sessionHoursLength."."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Objectifs de la formation :"), ['bold' => true]);
                        $pageContent->addTextBreak();
                        // Données issues de la BDD
                        $i = 0;
                        foreach ( $sessionTrainingGoals as $goal ) {
                            $i++;
                            if ( $trainingGoalsLength-1 >= $i ) {
                                $pageContent->addText(htmlspecialchars($goal.", "));
                            } else {
                                $pageContent->addText(htmlspecialchars($goal."."));
                            }
                        }
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Fait pour servir et valoir ce que de droit."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        // Afficher la date du jour
                        $pageContent->addText(htmlspecialchars("À Avranches, le ".$sessionDate."."));
                        $pageContent->addTextBreak();
                        $pageContent->addImage("../public/images/signature.png", [
                            'height' => 100,
                            'width' => 170
                        ]);
                    }
                break;

                case 'Inscription':
                    $phpWord = new PhpWord();

                    foreach ( $traineeCollection as $trainee ) {
                        $trainee = $ter->findOneById($trainee->getId());
                        $this->em->persist($trainee);
        
                        $training = $tgr->findOneById($session->getTraining()->getId());
                        $this->em->persist($training);
        
                        $traineeCivility        = $trainee->getCivility();
                        $traineeLastName        = $trainee->getLastName();
                        $traineeFirstName       = $trainee->getFirstName();
                        $traineeCompanyName     = $trainee->getCompany()->getCorporateName();
                        $traineeCompanyCity     = $trainee->getCompany()->getCity();
                        $traineeCompanyRef      = $trainee->getCompany()->getReferenceNumber();
                        $sessionTrainingTitle   = $session->getTraining()->getTitle();
                        $sessionTrainingRef     = $session->getTraining()->getReferenceNumber();
                        $sessionDate            = $session->getDate()->format('Y-m-d');
                                                setlocale(LC_TIME, "fr_FR");
                                                $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
                        $sessionStartTimeAm     = $session->getStartTimeAm()->format('H:i');
                        $sessionEndTimeAm       = $session->getEndTimeAm()->format('H:i');
                        $sessionStartTimePm     = $session->getStartTimePm()->format('H:i');
                        $sessionEndTimePm       = $session->getEndTimePm()->format('H:i');
                        $sessionLocationName    = $session->getLocation()->getName();
                        $sessionLocationStreet  = $session->getLocation()->getStreet();
                        $sessionLocationPostalCode = $session->getLocation()->getPostalCode();
                        $sessionLocationCity    = $session->getLocation()->getCity();
                        $sessionTrainingGoals   = $session->getTraining()->getGoals();
                        $trainingGoalsLength    = $training->getGoals();
                                                $trainingGoalsLength = count($trainingGoalsLength);
                        $todayDate              = date('d/m/Y');
                        
        
                        $phpWord->setDefaultFontName('Arial');
                        $phpWord->setDefaultFontSize(11.5);
                        $phpWord->setDefaultParagraphStyle(['lineHeight' => 1.3]);
        
                        $section = $phpWord->addSection();
                        $header = $section->addHeader();
                        $footer = $section->addFooter();
        
        
                        // Content header
                        $header->addImage("../public/images/FC-PRO-logo.png", [
                            'height' => 50,
                            'width' => 100,
                            'positioning' => 'absolute'
                        ]);
                        $header->addText("OGEC Notre Dame de la Providence <w:br/> 
                                        Service de Formation professionnelle continue <w:br/>
                                        9, rue chanoine Bérenger BP 340 <w:br/>
                                        50300 AVRANCHES <w:br/>", ['size' => 10], ['align' => 'right']);
        
                        // Content footer 
                        $footer->addText("FC PRO service de formation professionnelle Continue de OGEC Notre Dame de la Providence <w:br/>9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 <w:br/>mail fcpro@ndlaprovidence.org <w:br/>N° activité 25500040250 référençable DataDocks", ['size' => 10], ['align' => 'left']); 
                        

                        $section->addText(htmlspecialchars("INSCRIPTION À UNE FORMATION"), ['bold' => true, 'size' => 16 ], ['align' => 'center']);
                        $pageContent = $section->addTextRun();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("À l'attention de "));
                        $pageContent->addText(htmlspecialchars( $traineeCivility." ".$traineeLastName." ".$traineeFirstName.", ".$traineeCompanyName." ".$traineeCompanyCity." ".$traineeCompanyRef."."), ['bold' => true]);
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("J'accuse réception de votre inscription à la formation :"));
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars( $sessionTrainingTitle." "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("identifiée par le numéro ".$sessionTrainingRef." d'une durée de ".$sessionHoursLength." qui aura lieu :"));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $previousCurrentSession = '';
                        foreach ( $sessionCollection as $session ) {
                            $currentSession = $session->getLocation()->getName().", ".$session->getLocation()->getStreet()." ".$session->getLocation()->getPostalCode()." ".$session->getLocation()->getCity().".";
                            if ( $currentSession != $previousCurrentSession ) {
                                $pageContent->addText(htmlspecialchars("Dans les locaux de ".$sessionLocationName.", ".$sessionLocationStreet." ".$sessionLocationPostalCode." ".$sessionLocationCity." :"));
                                $pageContent->addTextBreak();
                            }
                            $sessionDate            = $session->getDate()->format('Y-m-d');
                            setlocale(LC_TIME, "fr_FR");
                            $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
        
                            $pageContent->addText(htmlspecialchars($sessionDate), ['bold' => true]);
                            $pageContent->addText(htmlspecialchars(" de ".$sessionStartTimeAm." à ".$sessionEndTimeAm." et de ".$sessionStartTimePm." à ".$sessionEndTimePm));
                            $pageContent->addTextBreak();
                            
                            $previousCurrentSession = $currentSession;
                        }
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Objectifs de la formation "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("(pour plus de détails, se rapporter au programme transmis précédemment) :"));
                        $pageContent->addTextBreak();
                        $i = 0;
                        foreach ( $sessionTrainingGoals as $goal ) {
                            $i++;
                            if ( $trainingGoalsLength-1 >= $i ) {
                                $pageContent->addText(htmlspecialchars($goal.", "));
                            } else {
                                $pageContent->addText(htmlspecialchars($goal."."));
                            }
                        }
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Cette formation pourra être annulée si le nombre d'inscrits n'atteint pas un effectif minimum."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Vous recevrez une convocation 10 jours avant le début de la session."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("À Avranches, le ".$todayDate."."));
                        $pageContent->addTextBreak();
                        $pageContent->addImage("../public/images/signature.png", [
                            'height' => 100,
                            'width' => 170
                        ]);
                    }
                break;

                case 'Reception':
                    $phpWord = new PhpWord();

                    foreach ( $traineeCollection as $trainee ) {
                        $trainee = $ter->findOneById($trainee->getId());
                        $this->em->persist($trainee);
        
                        $training = $tgr->findOneById($session->getTraining()->getId());
                        $this->em->persist($training);
        
                        $traineeCivility        = $trainee->getCivility();
                        $traineeLastName        = $trainee->getLastName();
                        $traineeFirstName       = $trainee->getFirstName();
                        $traineeCompanyName     = $trainee->getCompany()->getCorporateName();
                        $traineeCompanyCity     = $trainee->getCompany()->getCity();
                        $traineeCompanyRef      = $trainee->getCompany()->getReferenceNumber();
                        $sessionTrainingTitle   = $session->getTraining()->getTitle();
                        $sessionTrainingRef     = $session->getTraining()->getReferenceNumber();
                        $sessionDate            = $session->getDate()->format('Y-m-d');
                                                setlocale(LC_TIME, "fr_FR");
                                                $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
                        $sessionStartTimeAm     = $session->getStartTimeAm()->format('H:i');
                        $sessionEndTimeAm       = $session->getEndTimeAm()->format('H:i');
                        $sessionStartTimePm     = $session->getStartTimePm()->format('H:i');
                        $sessionEndTimePm       = $session->getEndTimePm()->format('H:i');
                        $sessionLocationName    = $session->getLocation()->getName();
                        $sessionLocationStreet  = $session->getLocation()->getStreet();
                        $sessionLocationPostalCode = $session->getLocation()->getPostalCode();
                        $sessionLocationCity    = $session->getLocation()->getCity();
                        $sessionTrainingGoals   = $session->getTraining()->getGoals();
                        $trainingGoalsLength    = $training->getGoals();
                                                $trainingGoalsLength = count($trainingGoalsLength);
                        $todayDate              = date('d/m/Y');
                        
        
                        $phpWord->setDefaultFontName('Arial');
                        $phpWord->setDefaultFontSize(11.5);
                        $phpWord->setDefaultParagraphStyle(['lineHeight' => 1.3]);
        
                        $section = $phpWord->addSection();
                        $header = $section->addHeader();
                        $footer = $section->addFooter();
        
        
                        // Content header
                        $header->addImage("../public/images/FC-PRO-logo.png", [
                            'height' => 50,
                            'width' => 100,
                            'positioning' => 'absolute'
                        ]);
                        $header->addText("OGEC Notre Dame de la Providence <w:br/> 
                                        Service de Formation professionnelle continue <w:br/>
                                        9, rue chanoine Bérenger BP 340 <w:br/>
                                        50300 AVRANCHES <w:br/>", ['size' => 10], ['align' => 'right']);
        
                        // Content footer 
                        $footer->addText("FC PRO service de formation professionnelle Continue de OGEC Notre Dame de la Providence <w:br/>9, rue chanoine Bérenger BP 340, 50300 AVRANCHES. Tel 02.33.58.02.22 <w:br/>mail fcpro@ndlaprovidence.org <w:br/>N° activité 25500040250 référençable DataDocks", ['size' => 10], ['align' => 'left']); 
                        

                        $section->addText(htmlspecialchars('ACCUSÉ DE RÉCEPTION'), ['bold' => true, 'size' => 16 ], ['align' => 'center']);
                        $pageContent = $section->addTextRun();
                        $pageContent->addTextBreak(); 
                        $pageContent->addText(htmlspecialchars("Je soussigné, "));
                        $pageContent->addText(htmlspecialchars( $traineeCivility." ".$traineeLastName." ".$traineeFirstName.", ".$traineeCompanyName." ".$traineeCompanyCity." ".$traineeCompanyRef." "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("confirme avoir reçu une attestation pour la formation que j'ai suivie "));
                        foreach ( $sessionCollection as $session ) {
                            $sessionDate            = $session->getDate()->format('Y-m-d');
                            setlocale(LC_TIME, "fr_FR");
                            $sessionDate = strftime("%A %d %B %G", strtotime($sessionDate));
                        }
                        $pageContent->addText(htmlspecialchars($sessionDate." "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars("pendant une durée de ".$sessionHoursLength." dans les locaux de ".$sessionLocationName.", ".$sessionLocationStreet." ".$sessionLocationPostalCode." ".$sessionLocationCity."."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Prestation de la formation : "));
                        $pageContent->addText(htmlspecialchars( $sessionTrainingTitle." "), ['bold' => true]);
                        $pageContent->addText(htmlspecialchars( "identifiée par le numéro ".$sessionTrainingRef));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Objectifs de la formation :"), ['bold' => true]);
                        $pageContent->addTextBreak();
                        $i = 0;
                        foreach ( $sessionTrainingGoals as $goal ) {
                            $i++;
                            if ( $trainingGoalsLength-1 >= $i ) {
                                $pageContent->addText(htmlspecialchars($goal.", "));
                            } else {
                                $pageContent->addText(htmlspecialchars($goal."."));
                            }
                        }
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("Fait pour servir et valoir ce que de droit."));
                        $pageContent->addTextBreak();
                        $pageContent->addTextBreak();
                        $pageContent->addText(htmlspecialchars("À Avranches, le ".$todayDate."."));
                    }
                break;

                default:

                break;
            }

            // Saving the document as OOXML file...
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            
            // Name generated of file
            $fileName = $document[$currentDoc].".docx";

            // Path of saved file
            $filePath = "../public/temp/".$fileName;

            // Write file into path
            $objWriter->save($filePath);
        }

        $finder = new Finder();
        $finder->files()->in('../public/temp');
 
        $zipName = $sessionTrainingRef."-".$sessionDate."-".rand(10000,99999).".zip";

        $zip = new ZipArchive();  
        $res = $zip->open("../public/temp/".$zipName, ZipArchive::CREATE);  
        
        foreach ( $finder as $file ) {
            $fileNameWithExtension = $file->getRelativePathname();
            $zip->addFile("../public/temp/".$fileNameWithExtension, $fileNameWithExtension);  
        };
        $zip->close();

        return $this->redirect("/temp/".$zipName, 301);
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

            return $this->redirectToRoute('session_show', [
                'id' => $session->getId(),
                'update' => 'success'
            ]);
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