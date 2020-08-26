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
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class LoggableEntry extends AbstractLogEntry
{
}
