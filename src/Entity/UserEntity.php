<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity)
 */
class UserEntity
{
    /**
     * @ORM\Column(type="float")
     */
    private $workingHoursPerDay;


    public function getWorkingHoursPerDay(): ?float
    {
        return $this->workingHoursPerDay;
    }

    public function setWorkingHoursPerDay(float $hours): self
    {
        $this->workingHoursPerDay = $hours;

        return $this;
    }

}
