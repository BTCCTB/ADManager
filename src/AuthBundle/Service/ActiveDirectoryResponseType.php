<?php

namespace AuthBundle\Service;

/**
 * Class ActiveDirectoryResponseType
 *
 * @package AuthBundle\Service
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
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
