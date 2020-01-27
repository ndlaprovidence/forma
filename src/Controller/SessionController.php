<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Entity\Company;
use App\Entity\Session;
use App\Entity\Trainee;
use App\Form\SessionType;
use App\Entity\TraineeParticipation;
use App\Repository\SessionRepository;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Argument\ServiceLocator;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $session = new Session();

        if ( $request->query->has('file_name') ) {
            $fileName = $request->query->get('file_name');
            $this->em = $em;

            // Create a new empty session with the upload linked
            $upload = new Upload();
            $upload->setFileName($fileName);
            $session->setUpload($upload);
            $this->em->persist($session);
            $this->em->flush();

            // START READING CSV
            Cell::setValueBinder(new AdvancedValueBinder());

            $inputFileType = 'Csv';
            $inputFileName = './public/uploads/'.$fileName;

            /**  Create a new Reader of the type defined in $inputFileType  **/
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            /**  Set the delimiter to a TAB character  **/
            $reader->setDelimiter(";");
            $spreadsheet = $reader->load($inputFileName);
        
            $loadedSheetNames = $spreadsheet->getSheetNames();
            
            /**  Load the file to a Spreadsheet Object  **/
            $output->writeln($loadedSheetNames);
            
            foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $spreadsheet->setActiveSheetIndexByName($loadedSheetName);            
                $sheetData = $spreadsheet->getActiveSheet()->toArray();

                // COMPARATIF DES DEUX FORMATS CSV POSSIBLES
                if ($sheetData[0][0] == "Civilité") {
                    // Opcalia
                    $plateform = 1;
                } else if ( $sheetData[0][0] == "Prestation" ) {
                    // Formiris
                    $plateform = 2;
                } else {
                    return ('Erreur');
                }


                // AJOUT A LA BDD SELON LE FORMAT CSV
                if ($plateform == 1) {
                    for ($i = 1; $i<= sizeof($sheetData)-1; $i++)
                    {
                        $trainee = (new Trainee())
                            ->setLastName($sheetData[$i][4])
                            ->setFirstName($sheetData[$i][4])
                            ->setEmail($sheetData[$i][7])          
                        ;
                        $this->em->persist($trainee);
    
                        $company = (new Company())
                            ->setCorporateName($sheetData[$i][6])
                        ;
    
                        $this->em->persist($company);
                        $trainee->setCompany($company);
                    }
    
                    $this->em->flush();
                } else if ($plateform == 2) {
                    for ($i = 1; $i<= sizeof($sheetData)-1; $i++)
                    {
                        $trainee = (new Trainee())
                            ->setLastName($sheetData[$i][2])
                            ->setFirstName($sheetData[$i][1])
                            ->setEmail($sheetData[$i][5])          
                        ;
                        $this->em->persist($trainee);
    
                        $company = (new Company())
                            ->setCorporateName($sheetData[$i][6])
                        ;
    
                        $this->em->persist($company);
                        $trainee->setCompany($company);
                    }
    
                    $this->em->flush();
                }
            }
        }
        
        // FINSIED - INSERT LA SESSION VIDE
        // FINSIED - Ouvrir le fichier CSV
        // DIFFERENCE OPCALIA / FORMIRIS
        // FINSIED - Importer chaque ligne utilisateur
        // INSERT L'utilisateur si il n'y est pas
        // FINSIED - ::TraineeParticipation INSERT LA SESSION ET INSERT LES UTILISATEURS
        // Extraire les utilisateurs du CSV pour l'afficher dans un tableau la page nouvelle session
        // Ajouter dans la table traineeParticipation la nouvelle session et les stagiaires associés

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
