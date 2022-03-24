<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public const KEY_PHP_VERSION = 'phpVersion';
    public const KEY_NODE_VERSION = 'nodeVersion';
    public const KEY_COMPOSER_VERSION = 'composerVersion';
    public const KEY_WORKING_DIRECTORY = 'workingDirectory';
    public const KEY_DATA_DIRECTORY = 'dataDirectory';

    /** @var string[] */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key): ?string
    {
        return optional(self::where('key', $key)->first())->value;
    }

    public static function find(string $key): ?self
    {
        return self::where('key', $key)->first();
    }

    public static function updateOrCreateValue(array $findAttributes, array $attributes): string
    {
        return self::updateOrCreate($findAttributes, $attributes)->value;
    }

    public static function updateValueByKey(string $key, string $value): self
    {
        return tap(self::find($key))->update(['value' => $value]);
    }
}
