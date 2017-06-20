<?php

declare(strict_types=1);

namespace Starychfojtu\ViewComponentBundle\Tests\Finder;

use PHPUnit\Framework\TestCase;
use Starychfojtu\ViewComponentBundle\Exception\TemplateNotFoundException;
use Starychfojtu\ViewComponentBundle\Finder\TemplateFinder;

class TemplateFinderTest extends TestCase
{
    public const TEMPLATES_DIR = __DIR__ . '/../TestData';

    public function test_will_find_templates()
    {
        // Arrange
        $templateFinder = new TemplateFinder($this->getTestDirs());

        // Act
        $result = $templateFinder->findTemplate('test');
        $result2 = $templateFinder->findTemplate('test2');

        // Assert
        $this->assertSame($result, 'test.html.twig');
        $this->assertSame($result2, 'components\test2.html.twig');
    }

    public function test_will_not_find_not_existing_template()
    {
        // Arrange
        $templateFinder = new TemplateFinder($this->getTestDirs());
        $this->expectException(TemplateNotFoundException::class);

        // Act
        $result = $templateFinder->findTemplate('shouldFail');
    }

    public function test_will_not_find_template_in_not_configured_dir()
    {
        // Arrange
        $templateFinder = new TemplateFinder(['']);
        $this->expectException(TemplateNotFoundException::class);

        // Act
        $result = $templateFinder->findTemplate('test2');
    }

    public function getTestDirs(): array
    {
        return [
            '',
            'components'
        ];
    }
}
