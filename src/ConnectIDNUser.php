<?php

declare(strict_types=1);

namespace BSSN\ConnectIDN;

class ConnectIDNUser
{
    public function __construct(
        public readonly string  $sub,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?bool   $emailVerified,
        public readonly ?string $phoneNumber,
        public readonly ?string $picture,
        public readonly ?string $rid,
        public readonly ?string $nip,
        public readonly array   $roles,
        public readonly string  $accessToken,
        public readonly ?string $idToken,
    ) {}

    public function toArray(): array
    {
        return [
            'sub'           => $this->sub,
            'name'          => $this->name,
            'email'         => $this->email,
            'email_verified'=> $this->emailVerified,
            'phone_number'  => $this->phoneNumber,
            'picture'       => $this->picture,
            'RID'           => $this->rid,
            'nip'           => $this->nip,
            'roles'         => $this->roles,
        ];
    }
}
