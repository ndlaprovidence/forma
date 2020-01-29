<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Entity\Company;
use App\Entity\Session;
use App\Entity\Trainee;
use App\Entity\Training;
use App\Form\SessionType;
use App\Entity\TrainingCategory;
use App\Repository\CompanyRepository;
use App\Repository\SessionRepository;
use App\Repository\TraineeRepository;
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
    public function new(Request $request, EntityManagerInterface $em, CompanyRepository $cr, TraineeRepository $ter, TrainingRepository $tgr, TrainingCategoryRepository $tgcr): Response
    {
        if ( $request->query->has('file_name') ) {
            $fileName = $request->query->get('file_name');
            $this->em = $em;

            $session = new Session();

            // Create a new empty session with the upload linked
            $upload = new Upload();
            $upload->setFileName($fileName);
            $session->setUpload($upload);
            $this->em->persist($session);
            $this->em->flush();

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

                // COMPARATIF DES DEUX FORMATS CSV POSSIBLES
                if ($sheetData[0][0] == 'Civilité') {
                    // Opcalia
                    $plateform = 1;
                } else if ( $sheetData[0][0] == 'Prestation' ) {
                    // Formiris
                    $plateform = 2;
                } else {
                    return ('Erreur');
                }

                // AJOUT A LA BDD SELON LE FORMAT CSV
                if ($plateform == 1) {
                    for ($i = 1; $i< sizeof($sheetData); $i++)
                    {

                        // Créer la category de formation si elle n'existe pas déjà
                        $trainingCategoryTitle = $sheetData[$i][14]; 

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
                        $trainingTitle = $sheetData[$i][15]; 

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
                                ->setTrainingCategory($trainingCategory);
                            $this->em->persist($training);
                        }


                        // Créer un trainee si il n'existe pas déjà
                        $lastName = strtoupper($sheetData[$i][2]);
                        $firstName = strtolower($sheetData[$i][1]); 
                        $firstName = ucfirst($firstName);
                        $email = strtolower($sheetData[$i][5]); 

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
    

                        // Créer une company si il n'existe pas déjà
                        $corporateName = $sheetData[$i][7];
                        $street = strtolower($sheetData[$i][9]);
                        $city = strtoupper($sheetData[$i][12]);

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
                                ->setPostalCode($sheetData[$i][11])
                                ->setCity($city)
                                ->setSiretNumber($sheetData[$i][8])
                                ->setPhoneNumber($sheetData[$i][4]);
                            $this->em->persist($company);
                            $trainee->setCompany($company);
                        }


                        // Ajoute la formation et les stagiaire à la session
                        $startDate = new \DateTime('@'.strtotime($sheetData[$i][16]));
                        $session
                            ->addTrainee($trainee)
                            ->setTraining($training)
                            ->setStartDate($startDate);

                        $this->em->flush();
                    }

                    //////////////////////////////////////////////////////////////////////////////

                } else if ($plateform == 2) {
                    for ($i = 1; $i< sizeof($sheetData); $i++)
                    {

                        // Créer la formation si elle n'existe pas déjà
                        $trainingTitle = $sheetData[$i][0]; 

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
                                ->setPlatform('Formiris');
                            $this->em->persist($training);
                        }


                        // Créer un trainee si il n'existe pas déjà
                        $names = explode(" ", $sheetData[$i][4]);
                        $lastName = strtoupper($names[0]);
                        $firstName = strtolower($names[1]); 
                        $firstName = ucfirst($firstName);
                        $email = strtolower($sheetData[$i][7]); 

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
    

                        // Créer une company si il n'existe pas déjà
                        $names = explode(" ", $sheetData[$i][6]);
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


                        // Ajoute la formation et les stagiaire à la session
                        $startDate = new \DateTime('@'.strtotime('2020-01-01'));
                        $session
                            ->addTrainee($trainee)
                            ->setTraining($training)
                            ->setStartDate($startDate);

                        $this->em->persist($session);

                        $this->em->flush();
                    }
                }
            }
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('session_index');
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'file_name' => $fileName,
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
