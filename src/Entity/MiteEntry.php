<?php

namespace App\Entity;

use App\Entity\Project;
use App\Entity\Service;


class MiteEntry
{
    private $project;
    private $service;
    private $message;
    private $minutes;
    private $date;


    public function getDate(): ?string
    {
        return $this->date;
    }


    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }


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
