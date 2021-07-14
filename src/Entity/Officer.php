<?php

namespace App\Entity;

use App\Repository\OfficerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;

/**
 * @ORM\Entity(repositoryClass=OfficerRepository::class)
 */
class Officer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="officer", cascade={"persist", "detach"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $countries = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCountries(): ?array
    {
        return $this->countries;
    }

    public function setCountries(array $countries): self
    {
        $this->countries = $countries;

        return $this;
    }

    public function getCountriesName(): ?array
    {
        $countries = [];

        foreach ($this->countries as $country) {
            $countries[$country] = Countries::getAlpha3Name($country);
        }

        return $countries;
    }
}
