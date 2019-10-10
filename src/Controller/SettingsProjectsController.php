<?php
// src/Controller/SettingsProjectsController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\HttpFoundation\Request;


use App\Form\DefaultProjectFormType;
use App\Entity\DefaultProject;
use App\Service\DefaultProjectsService;


class SettingsProjectsController extends AbstractController
{
	   /**
      * @Route("/mite/settings/projects", name="show_default_projects")
      */
    public function showDefaultProjects(DefaultProjectsService $defaultProjectsService, Request $request )
    {
      // read yaml with default projects
      $defaultProjects = $defaultProjectsService->readDefaultProjects();

      // prepare form to add new default projects      
      $defaultProject = new DefaultProject();
      //$defaultProject->setId(123);
      //$defaultProject->setName("a Name");
      //$defaultProject->setServiceId(987);
      //$defaultProject->setServiceName("a Service");

      $addDefaultProjectForm = $this->createForm(DefaultProjectFormType::class, $defaultProject);

      // check for submitted data
      $addDefaultProjectForm->handleRequest($request);
      if ($addDefaultProjectForm->isSubmitted() && $addDefaultProjectForm->isValid()) {

        $newDefaultProject = $addDefaultProjectForm->getData();

        $defaultProjectsService->AddDefaultProject($newDefaultProject);

        // reading out yaml file again to show the update
        $defaultProjects = $defaultProjectsService->readDefaultProjects();

      }


      return $this->render('mite/settings/projects.html.twig', [
           'defaultProjects' => $defaultProjects,
           'form' => $addDefaultProjectForm->createView(),
      ]);

    }


     /**
      * @Route("/mite/settings/projects/delte/{id}", name="delete_default_project")
      */
    public function deleteDefaultProject(DefaultProjectsService $defaultProjectsService, Request $request, $id)
    {
      $defaultProjectsService->deleteDefaultProject($id);

      // show the updated list
      return $this->redirectToRoute('show_default_projects');
    }

     /**
      * @Route("/mite/settings/projects/reset")
      */
    function resetDefaultProjects(DefaultProjectsService $defaultProjectsService)
    {
        $defaultProjectsService->resetDefaultProjectsAndAddDummy();
        return $this->redirectToRoute('show_default_projects');

    }


} // class
