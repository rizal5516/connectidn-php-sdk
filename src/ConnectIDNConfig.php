<?php

declare(strict_types=1);

namespace BSSN\ConnectIDN;

class ConnectIDNConfig
{
    public const ISSUER_URLS = [
        'development' => 'https://dev-connect-idn.bssn.go.id/realms/dev-identity-broker',
        'staging'     => 'https://stg-connect-idn.bssn.go.id/realms/identity-broker',
    ];

    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $redirectUri,
        public readonly string $environment = 'staging',
        public readonly array  $scopes = ['openid', 'profile', 'email'],
        public readonly string $postLogoutUri = '/',
        public readonly bool   $verifyTls = true,
    ) {}

    public function __debugInfo(): array
    {
        return [
            'clientId'      => $this->clientId,
            'clientSecret'  => '[REDACTED]',
            'redirectUri'   => $this->redirectUri,
            'environment'   => $this->environment,
            'scopes'        => $this->scopes,
            'postLogoutUri' => $this->postLogoutUri,
            'verifyTls'     => $this->verifyTls,
        ];
    }

    public function issuerUrl(): string
    {
        return self::ISSUER_URLS[$this->environment]
            ?? throw new \InvalidArgumentException("Environment '{$this->environment}' belum tersedia.");
    }
}
