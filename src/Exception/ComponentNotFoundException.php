<?php

declare(strict_types=1);

namespace ViewComponent\Exception;

use Exception;

class ComponentNotFoundException extends Exception
{
    public static function getErrorMessage(string $name, array $componentsDirs): string
    {
        $message = 'Component with name '.$name.'. Looked into: ';

        foreach ($componentsDirs as $dir)
        {
            $message .= $dir.', ';
        }

        return $message;
    }
}