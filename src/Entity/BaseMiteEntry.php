<?php

namespace App\Entity;

use App\Entity\Project;
use App\Entity\Service;


class BaseMiteEntry
{
    private $project;
    private $service;
    private $message;
    private $minutes;


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

    public function getMessage(): ?string
    {
        return $this->message;
    }


    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMinutes(): ?int
    {
        return $this->minutes;
    }


    public function setMinutes(int $minutes): self
    {
        $this->minutes = $minutes;

        return $this;
    }
}
