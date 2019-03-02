<?php

declare(strict_types=1);

namespace App\User\Auth;

use ArtisanSdk\SRP\Contracts\Service as Contract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Traits\ForwardsCalls;

class SRP
{
    use ForwardsCalls;

    /**
     * The underlying SRP service calls are forwarded to.
     *
     * @var \ArtisanSdk\SRP\Contracts\Service
     */
    protected $service;

    /**
     * The cache repository where the underlying SRP service is cached.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * The TTL for the cache in seconds.
     *
     * @var int
     */
    protected $ttl;

    /**
     * Inject the underlying service and cache repository.
     *
     * @param \ArtisanSdk\SRP\Contracts\Service      $service
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param int                                    $ttl     for cache in seconds
     */
    public function __construct(Contract $service, Repository $cache, int $ttl)
    {
        $this->service = $service;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * Forward calls to the underlying service and cache the service state.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        $result = $this->forwardCallTo($this->service, $method, $arguments);

        if ($this->service->identity()) {
            $this->save();
        }

        return $result;
    }

    /**
     * Load out of cache the previously stored service state if it exists.
     *
     * @param string $identity
     *
     * @return self
     */
    public function load(string $identity): self
    {
        $this->service = $this->cache->get(
            $this->cacheKey($identity),
            $this->service
        );

        return $this;
    }

    /**
     * Save the underlying service state to cache under the identity for the user.
     *
     * @return self
     */
    public function save(): self
    {
        $this->cache->put(
            $this->cacheKey($this->service->identity()),
            $this->service,
            Carbon::now()->addSeconds($this->ttl)
        );

        return $this;
    }

    /**
     * Reset the service entirely by deleting the cached state.
     *
     * @return self
     */
    public function reset(): self
    {
        if ($identity = $this->service->identity()) {
            $this->cache->delete(
                $this->cacheKey($identity)
            );
        }

        return $this;
    }

    /**
     * Get the cache key.
     *
     * @param string $identity
     *
     * @return string
     */
    protected function cacheKey(string $identity): string
    {
        return class_basename(static::class).':'.$identity;
    }
}
