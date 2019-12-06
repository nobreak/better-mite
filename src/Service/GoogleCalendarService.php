<?php


// src/Service/GoogleCalendarService.php
namespace App\Service;


use Google_Service_Calendar;
use App\Entity\CalendarSuggestionMiteEntry;


class GoogleCalendarService
{

	 private $client; 
	 private $service;


 	 public function __construct(GoogleClientService $googleClientService)
     {
	     // Get the API client and construct the service object.
        $this->client = $googleClientService->getGoogleClient();
        $this->service = new Google_Service_Calendar($this->client);
     }



	function getCalendarSuggestionMiteEntries($date)
  	{
  		$service = $this->service;
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
            $item  = new CalendarSuggestionMiteEntry($event);
            $items[] = $item;
        }

        return $items;

        // if (empty($events)) {
        //     echo "<br>No upcoming events found.\n";
        // } else {
        //     print "Upcoming events:\n";
        //     foreach ($events as $event) {
        //       $start = $event->start->dateTime;
        //       if (empty($start)) {
        //         $start = $event->start->date;
        //       }
        //       printf("<br> %s (%s)\n", $event->getSummary(), $start);
        //     } 
        // } 
  	}


} // eoc