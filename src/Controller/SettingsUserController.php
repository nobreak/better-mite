<?php

// src/Controller/SettingsUserController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\HttpFoundation\Request;


use App\Form\UserFormType;
use App\Entity\UserEntity;
use App\Service\UserService;


class SettingsUserController extends AbstractController
{
	   /**
      * @Route("/mite/settings/user", name="show_user")
      */
    public function showUser(UserService $userService, Request $request )
    {
      // read yaml with default User
      $userEntity = $userService->readUser();

      $updateUserForm = $this->createForm(UserFormType::class, $userEntity);

      // check for submitted data
      $updateUserForm->handleRequest($request);
      if ($updateUserForm->isSubmitted() && $updateUserForm->isValid()) {

        $updatedUser = $updateUserForm->getData();

        $userService->updateUser($updatedUser);

        // reading out yaml file again to show the update
        $user = $userService->readUser();

      }


      return $this->render('mite/settings/user.html.twig', [
           'form' => $updateUserForm->createView(),
      ]);

    }



    /**
      * @Route("/mite/settings/user/reset")
      */
    function resetUser(UserService $userService)
    {
        $userService->resetUserAndAddDummy();
        return $this->redirectToRoute('show_user');

    }

} // class