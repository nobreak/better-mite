<?php
// src/Controller/SettingsDailyMiteEntries.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\HttpFoundation\Request;


use App\Form\DailyMiteEntryFormType;
use App\Entity\DailyMiteEntryEntity;
use App\Service\DailyMiteEntriesService;


class SettingsDailyMiteEntriesController extends AbstractController
{
	   /**
      * @Route("/mite/settings/dailyMiteEntries", name="show_daily_mite_entries")
      */
    public function showDailyMiteEntries(DailyMiteEntriesService $dailyMiteEntriesService, Request $request )
    {
      // read yaml with default projects
      $dailyMiteEntries = $dailyMiteEntriesService->readDailyMiteEntries();

      // prepare form to add new default projects      
      $dailyMiteEntry = new DailyMiteEntryEntity();

      $addDailyMiteEntryForm = $this->createForm(DailyMiteEntryFormType::class, $dailyMiteEntry);

      // check for submitted data
      $addDailyMiteEntryForm->handleRequest($request);
      if ($addDailyMiteEntryForm->isSubmitted() && $addDailyMiteEntryForm->isValid()) {

        $newDailyMiteEntry = $addDailyMiteEntryForm->getData();

        $dailyMiteEntriesService->addDailyMiteEntry($newDailyMiteEntry);

        // reading out yaml file again to show the update
        $dailyMiteEntries = $dailyMiteEntriesService->readDailyMiteEntries();

      }


      return $this->render('mite/settings/dailyMiteEntryies.html.twig', [
           'dailyMiteEntries' => $dailyMiteEntries,
           'form' => $addDailyMiteEntryForm->createView(),
      ]);

    }


     /**
      * @Route("/mite/settings/dailyMiteEntries/delte/{id}", name="delete_daily_mite_entry")
      */
    public function deleteDailyMiteEnitry(DailyMiteEntriesService $dailyMiteEntriesService, Request $request, $id)
    {
      $dailyMiteEntriesService->deleteDailyMiteEntry($id);

      // show the updated list
      return $this->redirectToRoute('show_daily_mite_entries');
    }

     /**
      * @Route("/mite/settings/dailyMiteEntries/reset")
      */
    function resetDefaultProjects(DailyMiteEntriesService $dailyMiteEntriesService)
    {
        $dailyMiteEntriesService->resetDailyMiteEntriesAndAddDummy();
        return $this->redirectToRoute('show_daily_mite_entries');

    }


} // class
