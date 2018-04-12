<?php

namespace App\Entity;

use App\Entity\Traits\Blameable;
use App\Entity\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Application
 *
 * @ORM\Table(name="application")
 * @ORM\Entity(repositoryClass="App\Repository\ApplicationRepository")
 * @Gedmo\Loggable(logEntryClass="LoggableEntry")
 */
class Application implements EntityInterface
{
    // Traits
    use Timestampable;
    use Blameable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, unique=true)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="link_fr", type="string", length=255, nullable=true, unique=true)
     */
    private $linkFr;

    /**
     * @var string
     *
     * @ORM\Column(name="link_nl", type="string", length=255, nullable=true, unique=true)
     */
    private $linkNl;

    /**
     * @var bool
     *
     * @ORM\Column(name="enable", type="boolean", options={"default"=1})
     */
    private $enable;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Application
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Application
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set linkFr
     *
     * @param string $linkFr
     *
     * @return Application
     */
    public function setLinkFr($linkFr)
    {
        $this->linkFr = $linkFr;

        return $this;
    }

    /**
     * Get linkFr
     *
     * @return string
     */
    public function getLinkFr()
    {
        return $this->linkFr;
    }

    /**
     * Set linkNl
     *
     * @param string $linkNl
     *
     * @return Application
     */
    public function setLinkNl($linkNl)
    {
        $this->linkNl = $linkNl;

        return $this;
    }

    /**
     * Get linkNl
     *
     * @return string
     */
    public function getLinkNl()
    {
        return $this->linkNl;
    }

    /**
     * Set enable
     *
     * @param boolean $enable
     *
     * @return Application
     */
    public function setEnable($enable)
    {
        $this->enable = $enable;

        return $this;
    }

    /**
     * Get enable
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->enable;
    }
}
