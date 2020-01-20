<?php

namespace App\Controller;

use App\Entity\TrainingCategory;
use App\Form\TrainingCategoryType;
use App\Repository\TrainingCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/training/category")
 */
class TrainingCategoryController extends AbstractController
{
    /**
     * @Route("/", name="training_category_index", methods={"GET"})
     */
    public function index(TrainingCategoryRepository $trainingCategoryRepository): Response
    {
        return $this->render('training_category/index.html.twig', [
            'training_categories' => $trainingCategoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="training_category_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $trainingCategory = new TrainingCategory();
        $form = $this->createForm(TrainingCategoryType::class, $trainingCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trainingCategory);
            $entityManager->flush();

            return $this->redirectToRoute('training_category_index');
        }

        return $this->render('training_category/new.html.twig', [
            'training_category' => $trainingCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="training_category_show", methods={"GET"})
     */
    public function show(TrainingCategory $trainingCategory): Response
    {
        return $this->render('training_category/show.html.twig', [
            'training_category' => $trainingCategory,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="training_category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, TrainingCategory $trainingCategory): Response
    {
        $form = $this->createForm(TrainingCategoryType::class, $trainingCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('training_category_index');
        }

        return $this->render('training_category/edit.html.twig', [
            'training_category' => $trainingCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="training_category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TrainingCategory $trainingCategory): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trainingCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trainingCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('training_category_index');
    }
}
