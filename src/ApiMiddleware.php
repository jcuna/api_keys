<?php

/**
 * Created by jcuna.
 * Date: 9/21/18
 * Time: 2:26 PM
 */

declare(strict_types=1);

namespace Jcuna\ApiKeys;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jcuna\ApiKeys\Exceptions\ApiKeysException;
use Jcuna\ApiKeys\Models\ApiKey;

class ApiMiddleware
{

    /**
     * @var ApiKey
     */
    private $apiKey;

    /**
     * ApiMiddleware constructor.
     * @param ApiKey $apiKey
     */
    public function __construct(ApiKey $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws ApiKeysException
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-Auth-Token');
        if (is_null($key)) {
            throw new ApiKeysException(ApiKeysException::UNAUTHORIZED);
        }

        $client = $this->apiKey->where('key', $key)->first();

        if (is_null($client)) {
            throw new ApiKeysException(ApiKeysException::INVALID_TOKEN);
        }

        $this->checkExpired($client);
        $this->checkOrigin($client, $request);
        $this->checkAccessMap($client, $request);

        if (env('API_KEYS_UPDATE_LAST_ACCESS', '0') === '1') {
            $client->setAttribute('last_accessed_at', DB::raw('Now()'));
            $client->save();
        }

        return $next($request);
    }

    /**
     * @param Model $client
     * @throws ApiKeysException
     */
    private function checkExpired(Model $client): void
    {
        if ($client->getAttribute('expires_at') <= time()) {
            throw new ApiKeysException(ApiKeysException::TOKEN_EXPIRED);
        }
    }

    /**
     * @param Model $client
     * @param Request $request
     * @throws ApiKeysException
     */
    private function checkOrigin(Model $client, Request $request): void
    {
        if (env('API_KEYS_CHECK_ORIGIN', '0') === '1') {
            $origin = $request->header('origin') ?? $request->server('HTTP_HOST');

            if ($origin !== $request->server('SERVER_NAME') &&
                strpos($origin, $client->getAttribute('origin_url')) === false) {
                throw new ApiKeysException(ApiKeysException::INVALID_TOKEN);
            }
        }
    }

    /**
     * @param Model $client
     * @param Request $request
     * @throws ApiKeysException
     */
    private function checkAccessMap(Model $client, Request $request): void
    {
        $accessMap = $client->getAttribute('access_map');

        if (is_array($accessMap) && ! in_array($request->server('SERVER_NAME'), $accessMap)) {
            throw new ApiKeysException(ApiKeysException::UNAUTHORIZED);
        }
    }
}
