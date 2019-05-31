<?php

namespace App\Entity\Traits;

use App\Entity\EntityInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait Timestampable
 *
 * @package App\Entity\Traits
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
trait Timestampable
{
    /**
     * @var DateTime|null
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var DateTime|null
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * Sets createdAt.
     *
     * @param DateTime $createdAt
     *
     * @return EntityInterface|$this
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return DateTime|null
     */
    public function getCreatedAt(): ? DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @param DateTime $updatedAt
     *
     * @return EntityInterface|$this
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     *
     * @return DateTime|null
     */
    public function getUpdatedAt() : ? DateTime
    {
        return $this->updatedAt;
    }
}
