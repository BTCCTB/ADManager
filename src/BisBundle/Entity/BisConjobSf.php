<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BisConjobSf
 *
 * @package BisBundle\Entity
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="bis_conjob_sf")
 */
class BisConjobSf
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="fk_job_id")
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisJobSf")
     */
    private $fkJobId;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="fk_con_id")
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisContractSf")
     */
    private $fkConId;

    /**
     * @ORM\Column(type="date", name="conjob_entry_date", nullable=true)
     */
    private $conjobEntryDate;

    public function getFkJobId()
    {
        return $this->fkJobId;
    }

    public function setFkJobId($fkJobId)
    {
        $this->fkJobId = $fkJobId;
        return $this;
    }

    public function getFkConId()
    {
        return $this->fkConId;
    }

    public function setFkConId($fkConId)
    {
        $this->fkConId = $fkConId;
        return $this;
    }

    public function getConjobEntryDate()
    {
        return $this->conjobEntryDate;
    }

    public function setConjobEntryDate($conjobEntryDate)
    {
        $this->conjobEntryDate = $conjobEntryDate;
        return $this;
    }

}
