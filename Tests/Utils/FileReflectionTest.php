<?php

declare(strict_types=1);

namespace ViewComponent\Tests\Utils;

use PHPUnit\Framework\TestCase;
use ViewComponent\Utils\FileReflection;

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
