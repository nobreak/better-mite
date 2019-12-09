<?php


// src/Service/DefaultProjectsService.php
namespace App\Service;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Yaml\Yaml;
use App\Entity\Project;


class DefaultProjectsService
{

    // read the yaml file and return all configured default projects as array of StdClass
	  public function readDefaultProjects()
    {
      $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
      $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());


      $yamlDefaultProjects = Yaml::parseFile($assets->getUrl('/yaml/defaultProjects.yaml'), Yaml::PARSE_OBJECT_FOR_MAP);

      usort($yamlDefaultProjects->projects, function($a, $b)
      {
          return strcmp(strtolower($a->name), strtolower($b->name));
      });

      return $yamlDefaultProjects->projects;
    }


    // read the yaml file and return all configured default projects as array of Project::class
    public function readDefaultProjectObjects()
    {
      $miteProjStdClass = $this->readDefaultProjects();

      $projects = array();
      foreach ($miteProjStdClass as $key => $value) {
            $project = new Project($value->id, $value->name );
            array_push($projects, $project);
      }

      return $projects; 
    }



    public function AddDefaultProject($newDefaultProject)
    {
        $newDefaultProjects = $this->readDefaultProjects();
        array_push($newDefaultProjects, $this->convertDefaultProjectToStdClass($newDefaultProject));

        // write the default projects into yaml file
        $this->writeDefaultProjects($newDefaultProjects);

    }

    public function deleteDefaultProject($id)
    {
    	$readDefaultProjects = $this->readDefaultProjects();

      	// filter out object with id
      	$filteredArray = array_filter( $readDefaultProjects,
            function ($e) use (&$id) {
                return $e->id != $id;
            }
      	);

      	// write new array to yaml
      	$this->writeDefaultProjects($this->convertArrOfDefaultProjectsToStdClassArr($filteredArray));
    }


    public function resetDefaultProjectsAndAddDummy()
    {
    	$assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $object = new \stdClass();
        $object->id = 123456789;
        $object->name = "Delete this only";
        $object->serviceId = 987654321;
        $object->serviceName = "if you have added another default project";


        $array = [
          'projects' => [$object]
        ];
        $yaml = Yaml::dump($array, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/defaultProjects.yaml'), $yaml);

    }



    private function writeDefaultProjects($newDefaultProjects)
    {
        $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $array = [
          'projects' => $newDefaultProjects
        ];


        $yaml = Yaml::dump($array, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/defaultProjects.yaml'), $yaml);

    }



    private function convertArrOfDefaultProjectsToStdClassArr($defaultProjects)
    {
        $newDefaultProjects = array();
        
        // add new project to existing one
        foreach ($defaultProjects as $key => $value) {
          $object = new \stdClass();
          $object->id = $value->id; //getId();
          $object->name = $value->name; //getName();
          $object->serviceId = $value->serviceId;
          $object->serviceName = $value->serviceName;

          array_push($newDefaultProjects, $object);
        }

        return $newDefaultProjects;
    }


    private function convertDefaultProjectToStdClass($defaultProject)
    {
        $object = new \stdClass();
        $object->id = $defaultProject->getProject()->getId();
        $object->name = $defaultProject->getProject()->getName();
        $object->serviceId = $defaultProject->getService()->getId();
        $object->serviceName = $defaultProject->getService()->getName();


        return $object;
    }




}