<?php



use App\Entity\CalendarMiteEntry;

namespace App\Entity;




class CalendarMiteEntries 
{
    /**
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must select at least one calendar event",
     * )
     */
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

    public function addEvent(CalendarMiteEntry $event)
    {
        array_push($this->events, $event );
    }


}
