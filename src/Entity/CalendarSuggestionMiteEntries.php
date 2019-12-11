<?php



use App\Entity\CalendarSuggestionMiteEntry;

namespace App\Entity;




class CalendarSuggestionMiteEntries 
{
    protected $events; // array of CalendarSuggestionMiteEntry objects - used for a form


    public function __construct()
    {
        $this->events = array();

    }


    // param array CalendarSuggestionMiteEntry objects
    public function setEvents(array $events)
    {
        $this->events = $entries;
    }

    // returns array of CalendarSuggestionMiteEntry objects
    public function getEvents()
    {
        return $this->events;
    }

    public function addEvent(CalendarSuggestionMiteEntry $event)
    {
        array_push($this->events, $event );
    }


}
