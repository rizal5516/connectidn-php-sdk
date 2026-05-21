<?php

declare(strict_types=1);

namespace BSSN\ConnectIDN;

class ConnectIDNConfig
{
    public const ISSUER_URLS = [
        'staging' => 'https://stg-connect-idn.bssn.go.id/realms/identity-broker',
    ];

    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $redirectUri,
        public readonly string $environment = 'staging',
        public readonly array  $scopes = ['openid', 'profile', 'email'],
        public readonly string $postLogoutUri = '/',
    ) {}

    public function issuerUrl(): string
    {
        return self::ISSUER_URLS[$this->environment]
            ?? throw new \InvalidArgumentException("Environment '{$this->environment}' belum tersedia.");
    }
}
