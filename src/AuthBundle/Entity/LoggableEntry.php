<?php

namespace AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

/**
 * Class LoggableEntry
 *
 * @package AuthBundle\Entity
 * @ORM\Table(name="loggable_entry")
 * @ORM\Entity(repositoryClass="LoggableEntryRepository")
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class LoggableEntry extends AbstractLogEntry
{

}
