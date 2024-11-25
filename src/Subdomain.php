<?php
declare(strict_types=1);

final class Subdomain
{
    public function __construct(
        public readonly string $name,
        public readonly string $directory,
    )
    {

    }
}