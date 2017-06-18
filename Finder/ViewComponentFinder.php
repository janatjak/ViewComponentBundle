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

namespace ViewComponent\Finder;

use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;
use ViewComponent\Exception\ComponentNotFoundException;
use ViewComponent\Utils\FileReflection;
use ViewComponent\ViewComponentInterface;

class ViewComponentFinder
{
    public const SRC_DIR = __DIR__ . '/../../../../src/';
    public const CONFIG_COMPONENTS_DIRS = 'component_dirs';

    /**
     * @var array
     */
    private $configuredComponentDirs;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ViewComponentFinder constructor.
     * @param array $configuredComponentDirs
     * @param ContainerInterface $container
     */
    public function __construct(array $configuredComponentDirs, ContainerInterface $container)
    {
        $this->configuredComponentDirs = $configuredComponentDirs;
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return null|ViewComponentInterface
     * @throws ComponentNotFoundException
     */
    public function findViewComponent(string $name): ?ViewComponentInterface
    {
        $componentsDirs = $this->getComponentDirs();

        foreach ($componentsDirs as $dir) {
            $viewComponent = $this->findViewComponentInDir($name, $dir);
            if ($viewComponent != null) {
                return $this->instantiateViewComponent($viewComponent);
            }
        }

        throw new ComponentNotFoundException(
            ComponentNotFoundException::getErrorMessage($name, $componentsDirs)
        );
    }

    public function getComponentDirs(): array
    {
        $func = function ($dir) {
            return self::SRC_DIR.$dir;
        };

        return array_map($func, $this->configuredComponentDirs);
    }

    public function findViewComponentInDir(string $name, string $dir): ?string
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

    public function instantiateViewComponent(string $class): ViewComponentInterface
    {
        return $this->container->get($class);
    }

    public static function stripName(string $name): string
    {
        return str_replace('ViewComponent.php', '', $name);
    }
}
