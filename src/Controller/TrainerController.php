<?php

namespace App\Controller;

use App\Entity\Trainer;
use App\Form\TrainerType;
use App\Repository\TrainerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/trainer")
 */
class TrainerController extends AbstractController
{
    /**
     * @Route("/", name="trainer_index", methods={"GET"})
     */
    public function index(TrainerRepository $trainerRepository): Response
    {
        return $this->render('trainer/index.html.twig', [
            'trainers' => $trainerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="trainer_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $trainer = new Trainer();
        $form = $this->createForm(TrainerType::class, $trainer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trainer);
            $entityManager->flush();

            return $this->redirectToRoute('trainer_index');
        }

        return $this->render('trainer/new.html.twig', [
            'trainer' => $trainer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="trainer_show", methods={"GET"})
     */
    public function show(Trainer $trainer): Response
    {
        return $this->render('trainer/show.html.twig', [
            'trainer' => $trainer,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="trainer_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Trainer $trainer): Response
    {
        $form = $this->createForm(TrainerType::class, $trainer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('trainer_index');
        }

        return $this->render('trainer/edit.html.twig', [
            'trainer' => $trainer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="trainer_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Trainer $trainer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trainer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trainer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('trainer_index');
    }
}
