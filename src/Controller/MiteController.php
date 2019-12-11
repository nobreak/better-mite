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
use App\Entity\Project;
use App\Entity\CalendarSuggestionMiteEntries;
use App\Form\AddMiteEntryFormType;
use App\Form\CalendarEventsFormType;

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

        /** DO WE HAVE TO HANDLE AT FIRST A FORM REQUEST ? **/
        $addMiteEntry = new MiteEntry();
        $addMiteEntry->setDate($date);

        $addMiteEntryForm = $this->createForm(AddMiteEntryFormType::class, $addMiteEntry);
        $addMiteEntryForm->handleRequest($request);
        if ($addMiteEntryForm->isSubmitted() && $addMiteEntryForm->isValid()) {
            $newMiteEntry = $addMiteEntryForm->getData();

            $miteService->addMiteEntry($newMiteEntry);
        }


        /** GETTING DATA FROM MITE **/
        // get all mite entries for this day
        $miteEntries = $miteService->getMiteEntries($year, $month, $day);
        // get ALL projects for some comboboxes from mite
        $miteProjetcs = $miteService->getMiteProjects();
        // get ALL services for some comboboxes from mite
        $miteServices = $miteService->getMiteServices();


        /** GETTING DATA FROM CALENDAR AND CREAT FORM FOR IT**/
        // get all calendar events for this day
        $events = $calendarService->getCalendarMiteEntries($date);
        $calendarEventsForm = $this->createForm(CalendarEventsFormType::class, $events, [
            'action' => $this->generateUrl('book_calendar_events', [
              'date' => $date
            ])
        ]); 


        /** GETTING DATA FROM USER CONFIG **/
        // get user defined projects for some comoboxes from user configuration
        $miteDefaultProjects = $defaultProjectsService->readDefaultProjectObjects(); 
        $userEntity = $userService->readUser();


        // calculate missing time for mite entries on this day
        $missingTime = $this->calculateMissingTimeMiteEntries($miteEntries, $userEntity);



        

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
            'date' => $date,
            'miteEntries' => $miteEntries,
            'countMinutes' => $missingTime->currentMinutes,
            'missingMinutes' => $missingTime->minutes,
            'missingMinutesStr' => $missingTime->dateTime->format('H:i')." hours",
            'maxMinutes' => $missingTime->maxMinutes,
            'currentPercent' => $missingTime->inPercent,
            'miteProjects' => $miteProjetcs,
            'miteDefaultProjects' => $miteDefaultProjects,
            'miteServices' => $miteServices,
            'addMiteEntryForm' => $addMiteEntryForm->createView(),
            'calendarEventsForm' => $calendarEventsForm->createView(),
            'serviceMapping' => $js,
            'suggestionList' => $suggestionList,
        ]);

    }


    /**
     * @Route("/mite/book_calendar_events/{date}", name="book_calendar_events")  
     */
    public function bookCalendarEvents(MiteService $miteService, Request $request, $date)
    {
      echo "FUUUUUUNNZT";

      // // get the CalendarSuggestionMiteEntry from array from request 
      // foreach ($request->request as $key => $value) {
      //   $item;

      //   // add it to mite        
      //   $item->setDate($date);
      //   $miteService->addMiteEntry($item);
      // }


      return $this->redirectToMiteEntries($date, $miteService);
    }


     /**
      * @Route("/mite/delete/{id}/{date}", name="delete_mite_entry")
      */
    public function deleteMiteEntry(MiteService $miteService, Request $request, $id, $date)
    {
      $miteService->deleteMiteEntry($id);

      return $this->redirectToMiteEntries($date, $miteService);
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

      return $this->redirectToMiteEntries($date, $miteService);
    }


    /**
      * @Route("/mite/addCalendarEventsToSuggestions/{date}", name="add_calendar_events_to_suggestions")
      */
    public function addCalendarEventsToSuggestions(MiteService $miteService, ApplicationGlobalsService $appGlobalsService, Request $request, $date)
    {
      // get t


      $calendarEvents = array();
      // find selected suggestions from form in this
      foreach ($request->request as $key => $value) 
      {
          // create Calender suggestion Mite entriy

          // add product and service

      }

      // all temp saved suggestions
      $appGlobalsService->addCalendarEventsToSuggestionList($calendarEvents);

      return $this->redirectToMiteEntries($date, $miteService);
     
    }


    // private function

    private function redirectToMiteEntries($date, $miteService) 
    {
      $year = date('Y', strtotime($date));
      $month = date('m', strtotime($date));
      $day = date('d', strtotime($date));
      // show the updated list
      $parameters = ['miteService' => $miteService, 'year' => $year, 'month' => $month, 'day' => $day];

      return $this->redirectToRoute('show_mite_entries_by_date', $parameters);
    }


    private function calculateMissingTimeMiteEntries($miteEntries, $userEntity)
    {
        // get current booked time
        $currentBookedMinutes = 0;
        foreach ($miteEntries as $key => $entry) {
          $currentBookedMinutes += $entry->time_entry->minutes;
        }

        // get max time for this user
        $maxMinutes = $userEntity->GetWorkingHoursPerDay()*60;

        return new MissingTime($maxMinutes, $currentBookedMinutes);
    }

}

class MissingTime 
{
    public $dateTime; // missing time as DateTime obj
    public $inPercent; // missing time in percent
    public $minutes; // missing time in minutes
    public $currentMinutes;
    public $maxMinutes;

    public function __construct($maxMinutes, $currentMinutes)
    {
        $this->maxMinutes = $maxMinutes;
        $this->currentMinutes = $currentMinutes;

        $this->minutes = $this->maxMinutes - $this->currentMinutes;
        if ($this->minutes < 0)
          $this->minutes = 0;

        $this->inPercent = ($currentMinutes / $maxMinutes) * 100;

        $hours    = (int)($this->minutes / 60);
        $restMinutes  = $this->minutes - ($hours * 60);   

        $this->dateTime = new \DateTime($hours.":".$restMinutes);
    }
}

