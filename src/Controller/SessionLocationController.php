<?php

namespace App\Controller;

use App\Entity\SessionLocation;
use App\Form\SessionLocationType;
use App\Repository\SessionLocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/location")
 */
class SessionLocationController extends AbstractController
{
    /**
     * @Route("/", name="location_index", methods={"GET"})
     */
    public function index(SessionLocationRepository $sessionLocationRepository): Response
    {
        return $this->render('location/index.html.twig', [
            'locations' => $sessionLocationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="location_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $sessionLocation = new SessionLocation();
        $form = $this->createForm(SessionLocationType::class, $sessionLocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sessionLocation);
            $entityManager->flush();

            return $this->redirectToRoute('location_index');
        }

        return $this->render('location/new.html.twig', [
            'location' => $sessionLocation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="location_show", methods={"GET"})
     */
    public function show(SessionLocation $sessionLocation): Response
    {
        return $this->render('location/show.html.twig', [
            'location' => $sessionLocation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="location_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, SessionLocation $sessionLocation): Response
    {
        $form = $this->createForm(SessionLocationType::class, $sessionLocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('location_index');
        }

        return $this->render('location/edit.html.twig', [
            'location' => $sessionLocation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="location_delete", methods={"DELETE"})
     */
    public function delete(Request $request, SessionLocation $sessionLocation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sessionLocation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sessionLocation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('location_index');
    }
}
