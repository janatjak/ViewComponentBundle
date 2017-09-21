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

use Nayjest\StrCaseConverter\Str;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Finder\Finder;
use Starychfojtu\ViewComponentBundle\Exception\ComponentNotFoundException;
use Starychfojtu\ViewComponentBundle\Exception\TemplateNotFoundException;

class TemplateFinder
{
    public const TEMPLATES_DIR = __DIR__ . '/../../../../templates/';
    public const CONFIG_TEMPLATES_DIRS = 'template_dirs';
    private const CACHE_PREFIX = 'Component_template_';
    private const CACHE_DIR = __DIR__.'/../../../../var/cache/';
    private const CACHE_NAME = '/view_component';

    /**
     * @var array
     */
    private $configuredTemplateDirs;

    /**
     * @var FilesystemCache
     */
    private $cache;

    /**
     * @param array $configuredTemplateDirs
     */
    public function __construct(array $configuredTemplateDirs)
    {
        $this->configuredTemplateDirs = $configuredTemplateDirs;
        $this->cache = new FilesystemCache('', 0, self::CACHE_DIR.getenv('APP_ENV').self::CACHE_NAME);
    }

    /**
     * @param string $name
     * @return null|string
     * @throws TemplateNotFoundException
     */
    public function findTemplate(string $name): ?string
    {
        $cacheName = self::CACHE_PREFIX.$name;

        if ($this->cache->has($cacheName)) {
            return $this->cache->get($cacheName);
        }

        $templateDirs = $this->getTemplateDirs(self::TEMPLATES_DIR);

        for ($i = 0; $i < count($templateDirs); $i++) {
            $template = $this->findTemplateInDir($name, $templateDirs[$i]);

            if ($template != null) {
                $templateDir = $this->configuredTemplateDirs[$i].'/'.$template;
                $this->cache->set($cacheName, $templateDir);

                return $templateDir;
            }
        }

        throw new TemplateNotFoundException(
            ComponentNotFoundException::getErrorMessage($name, $templateDirs)
        );
    }

    /**
     * @param string $rootDir
     * @return array
     */
    public function getTemplateDirs(string $rootDir): array
    {
        $func = function ($dir) use ($rootDir){
            return $rootDir . $dir;
        };

        return array_map($func, $this->configuredTemplateDirs);
    }

    /**
     * @param string $name
     * @param string $dir
     * @return null|string
     */
    public function findTemplateInDir(string $name, string $dir): ?string
    {
        $finder = new Finder();
        $finder->files()->in($dir);

        foreach ($finder as $file) {
            $templateName = Str::toCamelCase($file->getRelativePathname());
            if ($templateName == $name . '.html.twig') {
                return $file->getRelativePathname();
            }
        }

        return null;
    }
}
