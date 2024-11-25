<?php
declare(strict_types=1);

require_once 'Subdomain.php';

final class GopherConfig
{
    /**
     * @param array<string, Subdomain> $subdomains
     * @param string[] $welcomeMessage
     */
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly array $subdomains,
        public readonly array $welcomeMessage,
    )
    {
    }
}