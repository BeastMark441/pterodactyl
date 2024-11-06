<?php

namespace Pterodactyl\Http\Requests\Api\Client\Account;

use phpseclib3\Crypt\DSA;
use phpseclib3\Crypt\RSA;
use Pterodactyl\Models\UserSSHKey;
use Illuminate\Validation\Validator;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Exception\NoKeyLoadedException;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class StoreSSHKeyRequest extends ClientApiRequest
{
    protected ?PublicKey $key;

    /**
     * Возвращает правила для этого запроса.
     */
    public function rules(): array
    {
        return [
            'name' => UserSSHKey::getRulesForField('name'),
            'public_key' => UserSSHKey::getRulesForField('public_key'),
        ];
    }

    /**
     * Проверяет, был ли этот SSH-ключ уже добавлен в учетную запись пользователя,
     * и если да, возвращает ошибку.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function () {
            try {
                $this->key = PublicKeyLoader::loadPublicKey($this->input('public_key'));
            } catch (NoKeyLoadedException $exception) {
                $this->validator->errors()->add('public_key', 'Предоставленный публичный ключ недействителен.');

                return;
            }

            if ($this->key instanceof DSA) {
                $this->validator->errors()->add('public_key', 'DSA ключи не поддерживаются.');
            }

            if ($this->key instanceof RSA && $this->key->getLength() < 2048) {
                $this->validator->errors()->add('public_key', 'RSA ключи должны быть длиной не менее 2048 байт.');
            }

            $fingerprint = $this->key->getFingerprint('sha256');
            if ($this->user()->sshKeys()->where('fingerprint', $fingerprint)->exists()) {
                $this->validator->errors()->add('public_key', 'Предоставленный публичный ключ уже существует в вашей учетной записи.');
            }
        });
    }

    /**
     * Возвращает публичный ключ, но отформатированный в едином стиле.
     */
    public function getPublicKey(): string
    {
        return $this->key->toString('PKCS8');
    }

    /**
     * Возвращает SHA256 отпечаток предоставленного ключа.
     */
    public function getKeyFingerprint(): string
    {
        if (!$this->key) {
            throw new \Exception('Публичный ключ не был правильно загружен для этого запроса.');
        }

        return $this->key->getFingerprint('sha256');
    }
}
