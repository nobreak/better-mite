<?php


// src/Service/DefaultServicesService.php
namespace App\Service;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Yaml\Yaml;


class DefaultServicesService
{
	public function readDefaultServices()
    {
      $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
      $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());


      $yamlDefaultServices = Yaml::parseFile($assets->getUrl('/yaml/defaultServices.yaml'), Yaml::PARSE_OBJECT_FOR_MAP);

      usort($yamlDefaultServices->services, function($a, $b)
      {
          return strcmp(strtolower($a->name), strtolower($b->name));
      });

      return $yamlDefaultServices->services;
    }

    public function addDefaultService($newDefaultService)
    {
        $newDefaultServices = $this->readDefaultServices();
        array_push($newDefaultServices, $this->convertDefaultServiceToStdClass($newDefaultService));

        // write the default projects into yaml file
        $this->writeDefaultServices($newDefaultServices);

    }

    public function deleteDefaultService($id)
    {
    	$readDefaultServices = $this->readDefaultServices();

      	// filter out object with id
      	$filteredArray = array_filter( $readDefaultServices,
            function ($e) use (&$id) {
                return $e->id != $id;
            }
      	);

      	// write new array to yaml
      	$this->writeDefaultServices($this->convertArrOfDefaultServicesToStdClassArr($filteredArray));
    }


    public function resetDefaultServicesAndAddDummy()
    {
    	  $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $object = new \stdClass();
        $object->id = 123456789;
        $object->name = "Delete this only if you have added another default service";

        $array = [
          'services' => [$object]
        ];
        $yaml = Yaml::dump($array, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/defaultServices.yaml'), $yaml);

    }



    private function writeDefaultServices($newDefaultServices)
    {
        $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $array = [
          'services' => $newDefaultServices
        ];


        $yaml = Yaml::dump($array, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/defaultServices.yaml'), $yaml);

    }



    private function convertArrOfDefaultServicesToStdClassArr($defaultServices)
    {
        $newDefaultServices = array();
        
        // add new service to existing one
        foreach ($defaultServices as $key => $value) {
          $object = new \stdClass();
          $object->id = $value->id; //getId();
          $object->name = $value->name; //getName();

          array_push($newDefaultServices, $object);
        }

        return $newDefaultServices;
    }


    private function convertDefaultServiceToStdClass($defaultService)
    {
        $object = new \stdClass();
        $object->id = $defaultService->getService()->getId();
        $object->name = $defaultService->getService()->getName();

        return $object;
    }


}