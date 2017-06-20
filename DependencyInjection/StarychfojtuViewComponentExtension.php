<?php

/*
 * This file is part of the `starychfojtu/viewcomponent` project.
 *
 * (c) https://github.com/starychfojtu/ViewComponentBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Starychfojtu\ViewComponentBundle\DependencyInjection;

use Starychfojtu\ViewComponent\Finder\ViewComponentFinder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Starychfojtu\ViewComponentBundle\Finder\TemplateFinder;

class StarychfojtuViewComponentExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'view_component.config.view_component_dirs',
                $config[ViewComponentFinder::CONFIG_COMPONENTS_DIRS]
        );
        $container->setParameter(
            'view_component.config.template_dirs',
                $config[TemplateFinder::CONFIG_TEMPLATES_DIRS]
        );

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.xml');
    }
}
