<?php

declare(strict_types=1);

/*
 * This file is part of the `starychfojtu/viewcomponent` project.
 *
 * (c) https://github.com/starychfojtu/ViewComponentBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Starychfojtu\ViewComponentBundle\Finder;

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Finder\Finder;
use Starychfojtu\ViewComponentBundle\Exception\ComponentNotFoundException;
use Starychfojtu\ViewComponentBundle\Utils\FileReflection;
use Starychfojtu\ViewComponentBundle\ViewComponentInterface;

class ViewComponentFinder
{
    public const SRC_DIR = __DIR__ . '/../../../../src/';
    public const CONFIG_COMPONENTS_DIRS = 'component_dirs';
    private const CACHE_PREFIX = 'Component_';
    private const CACHE_DIR = __DIR__.'/../../../../var/cache/';
    private const CACHE_NAME = '/view_component';

    /**
     * @var array
     */
    private $configuredComponentDirs;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FilesystemCache
     */
    private $cache;

    /**
     * @param array $configuredComponentDirs
     * @param ContainerInterface $container
     */
    public function __construct(array $configuredComponentDirs, ContainerInterface $container)
    {
        $this->configuredComponentDirs = $configuredComponentDirs;
        $this->container = $container;
        $this->cache = new FilesystemCache('', 0, self::CACHE_DIR.getenv('APP_ENV').self::CACHE_NAME);
    }

    /**
     * @param string $name
     * @return null|ViewComponentInterface
     * @throws ComponentNotFoundException
     */
    public function findViewComponent(string $name): ?ViewComponentInterface
    {
        $cacheName = self::CACHE_PREFIX.$name;

        if ($this->cache->has($cacheName)) {
            return $this->instantiateViewComponent((string) $this->cache->get($cacheName));
        }

        $componentsDirs = $this->getComponentDirs();

        foreach ($componentsDirs as $dir) {
            $viewComponent = $this->findViewComponentInDir($name, $dir);
            if ($viewComponent != null) {
                $this->cache->set($cacheName, $viewComponent);

                return $this->instantiateViewComponent($viewComponent);
            }
        }

        throw new ComponentNotFoundException(
            ComponentNotFoundException::getErrorMessage($name, $componentsDirs)
        );
    }

    /**
     * @return array
     */
    private function getComponentDirs(): array
    {
        $func = function ($dir) {
            return self::SRC_DIR.$dir;
        };

        return array_map($func, $this->configuredComponentDirs);
    }

    private function findViewComponentInDir(string $name, string $dir): ?string
    {
        $finder = new Finder();
        $finder->files()->in($dir);

        foreach ($finder as $file) {
            $componentName = self::stripName($file->getRelativePathname());
            if ($componentName == $name) {
                return FileReflection::getClassNamespaceFromFile($file->getRealPath())
                    . '\\'
                    . $componentName
                    . 'ViewComponent';
            }
        }

        return null;
    }

    /**
     * @param string $class
     * @return ViewComponentInterface
     */
    private function instantiateViewComponent(string $class): ViewComponentInterface
    {
        return $this->container->get($class);
    }

    /**
     * @param string $name
     * @return string
     */
    private static function stripName(string $name): string
    {
        return str_replace('ViewComponent.php', '', $name);
    }
}
