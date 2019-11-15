<?php


// src/Service/DefaultProjectsService.php
namespace App\Service;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Yaml\Yaml;


class DailyMiteEntriesService
{
	  public function readDailyMiteEntries()
    {
      $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
      $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());


      $yamlDailyMiteEntries = Yaml::parseFile($assets->getUrl('/yaml/dailyMiteEntries.yaml'), Yaml::PARSE_OBJECT_FOR_MAP);

      usort($yamlDailyMiteEntries->dailyMiteEntries, function($a, $b)
      {
          return strcmp(strtolower($a->message), strtolower($b->message));
      });

      return $yamlDailyMiteEntries->dailyMiteEntries;
    }


    public function readDailyMiteEntriesForWeekday($weekday)
    {
        $allEntries = $this->readDailyMiteEntries();

        $filteredArray = array_filter( $allEntries,
            function ($e) use (&$weekday) {
                if (in_array($weekday, $e->weekdays)) {
                  return true;
                } else {
                  return false;
                }
            }
        );
        return $filteredArray;
    }


    public function addDailyMiteEntry($newDailyMiteEntry)
    {
        $newDailyMiteEntries = $this->readDailyMiteEntries();
        array_push($newDailyMiteEntries, $this->convertDailyMiteEntryEntityToStdClass($newDailyMiteEntry));

        // write the new data into yaml file
        $this->writeDailyMiteEntries($newDailyMiteEntries);

    }



    public function deleteDailyMiteEntry($id)
    {
    	  $newDailyMiteEntries = $this->readDailyMiteEntries();

      	// filter out object with id
      	$filteredArray = array_filter( $newDailyMiteEntries,
            function ($e) use (&$id) {
                return $e->id != $id;
            }
      	);

      	// write new array to yaml
      	$this->writeDailyMiteEntries($filteredArray);
    }


    public function resetDailyMiteEntriesAndAddDummy()
    {
    	$assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $object = new \stdClass();
        $object->id = 123456789;
        $object->message = "Delete this only if you have added another daily mite entry";
        $object->serviceId = 987654321;
        $object->serviceName = "service dummy";
        $object->projectId = 13579;
        $object->projectName = "project dummy";
        $object->minutes = 30;
        $object->weekdays = [0,1,2,3,4];



        $array = [
          'dailyMiteEntries' => [$object]
        ];
        $yaml = Yaml::dump($array, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/dailyMiteEntries.yaml'), $yaml);

    }



    private function writeDailyMiteEntries($newDailyMiteEntries)
    {
        $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $array = [
          'dailyMiteEntries' => $newDailyMiteEntries
        ];


        $yaml = Yaml::dump($array, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/dailyMiteEntries.yaml'), $yaml);

    }


    private function convertDailyMiteEntryEntityToStdClass($dailyMiteEntryEntity)
    {
        $object = new \stdClass();
        $object->id = $dailyMiteEntryEntity->getId();
        $object->message = $dailyMiteEntryEntity->getMessage();
        $object->minutes = $dailyMiteEntryEntity->getMinutes();
        $object->serviceId = $dailyMiteEntryEntity->getService()->getId();
        $object->serviceName = $dailyMiteEntryEntity->getService()->getName();
        $object->projectId = $dailyMiteEntryEntity->getProject()->getId();
        $object->projectName = $dailyMiteEntryEntity->getProject()->getName();
        $object->weekdays = $dailyMiteEntryEntity->getWeekdays();


        return $object;
    }




}