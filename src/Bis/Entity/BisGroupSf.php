<?php

namespace Bis\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BisGroupSf
 *
 * @package Bis\Entity
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @ORM\Entity
 * @ORM\Table(name="bis_group_sf")
 */
class BisGroupSf
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="gro_code", length=100)
     */
    private $groCode;

    /**
     * @ORM\Column(type="string", name="gro_name", length=150)
     */
    private $groName;

    /**
     * @ORM\Column(type="string", name="gro_type", length=100)
     */
    private $groType;

    /**
     * @ORM\Column(type="string", name="gro_parent_code", length=100, nullable=true)
     * @ORM\ManyToOne(targetEntity="Bis\Entity\BisGroupSf")
     */
    private $groParentCode;

    /**
     * @ORM\Column(type="boolean", name="gro_active")
     */
    private $groActive;

    /**
     * @ORM\Column(type="datetime", name="gro_last_updated", options={"default"=0})
     */
    private $groLastUpdated;

    public function getGroCode()
    {
        return $this->groCode;
    }

    public function setGroCode($groCode)
    {
        $this->groCode = $groCode;

        return $this;
    }

    public function getGroName()
    {
        return $this->groName;
    }

    public function setGroName($groName)
    {
        $this->groName = $groName;

        return $this;
    }

    public function getGroType()
    {
        return $this->groType;
    }

    public function setGroType($groType)
    {
        $this->groType = $groType;

        return $this;
    }

    public function getGroParentCode()
    {
        return $this->groParentCode;
    }

    public function setGroParentCode($groParentCode)
    {
        $this->groParentCode = $groParentCode;

        return $this;
    }

    public function getGroActive()
    {
        return $this->groActive;
    }

    public function setGroActive($groActive)
    {
        $this->groActive = $groActive;

        return $this;
    }

    public function getGroLastUpdated()
    {
        return $this->groLastUpdated;
    }

    public function setGroLastUpdated($groLastUpdated)
    {
        $this->groLastUpdated = $groLastUpdated;

        return $this;
    }
}
