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

namespace Starychfojtu\ViewComponentBundle\Twig;

use Starychfojtu\ViewComponentBundle\Finder\ViewComponentFinder;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;
use Starychfojtu\ViewComponentBundle\Finder\TemplateFinder;

class ViewComponentExtension extends Twig_Extension
{
    private const CACHE_PREFIX = 'Component_Response_';
    private const CACHE_DIR = __DIR__.'/../../../../var/cache/';
    private const CACHE_NAME = '/view_component';

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var ViewComponentFinder
     */
    private $viewComponentFinder;

    /**
     * @var TemplateFinder
     */
    private $templateFinder;

    /**
     * @var FilesystemCache
     */
    private $cache;

    /**
     * @param Twig_Environment $twig
     * @param ViewComponentFinder $viewComponentFinder
     * @param TemplateFinder $templateFinder
     */
    public function __construct(
        Twig_Environment $twig,
        ViewComponentFinder $viewComponentFinder,
        TemplateFinder $templateFinder
    ) {
        $this->twig = $twig;
        $this->viewComponentFinder = $viewComponentFinder;
        $this->templateFinder = $templateFinder;
        $this->cache = new FilesystemCache('', 0, self::CACHE_DIR.getenv('APP_ENV').self::CACHE_NAME);
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction(
                'component',
                [$this, 'renderViewComponent'],
                ['is_safe' => ['html']])
        ];
    }

    /**
     * @param string $name
     * @param int $cacheTime
     * @return string
     */
    public function renderViewComponent(string $name, int $cacheTime = 0): string
    {
        $cacheName = self::CACHE_PREFIX.$name;

        if ($this->cache->has($cacheName)) {
            /** @var string $cacheData */
            $cacheData = $this->cache->get($cacheName);

            return $cacheData;
        }

        $data = $this->viewComponentFinder
            ->findViewComponent($name)
            ->render();

        if (array_key_exists('template', $data)) {
            $template = $data['template'];
            unset($data['template']);
            return $this->twig->render($template, $data);
        }

        $result = $this->twig->render($this->templateFinder->findTemplate($name), $data);

        if ($cacheTime > 0) {
            $this->cache->set($cacheName, $result, $cacheTime);
        }

        return $result;
    }
}
