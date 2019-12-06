<?php



namespace App\Entity;

use App\Entity\Project;
use App\Entity\Service;
use \DateTime;

use Google_Service_Calendar_Event;



class CalendarEventEntity
{
    public $id;
    public $title;
    public $startDateTime; 
    public $endDateTime;
    public $duration;


    public function __construct(Google_Service_Calendar_Event $googleEvent)
    {
        $this->id = $googleEvent->getId();
        $this->title = $googleEvent->getSummary();
        $this->StartDateTime = $googleEvent->start->dateTime;
        $this->EndDateTime = $googleEvent->end->dateTime;


        //echo $googleEvent->start . " <- DATE <br>";
        echo $googleEvent->start->dateTime . "<br>";

        $startAsDateTime = new DateTime($googleEvent->start->dateTime);
        $endAsDateTime = new DateTime($googleEvent->end->dateTime);

        $diff = $startAsDateTime->diff($endAsDateTime);

        //echo "DIFF: ". $diff->format("%H:%I") ." <br>";

        $this->duration = $diff->format("%H:%I");
    }

}
