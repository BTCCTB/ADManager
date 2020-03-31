<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BisJobgroupSf
 *
 * @package BisBundle\Entity
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @ORM\Entity
 * @ORM\Table(name="bis_jobgroup_sf")
 */
class BisJobgroupSf
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="fk_job_id")
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisJobSf")
     */
    private $fkJobId;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="fk_gro_id", length=100)
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisGroupSf")
     */
    private $fkGroId;

    public function getFkJobId()
    {
        return $this->fkJobId;
    }

    public function setFkJobId($fkJobId)
    {
        $this->fkJobId = $fkJobId;

        return $this;
    }

    public function getFkGroId()
    {
        return $this->fkGroId;
    }

    public function setFkGroId($fkGroId)
    {
        $this->fkGroId = $fkGroId;

        return $this;
    }
}
