<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class BisContractSfLast
 *
 * @ORM\Entity()
 * @ORM\Table(name="view_bis_contract_sf_last")
 *
 * @UniqueEntity(fields={"con_id"})
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class BisContractSfLast
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="con_id")
     */
    private $conId;

    /**
     * @ORM\OneToOne(targetEntity="BisBundle\Entity\BisPersonView", inversedBy="lastestContract")
     * @ORM\JoinColumn(name="con_per_id", referencedColumnName="per_id", nullable=true)
     * @var int
     */
    private $conPerId;

    /**
     * @ORM\Column(type="string", name="con_type", length=30, nullable=true)
     * @ORM\Id
     * @var string
     */
    private $conType;

    /**
     * @ORM\Column(type="date", name="con_date_start")
     */
    private $conDateStart;

    /**
     * @ORM\Column(type="date", name="con_date_stop", nullable=true)
     */
    private $conDateStop;

    /**
     * @ORM\Column(type="boolean", name="con_active")
     */
    private $conActive;

    /**
     * @ORM\Column(type="datetime", name="con_last_updated")
     */
    private $conLastUpdated;

    public function getConId()
    {
        return $this->conId;
    }

    public function setConId($conId)
    {
        $this->conId = $conId;

        return $this;
    }

    public function getConType()
    {
        return $this->conType;
    }

    public function setConType($conType)
    {
        $this->conType = $conType;

        return $this;
    }

    public function getConDateStart()
    {
        return $this->conDateStart;
    }

    public function setConDateStart($conDateStart)
    {
        $this->conDateStart = $conDateStart;

        return $this;
    }

    public function getConDateStop()
    {
        return $this->conDateStop;
    }

    public function setConDateStop($conDateStop)
    {
        $this->conDateStop = $conDateStop;

        return $this;
    }

    public function getConActive()
    {
        return $this->conActive;
    }

    public function setConActive($conActive)
    {
        $this->conActive = $conActive;

        return $this;
    }

    public function getConLastUpdated()
    {
        return $this->conLastUpdated;
    }

    public function setConLastUpdated($conLastUpdated)
    {
        $this->conLastUpdated = $conLastUpdated;

        return $this;
    }

    public function getConPerId()
    {
        return $this->conPerId;
    }

    public function setConPerId($conPerId)
    {
        $this->conPerId = $conPerId;

        return $this;
    }
}
