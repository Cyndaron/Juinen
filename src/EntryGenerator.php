<?php
declare(strict_types=1);

final class EntryGenerator
{
    private static function writeInfoEntry(string $info): string
    {
        return "i{$info}\tfake\t(NULL)\t0\r\n";
    }

    private static function writeListEntry(GopherConfig $config, string $description, string $link): string
    {
        $host = $config->host;
        $port = $config->port;
        return"1{$description}\t{$link}\t{$host}\t{$port}\r\n";
    }

    private static function writeTerminator(): string
    {
        return ".\r\n";
    }

    private static function createSubdomainsListing(GopherConfig $config): string
    {
        $output = [];
        foreach ($config->welcomeMessage as $line)
        {
            $output[] = EntryGenerator::writeInfoEntry($line);
        }
        $output[] = EntryGenerator::writeInfoEntry('');

        if (count($config->subdomains) > 0)
        {
            foreach ($config->subdomains as $domain => $data)
            {
                $output[] = EntryGenerator::writeListEntry($config, $data->name, "/{$domain}");
            }
        }
        else
        {
            $output[] = EntryGenerator::writeInfoEntry('No domains found!');

        }

        $output[] = EntryGenerator::writeTerminator();
        return implode('', $output);
    }

    private static function createCapsListing(): string
    {
        return file_get_contents(__DIR__ . '/../config/caps.txt');
    }

    private static function getConnectorAnswer(GopherConfig $config, string $subdomain, string $query): string
    {
        $domainSettings = $config->subdomains[$subdomain];
        $args = array_map(escapeshellarg(...), [$config->host, $subdomain, $config->port, $query]);
        $result = shell_exec("{$domainSettings->directory}/bin/gopher-connector.php " . implode(' ', $args));
        return (string)$result;
    }

    public static function getResponseForQuery(GopherConfig $config, string $query): string
    {
        $query = trim($query);
        $query = ltrim($query, '/');

        $parts = explode('/', $query);
        $gopherSubdomain = array_shift($parts);
        echo "Subdomain: {$gopherSubdomain}\n";
        $query = implode('/', $parts);

        if (!array_key_exists($gopherSubdomain, $config->subdomains))
        {
            if ($gopherSubdomain === 'caps.txt')
            {
                return self::createCapsListing();
            }

            return self::createSubdomainsListing($config);
        }

        $result = EntryGenerator::getConnectorAnswer($config, $gopherSubdomain, $query);
        if (empty($result)) {
            return self::writeTerminator();
        }

        return $result;
    }
}