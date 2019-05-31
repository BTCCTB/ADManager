<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BisJobSf
 *
 * @package BisBundle\Entity
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="bis_job_sf")
 */
class BisJobSf
{
    /**
     * @ORM\Column(name="job_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $jobId;

    /**
     * @ORM\Column(type="string", name="job_group", length=100, nullable=true)
     */
    private $jobGroup;

    /**
     * @ORM\Column(type="string",name="job_function", length=300)
     */
    private $jobFunction;

    /**
     * @ORM\Column(type="string",name="job_country_workplace",length=3)
     */
    private $jobCountryWorkplace;

    /**
     * @ORM\Column(type="string",name="job_class")
     */
    private $jobClass;

    /**
     * @ORM\Column(type="string",name="job_city_workplace", length=100, nullable=true)
     */
    private $jobCityWorkplace;

    /**
     * @ORM\Column(type="integer", name="job_manager_id", nullable=true)
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisPersonSf")
     * @ORM\JoinColumn(nullable=true)
     */
    private $jobManagerId;

    /**
     * @ORM\Column(type="datetime", name="job_last_updated", options={"default": 0})
     */
    private $jobLastUpdated;

    public function getJobId()
    {
        return $this->jobId;
    }

    public function setJobId($jobId)
    {
        $this->jobId = $jobId;

        return $this;
    }

    public function getJobGroup()
    {
        return $this->jobGroup;
    }

    public function setJobGroup($jobGroup)
    {
        $this->jobGroup = $jobGroup;

        return $this;
    }

    public function getJobFunction()
    {
        return $this->jobFunction;
    }

    public function setJobFunction($jobFunction)
    {
        $this->jobFunction = $jobFunction;

        return $this;
    }

    public function getJobCountryWorkplace()
    {
        return $this->jobCountryWorkplace;
    }

    public function setJobCountryWorkplace($jobCountryWorkplace)
    {
        $this->jobCountryWorkplace = $jobCountryWorkplace;

        return $this;
    }

    public function getJobClass()
    {
        return $this->jobClass;
    }

    public function setJobClass($jobClass)
    {
        $this->jobClass = $jobClass;

        return $this;
    }

    public function getJobCityWorkplace()
    {
        return $this->jobCityWorkplace;
    }

    public function setJobCityWorkplace($jobCityWorkplace)
    {
        $this->jobCityWorkplace = $jobCityWorkplace;

        return $this;
    }

    public function getJobManagerId()
    {
        return $this->jobManagerId;
    }

    public function setJobManagerId($jobManagerId)
    {
        $this->jobManagerId = $jobManagerId;

        return $this;
    }

    public function getJobLastUpdated()
    {
        return $this->jobLastUpdated;
    }

    public function setJobLastUpdated($jobLastUpdated)
    {
        $this->jobLastUpdated = $jobLastUpdated;

        return $this;
    }
}
