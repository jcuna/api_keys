<?php

/**
 * Created by jcuna.
 * Date: 9/25/18
 * Time: 6:32 PM
 */

declare(strict_types=1);

namespace Jcuna\ApiKeys\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiKeysException extends \Exception implements HttpExceptionInterface
{
    public const INVALID_TOKEN = 'it';
    public const UNAUTHORIZED = 'nt';
    public const TOKEN_EXPIRED = 'te';

    public const CODE_MAP = [
        self::INVALID_TOKEN => [ 'code' => 498, 'message' => 'Invalid token provided'],
        self::UNAUTHORIZED => [ 'code' => 403, 'message' => 'Unauthorized access'],
        self::TOKEN_EXPIRED => [ 'code' => 401, 'message' => 'Token Expired']
    ];

    /**
     * ApiKeysException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $storedCode = $code;
        $storedMessage = $message;

        if (isset(static::CODE_MAP[$message])) {
            $storedCode = static::CODE_MAP[$message]['code'];
            $storedMessage = static::CODE_MAP[$message]['message'];
        }

        parent::__construct($storedMessage, $storedCode, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return ['Access-Control-Allow-Origin' => '*'];
    }
}
