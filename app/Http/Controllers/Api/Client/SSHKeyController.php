<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;
use Pterodactyl\Transformers\Api\Client\UserSSHKeyTransformer;
use Pterodactyl\Http\Requests\Api\Client\Account\StoreSSHKeyRequest;

class SSHKeyController extends ClientApiController
{
    /**
     * Возвращает все SSH-ключи, которые были настроены для учетной записи
     * вошедшего в систему пользователя.
     */
    public function index(ClientApiRequest $request): array
    {
        return $this->fractal->collection($request->user()->sshKeys)
            ->transformWith($this->getTransformer(UserSSHKeyTransformer::class))
            ->toArray();
    }

    /**
     * Сохраняет новый SSH-ключ для учетной записи аутентифицированного пользователя.
     */
    public function store(StoreSSHKeyRequest $request): array
    {
        $model = $request->user()->sshKeys()->create([
            'name' => $request->input('name'),
            'public_key' => $request->getPublicKey(),
            'fingerprint' => $request->getKeyFingerprint(),
        ]);

        Activity::event('user:ssh-key.create')
            ->subject($model)
            ->property('fingerprint', $request->getKeyFingerprint())
            ->log();

        return $this->fractal->item($model)
            ->transformWith($this->getTransformer(UserSSHKeyTransformer::class))
            ->toArray();
    }

    /**
     * Удаляет SSH-ключ из учетной записи пользователя.
     */
    public function delete(ClientApiRequest $request): JsonResponse
    {
        $this->validate($request, ['fingerprint' => ['required', 'string']]);

        $key = $request->user()->sshKeys()
            ->where('fingerprint', $request->input('fingerprint'))
            ->first();

        if (!is_null($key)) {
            $key->delete();

            Activity::event('user:ssh-key.delete')
                ->subject($key)
                ->property('fingerprint', $key->fingerprint)
                ->log();
        }

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
