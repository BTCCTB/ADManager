<?php

namespace Bis\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class BisLevelSf
 *
 * @ORM\Entity()
 * @ORM\Table(name="view_bis_level_sf")
 *
 * @UniqueEntity(fields={"con_per_id", "gro_code"})
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class BisLevelSf
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Bis\Entity\BisPersonView", inversedBy="levels")
     * @ORM\JoinColumn(name="con_per_id", referencedColumnName="per_id", nullable=true)
     * @var BisPersonView
     */
    private $conPerId;

    /**
     * @ORM\Column(type="string", name="gro_code", length=100)
     * @var string
     */
    private $groCode;

    /**
     * @ORM\Column(type="string", name="gro_type", length=100)
     * @ORM\Id
     * @var string
     */
    private $groType;

    /**
     * @ORM\Column(type="string", name="gro_name", length=150)
     * @var string
     */
    private $groName;

    public function getConPerId()
    {
        return $this->conPerId;
    }

    public function setConPerId($conPerId)
    {
        $this->conPerId = $conPerId;

        return $this;
    }

    public function getGroCode()
    {
        return $this->groCode;
    }

    public function setGroCode($groCode)
    {
        $this->groCode = $groCode;

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

    public function getGroName()
    {
        return $this->groName;
    }

    public function setGroName($groName)
    {
        $this->groName = $groName;

        return $this;
    }
}
