<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Webmozart\Assert\Assert;
use Pterodactyl\Transformers\Api\Client\BaseClientTransformer;
use Pterodactyl\Http\Controllers\Api\Application\ApplicationApiController;

abstract class ClientApiController extends ApplicationApiController
{
    /**
     * Возвращает только те включения, которые допустимы для данного трансформера.
     */
    protected function getIncludesForTransformer(BaseClientTransformer $transformer, array $merge = []): array
    {
        $filtered = array_filter($this->parseIncludes(), function ($datum) use ($transformer) {
            return in_array($datum, $transformer->getAvailableIncludes());
        });

        return array_merge($filtered, $merge);
    }

    /**
     * Возвращает разобранные включения для этого запроса.
     */
    protected function parseIncludes(): array
    {
        $includes = $this->request->query('include') ?? [];

        if (!is_string($includes)) {
            return $includes;
        }

        return array_map(function ($item) {
            return trim($item);
        }, explode(',', $includes));
    }

    /**
     * Возвращает экземпляр трансформера приложения.
     *
     * @template T of \Pterodactyl\Transformers\Api\Client\BaseClientTransformer
     *
     * @param class-string<T> $abstract
     *
     * @return T
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function getTransformer(string $abstract)
    {
        Assert::subclassOf($abstract, BaseClientTransformer::class);

        return $abstract::fromRequest($this->request);
    }
}
