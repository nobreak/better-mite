<?php
// src/Controller/events.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Service\MiteService;
use App\Service\DefaultProjectsService;
use App\Service\UserService;
use App\Service\DailyMiteEntriesService;
use App\Service\SuggestionListService;
use App\Service\ApplicationGlobalsService;
use App\Service\GoogleCalendarService;


use App\Entity\MiteEntry;
use App\Form\AddMiteEntryFormType;

require __DIR__ . '/../../vendor/autoload.php';

 date_default_timezone_set('Europe/Berlin');


class MiteController extends AbstractController
{
	   /**
      * @Route("/mite")
      */
    public function renderMite(MiteService $miteService, 
                               DefaultProjectsService $defaultProjectsService, 
                               UserService $userService, 
                               DailyMiteEntriesService $dailyMiteEntriesService,
                               SuggestionListService $suggestionListService,
                               ApplicationGlobalsService $appGlobalsService, 
                               GoogleCalendarService $calendarService,
                               Request $request)
    {
        return $this->renderMiteByDate($miteService, 
                                       $defaultProjectsService, 
                                       $userService, 
                                       $dailyMiteEntriesService, 
                                       $suggestionListService, 
                                       $appGlobalsService, 
                                       $calendarService,
                                       $request, 
                                       date("Y"), date('m'), date('d'));
    }


     /**
      * @Route("/mite/{year<\d+>}/{month<\d+>}/{day<\d+>}", name="show_mite_entries_by_date")
      */
    public function renderMiteByDate(MiteService $miteService, 
                                     DefaultProjectsService $defaultProjectsService, 
                                     UserService $userService, 
                                     DailyMiteEntriesService $dailyMiteEntriesService,
                                     SuggestionListService $suggestionListService,
                                     ApplicationGlobalsService $appGlobalsService,
                                     GoogleCalendarService $calendarService,
                                     Request $request, $year, $month, $day)
    {
        $date = date('Y-m-d', mktime(0,0,0,$month, $day, $year));
        $weekday = date("w", mktime(0,0,0,$month, $day, $year));    

        $addMiteEntry = new MiteEntry();
        $addMiteEntry->setDate($date);

        $addMiteEntryForm = $this->createForm(AddMiteEntryFormType::class, $addMiteEntry);


        $addMiteEntryForm->handleRequest($request);
        if ($addMiteEntryForm->isSubmitted() && $addMiteEntryForm->isValid()) {
            $newMiteEntry = $addMiteEntryForm->getData();

            $miteService->addMiteEntry($newMiteEntry);
        }


        $events = $calendarService->getCalendarSuggestionMiteEntries($date);
        $miteEntries = $miteService->getMiteEntries($year, $month, $day);
        $miteProjects = $miteService->getMiteProjects();
        $miteServices = $miteService->getMiteServices();


        // calculate missing time
        $countMinutes = 0;
        foreach ($miteEntries as $key => $entry) {
          $countMinutes += $entry->time_entry->minutes;
        }

        $userEntity = $userService->readUser();
        
        $maxMinutes = $userEntity->GetWorkingHoursPerDay()*60;
        $missingMinutes = $maxMinutes - $countMinutes;
        if ($missingMinutes < 0)
          $missingMinutes = 0;

        $currentPercent = ($countMinutes / $maxMinutes) * 100;
        $hours    = (int)($missingMinutes / 60);
        $minutes  = $missingMinutes - ($hours * 60);   

        $missingTime = new \DateTime($hours.":".$minutes);
        

        // provide JS array with mappings for projects and assigned default servers
        // also inject the onChange event to select the right service
        $js = "   var serviceMapping = [\n";

        $defaultProjects = $defaultProjectsService->readDefaultProjects();
        foreach ($defaultProjects as $key => $value) {
            $js = $js . "      { projectID : " .$value->id. ", serviceID : " .$value->serviceId. " },\n";
        }
        $js = $js . "   ];\n";
        $js = $js . "

            $('#add_mite_entry_form_project').change(function() {
              
              var projectID = $(this).val()

              // find service id by mapping array
              var assignedServiceID = $.grep(serviceMapping, function(e){ return e.projectID == projectID; })[0].serviceID;

              // set selectd value
              $('#add_mite_entry_form_service').val(assignedServiceID);

              // rerender combobox
              $('#add_mite_entry_form_service').trigger('change');

            })";


        // build suggestion list for today
        
        $dailyMiteEntries = $dailyMiteEntriesService->readDailyMiteEntriesForWeekday($weekday);            
        $suggestionList = $suggestionListService->createSuggestionList($dailyMiteEntries, $events, $miteEntries);

        // we need to save all suggestion for later use when the form comes back to get the object
        $appGlobalsService->setSuggestionList($suggestionList);

        return $this->render('mite/mite.html.twig', [
            'events' => $events,
            'date' => $date,
            'miteEntries' => $miteEntries,
            'countMinutes' => $countMinutes,
            'missingMinutes' => $missingMinutes,
            'missingMinutesStr' => $missingTime->format('H:i')." hours",
            'maxMinutes' => $maxMinutes,
            'currentPercent' => $currentPercent,
            'miteProjects' => $miteProjects,
            'miteServices' => $miteServices,
            'addMiteEntryForm' => $addMiteEntryForm->createView(),
            'serviceMapping' => $js,
            'suggestionList' => $suggestionList,
        ]);

    }


     /**
      * @Route("/mite/delete/{id}/{date}", name="delete_mite_entry")
      */
    public function deleteMiteEntry(MiteService $miteService, Request $request, $id, $date)
    {
      $miteService->deleteMiteEntry($id);

      $year = date('Y', strtotime($date));
      $month = date('m', strtotime($date));
      $day = date('d', strtotime($date));
      // show the updated list
      $parameters = ['miteService' => $miteService, 'year' => $year, 'month' => $month, 'day' => $day];

      return $this->redirectToRoute('show_mite_entries_by_date', $parameters);
    }


     /**
      * @Route("/mite/addSuggestions/{date}", name="add_suggestions")
      */
    public function addSuggestions(MiteService $miteService, ApplicationGlobalsService $appGlobalsService, Request $request, $date)
    {
      // all temp saved suggestions
      $allSuggestions = $appGlobalsService->getSuggestionList();

      // find selected suggestions from form in this
      foreach ($request->request as $key => $value) {
        $item;
        foreach ($allSuggestions as $suggestion)
        {
            if ($suggestion->getId() == $value) {
              $item = $suggestion;
              break;
            }
        }

        // add it to mite        
        $item->setDate($date);
        $miteService->addMiteEntry($item);
      }

      $appGlobalsService->resetSuggestionList();

      $year = date('Y', strtotime($date));
      $month = date('m', strtotime($date));
      $day = date('d', strtotime($date));
      // show the updated list
      $parameters = ['miteService' => $miteService, 'year' => $year, 'month' => $month, 'day' => $day];

      return $this->redirectToRoute('show_mite_entries_by_date', $parameters);
    }


    /**
      * @Route("/mite/addCalendarEventsToSuggestions/{date}", name="add_calendar_events_to_suggestions")
      */
    public function addCalendarEventsToSuggestions(ApplicationGlobalsService $appGlobalsService, Request $request, $date)
    {
      // all temp saved suggestions
      $allSuggestions = $appGlobalsService->getSuggestionList();

      // find selected suggestions from form in this
      foreach ($request->request as $key => $value) {
        $item;
        foreach ($allSuggestions as $suggestion)
        {
            if ($suggestion->getId() == $value) {
              $item = $suggestion;
              break;
            }
        }

        // add it to mite        
        $item->setDate($date);
        $miteService->addMiteEntry($item);
      }

      $appGlobalsService->resetSuggestionList();

      $year = date('Y', strtotime($date));
      $month = date('m', strtotime($date));
      $day = date('d', strtotime($date));
      // show the updated list
      $parameters = ['miteService' => $miteService, 'year' => $year, 'month' => $month, 'day' => $day];

      return $this->redirectToRoute('show_mite_entries_by_date', $parameters);
    }



}

