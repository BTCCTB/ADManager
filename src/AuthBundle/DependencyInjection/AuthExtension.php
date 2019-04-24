<?php

namespace AuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class AdldapExtension
 *
 * @package AuthBundle\DependencyInjection
 *
 * @author Damien Lagae <damienlagae@gmail.com>
 */
class AuthExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}
