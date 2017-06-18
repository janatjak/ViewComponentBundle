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

namespace ViewComponent\Twig;

use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Twig_Environment;
use ViewComponent\Exception\ComponentNotFoundException;
use Twig_Extension;
use Twig_SimpleFunction;
use ViewComponent\Exception\TemplateNotFoundException;
use ViewComponent\ViewComponentInterface;

class ViewComponentExtension extends Twig_Extension
{
    //TODO: AutoRegister ViewComponents as services
    //TODO: is named ViewComponent or implement interface ViewComponent

    public const SRC_DIR = __DIR__.'/../../../../src/';
    public const TEMPLATES_DIR = __DIR__.'/../../../../templates/';

    public const CONFIG_TEMPLATES_DIRS = 'template_dirs';
    public const CONFIG_COMPONENTS_DIRS = 'component_dirs';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $config;

    /**
     * ViewComponentExtension constructor.
     * @param ContainerInterface $container
     * @param Twig_Environment $twig
     * @param array $config
     */
    public function __construct(ContainerInterface $container, Twig_Environment $twig, array $config)
    {
        $this->container = $container;
        $this->twig = $twig;
        $this->config = $config;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'component',
                [$this, 'renderViewComponent'],
                ['is_safe' => ['html']])
        ];
    }

    public function renderViewComponent(string $name): string
    {
        $class = $this->findViewComponent($name);
        $viewComponent = $this->instantiateViewComponent($class);
        $data = $viewComponent->render();

        if (array_key_exists('template', $data)) {
            $template = $data['template'];
            unset($data['template']);
            return $this->twig->render($template, $data);
        }

        return $this->twig->render($this->findTemplate($name), $data);
    }

    public function findViewComponent(string $name): ?string
    {
        $componentsDirs = $this->getComponentDirs();

        foreach ($componentsDirs as $dir) {
            $viewComponent = $this->findViewComponentInDir($name, $dir);
            if ($viewComponent != null) {
                return $viewComponent;
            }
        }

        throw new ComponentNotFoundException(
            ComponentNotFoundException::getErrorMessage($name, $componentsDirs)
        );
    }

    public function getComponentDirs()
    {
        $func = function ($dir) {
            return self::SRC_DIR.$dir;
        };

        return array_map($func, $this->config[self::CONFIG_COMPONENTS_DIRS]);
    }

    public function findViewComponentInDir(string $name, string $dir): ?string
    {
        $finder = new Finder();
        $finder->files()->in($dir);

        foreach ($finder as $file) {
            $componentName = self::stripName($file->getRelativePathname());
            if ($componentName == $name) {
                return self::getClassNamespaceFromFile($file->getRealPath())
                    . '\\'
                    . $componentName
                    . 'ViewComponent';
            }
        }

        return null;
    }

    public function findTemplate(string $name): ?string
    {
        $templateDirs = $this->getTemplateDirs();

        for ($i = 0; $i < count($templateDirs); $i++)
        {
            $template = $this->findTemplateInDir($name, $templateDirs[$i]);

            if ($template != null) {
                return $this->config[self::CONFIG_TEMPLATES_DIRS][$i].'/'.$template;
            }
        }

        throw new TemplateNotFoundException(
            ComponentNotFoundException::getErrorMessage($name, $templateDirs)
        );
    }

    public function getTemplateDirs()
    {
        $func = function ($dir) {
            return self::TEMPLATES_DIR.$dir;
        };

        return array_map($func, $this->config[self::CONFIG_TEMPLATES_DIRS]);
    }

    public function findTemplateInDir(string $name, string $dir): ?string
    {
        $finder = new Finder();
        $finder->files()->in($dir);

        foreach ($finder as $file) {
            $componentName = $file->getRelativePathname();
            if ($componentName == $name . '.html.twig') {
                return $file->getRelativePathname();
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

    public static function getClassNamespaceFromFile(string $filePathName): ?string
    {
        $src = file_get_contents($filePathName);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) {
            return null;
        } else {
            return $namespace;
        }
    }
}
