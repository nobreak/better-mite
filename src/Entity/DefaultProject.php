<?php

namespace App\Entity;

use App\Entity\Project;
use App\Entity\Service;


class DefaultProject
{
    private $project;
    private $service;


    public function getProject(): ?Project
    {
        return $this->project;
    }


    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

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
