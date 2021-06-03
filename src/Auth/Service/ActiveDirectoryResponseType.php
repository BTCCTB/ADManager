<?php

namespace Auth\Service;

/**
 * Class ActiveDirectoryResponseType
 *
 * @package Auth\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class ActiveDirectoryResponseType
{
    const GENERAL = 0;
    const CREATE = 1;
    const UPDATE = 2;
    const MOVE = 3;
    const DELETE = 4;
    const DISABLE = 5;
    const ENABLE = 6;
}
