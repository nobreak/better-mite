<?php

namespace App\Entity;

use App\Entity\Project;
use App\Entity\Service;
use App\Entity\BaseMiteEntry;


class MiteEntry extends BaseMiteEntry
{
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

}
