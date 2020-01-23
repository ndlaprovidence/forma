<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @Route("/", name="default")
     */
    public function index(Request $request)
    {
        $this->logger->info('Une info.');

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $originalFile = $upload->getFileName();
            $fileName = time().'-'.rand(10000,99999).'.'.$originalFile->getClientOriginalExtension();
            //$file->getClientOriginalName()
            $originalFile->move($this->getParameter('upload_directory'), $fileName);
            $upload->setFileName($fileName);
    
            return $this->redirectToRoute('session_new', [
                'file_name' => $fileName,
            ]);
        }


        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'form' => $form->createView(),
            ]);
    }
}
