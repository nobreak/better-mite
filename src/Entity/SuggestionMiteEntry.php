<?php

namespace App\Entity;

use App\Entity\Project;
use App\Entity\Service;
use App\Entity\MiteEntry;


class SuggestionMiteEntry extends MiteEntry
{
    private $id;


    public function __construct()
    {
        $this->id = uniqid();
    }


    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

}
