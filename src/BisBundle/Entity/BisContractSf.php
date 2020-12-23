<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BisContractSf
 *
 * @package BisBundle\Entity
 *
 * @ORM\Entity(repositoryClass="BisBundle\Repository\BisContractSfRepository")
 * @ORM\Table(name="bis_contract_sf")
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class BisContractSf
{
    /**
     * @var int
     *
     * @ORM\Column(name="con_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $conId;

    /**
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisPersonSf", inversedBy="contracts")
     * @ORM\JoinColumn(name="con_per_id", referencedColumnName="per_id", nullable=true)
     */
    private $conPerId;

    /**
     * @ORM\OneToMany(targetEntity="BisBundle\Entity\BisConjobSf", mappedBy="fkConId")
     * @var BisConjobSf[]|null
     */
    private $conjobs;

    /**
     * @var string
     *
     * @ORM\Column(name="con_type", type="string", length=30, nullable=true)
     */
    private $conType;

    /**
     * @ORM\Column(name="con_date_start", type="date")
     */
    private $conDateStart;

    /**
     * @ORM\Column(name="con_date_stop", type="date", nullable=true)
     */
    private $conDateStop;

    /**
     * @ORM\Column(name="con_active", type="boolean")
     */
    private $conActive;

    /**
     * @ORM\Column(name="con_last_updated", type="datetime", options={"default"=0})
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

    public function getConPerId()
    {
        return $this->conPerId;
    }

    public function setConPerId($conPerId)
    {
        $this->conPerId = $conPerId;

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

    public function getConjobs()
    {
        return $this->conjobs;
    }
}
