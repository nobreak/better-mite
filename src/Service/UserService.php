<?php


// src/Service/UserService.php
namespace App\Service;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Yaml\Yaml;

use App\Entity\UserEntity;


class UserService
{
	public function readUser()
    {
      $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
      $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

      $yamlUser = Yaml::parseFile($assets->getUrl('/yaml/user.yaml'), Yaml::PARSE_OBJECT_FOR_MAP);

      return $this->convertStdClassToUserEntity($yamlUser);
    }

    public function updateUser($newUser)
    {
        $this->writeUser($newUser);
    }



    public function resetUserAndAddDummy()
    {
    	$assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $object = new \stdClass();
        $object->workingHoursPerDay = 8;

        $yaml = Yaml::dump($object, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/user.yaml'), $yaml);

    }



    private function writeUser($newUser)
    {
        $assetPath = "file://".$_SERVER['DOCUMENT_ROOT'] . "assets";
        $assets = new UrlPackage($assetPath,new EmptyVersionStrategy());

        $yamlUser = $this->convertUserEntityToStdClass($newUser);


        $yaml = Yaml::dump($yamlUser, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents($assets->getUrl('/yaml/user.yaml'), $yaml);

    }

    private function convertStdClassToUserEntity($yamlUser)
    {
        $userEntity = new UserEntity();
        $userEntity->setWorkingHoursPerDay($yamlUser->workingHoursPerDay); 

        return $userEntity;
    }



    private function convertUserEntityToStdClass($userEntity)
    {
        $object = new \stdClass();
        $object->workingHoursPerDay = $userEntity->getWorkingHoursPerDay();

        return $object;
    }


    // private function convertDefaultServiceToStdClass($defaultService)
    // {
    //     $object = new \stdClass();
    //     $object->id = $defaultService->getService()->getId();
    //     $object->name = $defaultService->getService()->getName();

    //     return $object;
    // }


}