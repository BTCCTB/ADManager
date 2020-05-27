<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BisConjobSf
 *
 * @package BisBundle\Entity
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @ORM\Entity
 * @ORM\Table(name="bis_conjob_sf")
 */
class BisConjobSf
{
    /**
     * @ORM\Id
     * @ORM\JoinColumn(name="fk_job_id", referencedColumnName="job_id")
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisJobSf", inversedBy="conjobs")
     * @var BisJobSf
     */
    private $fkJobId;

    /**
     * @ORM\Id
     * @ORM\joinColumn(name="fk_con_id", referencedColumnName="con_id")
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisContractSf", inversedBy="conjobs")
     * @var BisContractSf
     */
    private $fkConId;

    /**
     * @ORM\Column(type="date", name="conjob_entry_date", nullable=true)
     */
    private $conjobEntryDate;

    /**
     * @ORM\Column(type="boolean", name="conjob_active", nullable=true)
     */
    private $conjobActive;

    /**
     * @ORM\Column(type="datetime", name="conjob_last_updated")
     */
    private $conjobLastUpdated;

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

    public function getConjobActive()
    {
        return $this->conjobActive;
    }

    public function setConjobActive($conjobActive)
    {
        $this->conjobActive = $conjobActive;

        return $this;
    }

    public function getConjobLastUpdated()
    {
        return $this->conjobLastUpdated;
    }

    public function setConjobLastUpdated($conjobLastUpdated)
    {
        $this->conjobLastUpdated = $conjobLastUpdated;

        return $this;
    }
}
