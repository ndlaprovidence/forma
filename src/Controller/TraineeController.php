<?php

namespace App\Controller;

use App\Entity\Trainee;
use App\Form\TraineeType;
use App\Repository\TraineeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/trainee")
 */
class TraineeController extends AbstractController
{
    /**
     * @Route("/", name="trainee_index", methods={"GET"})
     */
    public function index(TraineeRepository $traineeRepository): Response
    {
        return $this->render('trainee/index.html.twig', [
            'trainees' => $traineeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="trainee_show", methods={"GET"})
     */
    public function show(Trainee $trainee): Response
    {
        return $this->render('trainee/show.html.twig', [
            'trainee' => $trainee,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="trainee_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Trainee $trainee): Response
    {
        $form = $this->createForm(TraineeType::class, $trainee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('trainee_show', [
                'id' => $trainee->getId(),
                'update' => 'success'
            ]);
        }

        return $this->render('trainee/edit.html.twig', [
            'trainee' => $trainee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="trainee_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Trainee $trainee): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trainee->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trainee);
            $entityManager->flush();
        }

        return $this->redirectToRoute('trainee_index');
    }
}
