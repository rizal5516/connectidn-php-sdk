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
        $redirectUri = $postLogoutUri ?? $this->config->postLogoutUri;

        if ($idToken === '') {
            header('Location: ' . $redirectUri);
            exit;
        }

        $oidc = $this->buildClient();
        $oidc->signOut($idToken, $redirectUri);
        exit;
    }

    public function loginButton(
        string $loginPath = '/auth/login',
        ?string $logoSrc = null,
    ): string {
        // Hanya izinkan path relatif; blokir URI injection dan protocol-relative URL
        if (preg_match('/^\s*(javascript|vbscript|data):/i', $loginPath)
            || !str_starts_with($loginPath, '/')
            || str_starts_with($loginPath, '//')) {
            $loginPath = '/auth/login';
        }

        $style = implode(';', [
            'display:inline-flex',
            'align-items:center',
            'gap:14px',
            'background:#294174',
            'color:#ffffff',
            'padding:12px 24px 12px 20px',
            'border-radius:12px',
            'text-decoration:none',
            'cursor:pointer',
            'border:none',
            'font-family:Inter,system-ui,sans-serif',
            'line-height:1.2',
        ]);

        $logo = $logoSrc !== null
            ? '<img src="' . htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8') . '" width="36" height="36" style="object-fit:contain;flex-shrink:0;" alt="">'
            : '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><circle cx="12" cy="12" r="11" stroke="currentColor" stroke-width="2"/><path d="M8 12a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" fill="currentColor" opacity="0.8"/></svg>';

        $text = '<span style="display:flex;flex-direction:column;text-align:center;align-items:center;">'
            . '<span style="font-size:11px;font-weight:400;opacity:0.85;letter-spacing:0.2px;">Login with</span>'
            . '<span style="font-size:17px;font-weight:700;letter-spacing:0.5px;">CONNECTIDN</span>'
            . '</span>';

        $safePath = htmlspecialchars($loginPath, ENT_QUOTES, 'UTF-8');

        return "<a href=\"{$safePath}\" style=\"{$style}\" aria-label=\"Login with ConnectIDN\">{$logo}{$text}</a>";
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

        if (!$this->config->verifyTls) {
            $oidc->setVerifyHost(false);
            $oidc->setVerifyPeer(false);
        }

        return $oidc;
    }
}
