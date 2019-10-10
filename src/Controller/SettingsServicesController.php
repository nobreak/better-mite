<?php

// src/Controller/SettingsServicesController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\HttpFoundation\Request;


use App\Form\DefaultServiceFormType;
use App\Entity\DefaultService;
use App\Service\DefaultServicesService;


class SettingsServicesController extends AbstractController
{
	   /**
      * @Route("/mite/settings/services", name="show_default_services")
      */
    public function showDefaultServices(DefaultServicesService $defaultServicesService, Request $request )
    {
      // read yaml with default services
      $defaultServices = $defaultServicesService->readDefaultServices();

      // prepare form to add new default servcies      
      $defaultService = new DefaultService();

      $addDefaultServiceForm = $this->createForm(DefaultServiceFormType::class, $defaultService);

      // check for submitted data
      $addDefaultServiceForm->handleRequest($request);
      if ($addDefaultServiceForm->isSubmitted() && $addDefaultServiceForm->isValid()) {

        $newDefaultService = $addDefaultServiceForm->getData();

        $defaultServicesService->addDefaultService($newDefaultService);

        // reading out yaml file again to show the update
        $defaultServices = $defaultServicesService->readDefaultServices();

      }


      return $this->render('mite/settings/services.html.twig', [
           'defaultServices' => $defaultServices,
           'form' => $addDefaultServiceForm->createView(),
      ]);

    }


     /**
      * @Route("/mite/settings/services/delete/{id}", name="delete_default_service")
      */
    public function deleteDefaultService(DefaultServicesService $defaultServicesService, Request $request, $id)
    {
      $defaultServicesService->deleteDefaultService($id);

      // show the updated list
      return $this->redirectToRoute('show_default_services');
    }

     /**
      * @Route("/mite/settings/services/reset")
      */
    function resetDefaultServices(DefaultServicesService $defaultServicesService)
    {
        $defaultServicesService->resetDefaultServicesAndAddDummy();
        return $this->redirectToRoute('show_default_services');

    }
} // class