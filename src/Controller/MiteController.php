<?php
// src/Controller/events.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Google_Client;
use Google_Service_Calendar;
use Symfony\Component\HttpFoundation\Request;

use App\Service\MiteService;
use App\Service\DefaultProjectsService;
use App\Service\UserService;
use App\Service\DailyMiteEntriesService;
use App\Service\SuggestionListService;

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
                               Request $request)
    {
        return $this->renderMiteByDate($miteService, $defaultProjectsService, $userService, $dailyMiteEntriesService, $suggestionListService, $request, date("Y"), date('m'), date('d'));
    }


     /**
      * @Route("/mite/{year<\d+>}/{month<\d+>}/{day<\d+>}", name="show_mite_entries_by_date")
      */
    public function renderMiteByDate(MiteService $miteService, 
                                     DefaultProjectsService $defaultProjectsService, 
                                     UserService $userService, 
                                     DailyMiteEntriesService $dailyMiteEntriesService,
                                     SuggestionListService $suggestionListService,
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


        $events = $this->getCalendarEvents($date);
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



  function getCalendarEvents($date)
  {
     // Get the API client and construct the service object.
        $client = $this->getGoogleClient();
        $service = new Google_Service_Calendar($client);

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
          //'maxResults' => 10,
          'orderBy' => 'startTime',
          'singleEvents' => true,
          'timeMin' => date('c', strtotime($date)), //strtotime('today midnight')), //;mktime(0, 0, 0, 9, 9, 2019)),
          'timeMax' => date('c', strtotime($date . ' + 1 day')), //mktime(0, 0, 0, 9, 10, 2019)),
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        $items = [];

        foreach ($events as $event) {
            $item  = new event;
            $item->title = $event->getSummary();
            $item->StartDateTime = $event->start->dateTime;
            $item->EndDateTime = $event->end->dateTime;
            //$diff = $event->start->dateTime->diff($event->end->date);
            //$item->diffDates = $diff->format("%H:%I");
            $items[] = $item;
          }

        return $items;

        if (empty($events)) {
            echo "<br>No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($events as $event) {
              $start = $event->start->dateTime;
              if (empty($start)) {
                $start = $event->start->date;
              }
              printf("<br> %s (%s)\n", $event->getSummary(), $start);
            } 
        } 
  }


  /**
  * Returns an authorized API client.
  * @return Google_Client the authorized client object
  */
  function getGoogleClient()
  {
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
  }


}

class event {
  public $title;
  public $startDateTime; 
  public $endDateTime;
  public $diffDates;
} 