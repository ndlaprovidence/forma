<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Form\GoalType;
use App\Repository\GoalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/goal")
 */
class GoalController extends AbstractController
{
    /**
     * @Route("/", name="goal_index", methods={"GET"})
     */
    public function index(GoalRepository $goalRepository): Response
    {
        return $this->render('goal/index.html.twig', [
            'goals' => $goalRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="goal_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $goal = new Goal();
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($goal);
            $entityManager->flush();

            return $this->redirectToRoute('goal_index');
        }

        return $this->render('goal/new.html.twig', [
            'goal' => $goal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="goal_show", methods={"GET"})
     */
    public function show(Goal $goal): Response
    {
        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="goal_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Goal $goal): Response
    {
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('goal_index');
        }

        return $this->render('goal/edit.html.twig', [
            'goal' => $goal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="goal_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Goal $goal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$goal->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($goal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('goal_index');
    }
}
