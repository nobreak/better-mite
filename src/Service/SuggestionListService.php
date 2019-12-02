<?php


// src/Service/SuggestionListService.php
namespace App\Service;

use App\Entity\SuggestionMiteEntry;
use App\Entity\Project;
use App\Entity\Service;
use App\Service\ApplicationGlobals;


class SuggestionListService
{
	public function createSuggestionList($dailyMiteEntries, $calendarEvents, $currentMiteEntries)
    {
        // check that dailyMiteEntries and calendarEvents are not a part of currentMiteEntries

        $result = [];

        $this->findFromDailyMiteEntries($result, $dailyMiteEntries, $currentMiteEntries);
        //$this->findFromCalendarEvents($result, $calendarEvents, $currentMiteEntries);

        return $result;
    }




    private function findFromCalendarEvents(&$result, $calendarEvents, $currentMiteEntries)
    {

        foreach ($calendarEvents as $calendarEvent) 
        {
            // if there is the same message and project ID and service id still booked, ignore the daily mite entry
            $calendarEventTitle = $calendarEvent->title;

            //echo "test for daily entry: '". $dailyMessage ."'<br>";

            $filteredArray = array_filter( $currentMiteEntries,
                function ($e) use (&$calendarEventTitle) {

                    //echo "compare: '". $dailyMessage ."' with '".$e->time_entry->note."'<br>";
                    if (strcmp( $calendarEventTitle,  $e->time_entry->note) !== 0) {
                        //echo "   NOT EQUAL<br>";
                        // not equal
                        return false;
                    } else {
                        // equal
                        //echo "   EQUAL<br>";
                        return true;
                    }
                }
            );
            //echo "   FILTERED ARR<br>";
            //var_dump($filteredArray);

            if (count($filteredArray) > 0) 
            {
                // we found the daily mite entry in current mite entries, so we dont need to suggest    
                //echo "   GOING TO NEXT<br>";
                continue;
            } 
            else 
            {
                //echo "   ADDED IT<br>";
                // yes, we could suggest this entry
                // copy the information which we need
                $newSuggestionListEntry = new SuggestionMiteEntry();
                // $newSuggestionListEntry->setService(new Service($dailyMiteEntry->serviceId, $dailyMiteEntry->serviceName));
                // $newSuggestionListEntry->setProject(new Project($dailyMiteEntry->projectId, $dailyMiteEntry->projectName));
                $newSuggestionListEntry->setMessage($calendarEvent->title);
                $newSuggestionListEntry->setMinutes(10);
                array_push($result, $newSuggestionListEntry);
            }
        }
    }



    private function findFromDailyMiteEntries(&$result, $dailyMiteEntries, $currentMiteEntries)
    {

        foreach ($dailyMiteEntries as $dailyMiteEntry) 
        {
            // if there is the same message and project ID and service id still booked, ignore the daily mite entry
            $dailyMessage = $dailyMiteEntry->message;

            //echo "test for daily entry: '". $dailyMessage ."'<br>";

            $filteredArray = array_filter( $currentMiteEntries,
                function ($e) use (&$dailyMessage) {

                    //echo "compare: '". $dailyMessage ."' with '".$e->time_entry->note."'<br>";
                    if (strcmp( $dailyMessage,  $e->time_entry->note) !== 0) {
                        //echo "   NOT EQUAL<br>";
                        // not equal
                        return false;
                    } else {
                        // equal
                        //echo "   EQUAL<br>";
                        return true;
                    }
                }
            );
            //echo "   FILTERED ARR<br>";
            //var_dump($filteredArray);

            if (count($filteredArray) > 0) 
            {
                // we found the daily mite entry in current mite entries, so we dont need to suggest    
                //echo "   GOING TO NEXT<br>";
                continue;
            } 
            else 
            {
                //echo "   ADDED IT<br>";
                // yes, we could suggest this entry
                // copy the information which we need
                $newSuggestionListEntry = new SuggestionMiteEntry();
                $newSuggestionListEntry->setService(new Service($dailyMiteEntry->serviceId, $dailyMiteEntry->serviceName));
                $newSuggestionListEntry->setProject(new Project($dailyMiteEntry->projectId, $dailyMiteEntry->projectName));
                $newSuggestionListEntry->setMessage($dailyMiteEntry->message);
                $newSuggestionListEntry->setMinutes($dailyMiteEntry->minutes);
                array_push($result, $newSuggestionListEntry);
            }
        }
    }

}