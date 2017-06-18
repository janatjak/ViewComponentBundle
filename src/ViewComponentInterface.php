<?php

declare(strict_types=1);

namespace ViewComponent;

interface ViewComponentInterface
{
    public function render(): array;
}
