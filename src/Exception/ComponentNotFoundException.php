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