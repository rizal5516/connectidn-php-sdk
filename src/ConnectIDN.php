<?php

declare(strict_types=1);

namespace BSSN\ConnectIDN;

use Jumbojett\OpenIDConnectClient;

class ConnectIDN
{
    private ConnectIDNConfig $config;

    public function __construct(ConnectIDNConfig $config)
    {
        $this->config = $config;
    }

    public function authenticate(): ConnectIDNUser
    {
        $oidc = $this->buildClient();
        $oidc->authenticate();

        return new ConnectIDNUser(
            sub:           $oidc->getVerifiedClaims('sub'),
            name:          $oidc->getVerifiedClaims('name'),
            email:         $oidc->getVerifiedClaims('email'),
            emailVerified: $oidc->getVerifiedClaims('email_verified'),
            phoneNumber:   $oidc->getVerifiedClaims('phone_number'),
            picture:       $oidc->getVerifiedClaims('picture'),
            rid:           $oidc->getVerifiedClaims('RID'),
            nip:           $oidc->getVerifiedClaims('nip'),
            roles:         (array) ($oidc->getVerifiedClaims('roles') ?? []),
            accessToken:   $oidc->getAccessToken(),
            idToken:       $oidc->getIdToken(),
        );
    }

    public function logout(string $idToken, ?string $postLogoutUri = null): never
    {
        $oidc = $this->buildClient();
        $oidc->signOut($idToken, $postLogoutUri ?? $this->config->postLogoutUri);
        exit;
    }

    public function loginButton(
        string $loginPath = '/auth/login',
        string $label = 'Login with ConnectIDN',
        string $size = 'md',
        string $variant = 'default',
    ): string {
        // Cegah JavaScript/VBScript URI injection pada href
        if (preg_match('/^\s*(javascript|vbscript|data):/i', $loginPath)) {
            $loginPath = '/auth/login';
        }

        $isOutline = $variant === 'outline';
        $sizes = [
            'sm' => 'padding:8px 16px;font-size:13px;border-radius:6px',
            'md' => 'padding:11px 22px;font-size:15px;border-radius:8px',
            'lg' => 'padding:14px 28px;font-size:17px;border-radius:10px',
        ];

        $bg    = $isOutline ? 'transparent' : '#1a5276';
        $color = $isOutline ? '#1a5276' : '#ffffff';
        $border = $isOutline ? 'border:1.5px solid #1a5276;' : '';
        $style = "display:inline-flex;align-items:center;gap:8px;font-family:Inter,system-ui,sans-serif;font-weight:500;text-decoration:none;background:{$bg};color:{$color};{$border}" . ($sizes[$size] ?? $sizes['md']);

        $logo = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><circle cx="12" cy="12" r="11" stroke="currentColor" stroke-width="2"/><path d="M8 12a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" fill="currentColor" opacity="0.8"/></svg>';
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $safePath  = htmlspecialchars($loginPath, ENT_QUOTES, 'UTF-8');

        return "<a href=\"{$safePath}\" style=\"{$style}\" aria-label=\"{$safeLabel}\">{$logo}{$safeLabel}</a>";
    }

    private function buildClient(): OpenIDConnectClient
    {
        $oidc = new OpenIDConnectClient(
            $this->config->issuerUrl(),
            $this->config->clientId,
            $this->config->clientSecret,
        );

        $oidc->setRedirectURL($this->config->redirectUri);
        $oidc->addScope($this->config->scopes);
        $oidc->setCodeChallengeMethod('S256');

        if ($this->config->environment === 'development') {
            $oidc->setVerifyHost(false);
            $oidc->setVerifyPeer(false);
        }

        return $oidc;
    }
}
