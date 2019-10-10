<?php

namespace App\Entity;


use App\Entity\Service;


class DefaultService
{
    private $service;

    public function getService(): ?Service
    {
        return $this->service;
    }


    public function setService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }



}
