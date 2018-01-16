<?php

namespace AuthBundle\Service;

/**
 * Class ActiveDirectoryResponseStatus
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class ActiveDirectoryResponseStatus
{
    const NOTHING_TO_DO = 0;
    const ACTION_NEEDED = 1;
    const DONE = 100;
    const FAILED = 200;
    const EXCEPTION = 500;
}
