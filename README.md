# connectidn-php

ConnectIDN SDK for PHP — integrasi SSO via OpenID Connect (OIDC) dengan PKCE.

## Instalasi

```bash
composer require blpid/connectidn-php
```

## Quick Start

```php
use BSSN\ConnectIDN\ConnectIDN;
use BSSN\ConnectIDN\ConnectIDNConfig;

$config = new ConnectIDNConfig(
    clientId:     'YOUR_CLIENT_ID',
    clientSecret: 'YOUR_CLIENT_SECRET',
    redirectUri:  'https://yourapp.com/auth/callback',
);

$connectidn = new ConnectIDN($config);
```

## Contoh Penggunaan

### Login

Arahkan pengguna ke URL ini untuk memulai alur autentikasi:

```php
// auth/login.php
$connectidn->authenticate(); // redirect otomatis ke ConnectIDN
```

### Callback

`authenticate()` mendeteksi secara otomatis apakah request adalah redirect balik dari ConnectIDN. Panggil method yang sama di URL callback untuk menyelesaikan token exchange:

```php
// auth/callback.php
session_start();

$user = $connectidn->authenticate();

RequireAuth::store($user); // simpan ke session dengan aman
header('Location: /dashboard');
exit;
```

### Auth Guard

Proteksi halaman yang membutuhkan login:

```php
use BSSN\ConnectIDN\RequireAuth;

// Di awal halaman yang perlu login
RequireAuth::guard('/auth/login'); // redirect ke login jika belum autentikasi

$user = RequireAuth::user(); // ambil data user dari session
echo 'Halo, ' . htmlspecialchars($user['name']);
```

### Logout

```php
// auth/logout.php
session_start();

$idToken = $_SESSION['connectidn_id_token'] ?? null;
RequireAuth::clear(); // hapus session user

$connectidn->logout($idToken ?? '', 'https://yourapp.com');
```

### Login Button (HTML Helper)

```php
echo $connectidn->loginButton(
    loginPath: '/auth/login',
    label:     'Masuk dengan ConnectIDN',
    size:      'md',     // 'sm' | 'md' | 'lg'
    variant:   'default' // 'default' | 'outline'
);
```

## Opsi Konfigurasi

| Opsi | Tipe | Wajib | Default | Keterangan |
|------|------|-------|---------|------------|
| `clientId` | `string` | ✅ | — | Client ID dari portal ConnectIDN |
| `clientSecret` | `string` | ✅ | — | Client Secret dari portal ConnectIDN |
| `redirectUri` | `string` | ✅ | — | URL callback setelah login |
| `environment` | `string` | — | `'staging'` | Environment ConnectIDN |
| `scopes` | `array` | — | `['openid','profile','email']` | Scope OIDC |
| `postLogoutUri` | `string` | — | `'/'` | URL redirect setelah logout |

## Data User

`authenticate()` mengembalikan objek `ConnectIDNUser` dengan properti berikut:

| Properti | Tipe | Keterangan |
|----------|------|------------|
| `sub` | `string` | Subject identifier (user ID unik) |
| `name` | `?string` | Nama lengkap |
| `email` | `?string` | Alamat email |
| `emailVerified` | `?bool` | Status verifikasi email |
| `phoneNumber` | `?string` | Nomor telepon |
| `picture` | `?string` | URL foto profil |
| `rid` | `?string` | RID (identitas nasional) |
| `nip` | `?string` | NIP (ASN) |
| `roles` | `array` | Daftar role pengguna |
| `accessToken` | `string` | Access token OIDC |
| `idToken` | `?string` | ID token — simpan untuk keperluan logout |

## Keamanan

- PKCE (S256) diaktifkan secara default dan tidak dapat dinonaktifkan.
- `RequireAuth::store()` memanggil `session_regenerate_id()` untuk mencegah session fixation.
- Cookie session dikonfigurasi dengan `HttpOnly`, `Secure`, dan `SameSite=Lax`.
- `loginButton()` secara otomatis meng-escape HTML dan memblokir `javascript:`, `vbscript:`, `data:` URI.
- `RequireAuth::guard()` hanya mengizinkan path relatif untuk mencegah open redirect.

## Requirements

- PHP >= 8.1
- [`jumbojett/openid-connect-php`](https://github.com/jumbojett/OpenID-Connect-PHP) ^1.0

## Lisensi

MIT © BLPID — Badan Siber dan Sandi Negara
