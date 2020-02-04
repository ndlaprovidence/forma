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
use App\Repository\UploadRepository;
use App\Repository\CompanyRepository;
use App\Repository\SessionRepository;
use App\Repository\TraineeRepository;
use App\Repository\LocationRepository;
use App\Repository\TrainingRepository;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session,
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
