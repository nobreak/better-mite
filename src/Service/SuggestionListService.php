<?php


// src/Service/SuggestionListService.php
namespace App\Service;

use App\Entity\SuggestionMiteEntry;
use App\Entity\Project;
use App\Entity\Service;



class SuggestionListService
{
	public function createSuggestionList($dailyMiteEntries, $calendarEvents, $currentMiteEntries)
    {
        // check that dailyMiteEntries and calendarEvents are not a part of currentMiteEntries

        $result = [];

        foreach ($dailyMiteEntries as $dailyMiteEntry) {
            // copy the information which we need

            $newSuggestionListEntry = new SuggestionMiteEntry();
            $newSuggestionListEntry->setService(new Service($dailyMiteEntry->serviceId, $dailyMiteEntry->serviceName));
            $newSuggestionListEntry->setProject(new Project($dailyMiteEntry->projectId, $dailyMiteEntry->projectName));
            $newSuggestionListEntry->setMessage($dailyMiteEntry->message);
            $newSuggestionListEntry->setMinutes($dailyMiteEntry->minutes);
            array_push($result, $newSuggestionListEntry);

        }

        // save result array to global vars
        $GLOBALS["suggestionList"] = $result;

        return $result;
    }

}