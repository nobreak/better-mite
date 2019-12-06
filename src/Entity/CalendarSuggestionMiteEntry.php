<?php



namespace App\Entity;

use App\Entity\Project;
use App\Entity\Service;
use \DateTime;

use Google_Service_Calendar_Event;



class CalendarSuggestionMiteEntry extends SuggestionMiteEntry
{
    private $startTime; // used for representation in twig
    private $endTime; // used for representation in twig
    private $duration; // used for representation in twig
    private $title; // used for representation in twig


    public function __construct(Google_Service_Calendar_Event $googleEvent)
    {
        $this->setId($googleEvent->getId());

        $startAsDateTime = new DateTime($googleEvent->start->dateTime);
        $endAsDateTime = new DateTime($googleEvent->end->dateTime);

        $this->setStartTime($startAsDateTime->format('H:i'));
        $this->setEndTime($endAsDateTime->format('H:i'));

        $this->setDate($startAsDateTime->format('Y-m-d'));

        $message = "(" . $this->getStartTime() . " to " . $this->getEndTime().") MEETING:" .$googleEvent->getSummary(); 
        $this->setMessage($message);

        $this->title = $googleEvent->getSummary();

        $diff = $startAsDateTime->diff($endAsDateTime);
        $this->setMinutes = $diff->i; 
        $this->setDuration($diff->format("%H:%I"));
    }

    public function setStartTime(string $startTime)
    {
        $this->startTime = $startTime;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setEndTime(string $endTime)
    {
        $this->endTime = $endTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }


    public function setDuration(string $duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setTItle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
