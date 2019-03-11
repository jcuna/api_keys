<?php

/**
 * Created by jcuna.
 * Date: 9/24/18
 * Time: 7:59 PM
 */

declare(strict_types=1);

namespace Jcuna\ApiKeys\Models;

use Illuminate\Database\Eloquent\Builder;
use \Illuminate\Database\Eloquent\Model;

/**
 * Class ApiKey
 *
 *
 * @method static Builder where(string $key, string $val_comp, $value = null)
 * @package Jcuna\ApiKeys\Models
 */
class ApiKey extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'app_name',
        'origin_url',
        'access_map',
        'expires_at',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $instance) {
            do {
                $randomKey = bin2hex(random_bytes(32));
            } while (self::keyExists($randomKey));

            $instance->attributes['key'] = $randomKey;
        });
    }

    /**
     * @param null|string $dateTime
     */
    public function setExpiresAtAttribute(?string $dateTime): void
    {
        if (! is_null($dateTime)) {
            $d = new \DateTime($dateTime);
            $this->attributes['expires_at'] = $d->format('Y-m-d G:i:s');
        }
    }

    /**
     * @return int
     */
    public function getExpiresAtAttribute(): int
    {
        return \DateTime::createFromFormat('Y-m-d G:i:s', $this->attributes['expires_at'])->getTimestamp();
    }

    /**
     * @param string $map
     */
    public function setAccessMapAttribute(?string $map): void
    {
        if (! is_null($map)) {
            $this->attributes['access_map'] = json_encode(
                explode(',', trim($map, ' '))
            );
        } else {
            $this->attributes['access_map'] = null;
        }
    }

    /**
     * @return array|null
     */
    public function getAccessMapAttribute(): ?array
    {
        $attr = $this->attributes['access_map'];
        return ! is_null($attr) ? json_decode($attr, true) : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    private static function keyExists(string $key): bool
    {
        return self::where('key', $key)->count() > 0;
    }
}
