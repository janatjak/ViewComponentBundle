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
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;
use Starychfojtu\ViewComponentBundle\Finder\TemplateFinder;

class ViewComponentExtension extends Twig_Extension
{
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
     * StarychfojtuViewComponentExtension constructor.
     * @param Twig_Environment $twig
     * @param ViewComponentFinder $viewComponentFinder
     * @param TemplateFinder $templateFinder
     * @internal param array $config
     */
    public function __construct(
        Twig_Environment $twig,
        ViewComponentFinder $viewComponentFinder,
        TemplateFinder $templateFinder
    ) {
        $this->twig = $twig;
        $this->viewComponentFinder = $viewComponentFinder;
        $this->templateFinder = $templateFinder;
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
     * @return string
     */
    public function renderViewComponent(string $name): string
    {
        $data = $this->viewComponentFinder
            ->findViewComponent($name)
            ->render();

        if (array_key_exists('template', $data)) {
            $template = $data['template'];
            unset($data['template']);
            return $this->twig->render($template, $data);
        }

        return $this->twig->render($this->templateFinder->findTemplate($name), $data);
    }
}
