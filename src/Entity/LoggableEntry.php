<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

/**
 * Class LoggableEntry
 *
 * @package App\Entity
 *
 * @ORM\Table(name="loggable_entry")
 * @ORM\Entity(repositoryClass="App\Repository\LoggableEntryRepository")
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class LoggableEntry extends AbstractLogEntry
{

}
