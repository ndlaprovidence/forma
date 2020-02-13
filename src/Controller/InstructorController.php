<?php

namespace App\Controller;

use App\Entity\Instructor;
use App\Form\InstructorType;
use App\Repository\InstructorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/instructor")
 */
class InstructorController extends AbstractController
{
    /**
     * @Route("/", name="instructor_index", methods={"GET"})
     */
    public function index(InstructorRepository $instructorRepository): Response
    {
        return $this->render('instructor/index.html.twig', [
            'instructors' => $instructorRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="instructor_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $instructor = new Instructor();
        $form = $this->createForm(InstructorType::class, $instructor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($instructor);
            $entityManager->flush();

            if ( $request->query->has('source') == 'session' ) {

                if ( $request->query->has('id') ) {
                    return $this->redirectToRoute('session_edit', [
                        'id' => $request->query->get('id'),
                        'instructor' => 'success'
                    ]);
                }

                if ( $request->query->has('current_session_number') ) {

                    return $this->redirectToRoute('session_new', [
                        'file_name' => $request->query->get('file_name'),
                        'extension' => $request->query->get('extension'),
                        'current_session_number' => $request->query->get('current_session_number'),
                        'instructor' => 'success'
                    ]);
                } else {

                    return $this->redirectToRoute('session_new', [
                        'file_name' => $request->query->get('file_name'),
                        'extension' => $request->query->get('extension'),
                        'instructor' => 'success'
                    ]);
                }
            }

            return $this->redirectToRoute('instructor_index', [
                'new' => 'success'
            ]);
        }

        return $this->render('instructor/new.html.twig', [
            'instructor' => $instructor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="instructor_show", methods={"GET"})
     */
    public function show(Instructor $instructor): Response
    {
        return $this->render('instructor/show.html.twig', [
            'instructor' => $instructor,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="instructor_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Instructor $instructor): Response
    {
        $form = $this->createForm(InstructorType::class, $instructor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('instructor_show', [
                'id' => $instructor->getId(),
                'update' => 'success'
            ]);
        }

        return $this->render('instructor/edit.html.twig', [
            'instructor' => $instructor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="instructor_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Instructor $instructor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$instructor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($instructor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('instructor_index');
    }
}
