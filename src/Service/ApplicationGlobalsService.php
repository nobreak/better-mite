<?php


// src/Service/ApplicationGlobalsService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;



class ApplicationGlobalsService
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    public function getSuggestionList()
    {
        return $this->session->get('suggestion_list');
    }

    public function setSuggestionList($newSuggestionList)
    {
      $this->session->set('suggestion_list', $newSuggestionList);
    }

    public function resetSuggestionList()
    {
      $this->session->remove('suggestion_list' );
    }
   
}