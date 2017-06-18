<?php

declare(strict_types=1);

namespace ViewComponentBundle;

interface ViewComponentInterface
{
    public function render(): array;
}
