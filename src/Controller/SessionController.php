<?php

namespace App\Controller;

use App\Entity\Upload;
use League\Csv\Reader;
use App\Entity\Company;
use App\Entity\Session;
use App\Entity\Trainee;
use App\Form\SessionType;
use App\Entity\TraineeParticipation;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $session = new Session();

        if ( $request->query->has('file_name') ) {
            $fileName = $request->query->get('file_name');

            // Create a new empty session
            $entityManager = $this->getDoctrine()->getManager();
            $upload = new Upload();
            $upload->setFileName($fileName);
            $session->setUpload($upload);
            $entityManager->persist($session);
            $entityManager->flush();
            
            // Read CSV file
            $reader = Reader::createFromPath('../public/uploads/'. $fileName);
            $results = $reader->fetchAssoc();
    
            $this->em = $em;
            foreach ($results as $row) {

                $trainee = (new Trainee())
                    ->setLastName($row["Nom de l'enseignant"])
                    ->setFirstName($row["Nom de l'enseignant"])
                    ->setEmail($row["Email de l'établissement"])          
                ;

                $this->em->persist($trainee);
                
                if ($row['UP'] == "Immaculee Conception ST JAMES 0501367P" ) {
                    $company = (new Company())
                        ->setCorporateName('doublon');
                        // ->setStreet($row[''])
                        // ->setPostalCode($row[''])
                        // ->setCity($row[''])
                        // ->setSiretNumber($row[''])
                        // ->setPhoneNumber($row[''])
                        $this->em->persist($company);
                }else{
                    $company = (new Company())
                    ->setCorporateName($row['UP']);
                    // ->setStreet($row[''])
                    // ->setPostalCode($row[''])
                    // ->setCity($row[''])
                    // ->setSiretNumber($row[''])
                    // ->setPhoneNumber($row[''])
                    $this->em->persist($company);
                };

                // $this->em->persist($company);
                $trainee->setCompany($company);
            }

            $this->em->flush();

            $traineeParticipation = new TraineeParticipation();
            $traineeParticipation->addSession($session);
            $traineeParticipation->setConvocation('blabla');
            $entityManager->persist($traineeParticipation);
            $entityManager->flush();
        }

        
        // Ouvrir le fichier CSV
        // OPCALIA / FORMIRIS
        // Importer chaque ligne utilisateur
        // Si il est dans la base ne pas l'importer
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
