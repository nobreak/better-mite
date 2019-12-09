<?php


// src/Service/ApplicationGlobalsService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;



class ApplicationGlobalsService
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    // this is a temporay list which will be created when we have suggestions for day
    // it' is a list of SuggestionMiteEntry objects
    public function getSuggestionList()
    {
        return $this->session->get('suggestion_list');
    }

    public function setSuggestionList($newSuggestionList)
    {
      $this->session->set('suggestion_list', $newSuggestionList);
    }

    public function resetSuggestionList()
    {
      $this->session->remove('suggestion_list' );
    }


    // this add objects of type CalendarEventEntity to the list of SuggestionMiteEntries
    public function addCalendarEventsToSuggestionList($calendarEvents)
    {
      // get the suggestion list
      $suggestions = $this->getSuggestionList();

      // add the new SuggestionMiteEntries 
      foreach ($calendarEvents as $event) {
        array_push($suggestions, $event);
      }

      $this->setSuggestionList($suggestions);

    }
   
}