<?php

declare(strict_types=1);

namespace Starychfojtu\ViewComponentBundle\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Starychfojtu\ViewComponentBundle\Utils\FileReflection;

class FileReflectionTest extends TestCase
{
    public function test_can_recognize_namespace()
    {
        // Arrange
        $file = __DIR__.'../Utils/FileReflectionTest.php';

        // Act
        $namespace = FileReflection::getClassNamespaceFromFile($file);

        // Assert
        $this->assertSame($namespace, 'ViewComponent\Tests\Utils');
    }
}
