<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    /** @var string[] */
    protected $fillable = [
        'driver',
        'location',
        'directory_root',
        'name',
        'friendly_name',
        'secure',
    ];

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function isSecure(): bool
    {
        return (bool) $this->secure;
    }

    public function url(): string
    {
        $domain = $this->name . '.test';

        if ($this->isSecure()) {
            return 'https://' . $domain;
        }

        return 'http://' . $domain;
    }

    public function getCertificateCommonName(): ?string
    {
        return optional($this->certificate)->common_name;
    }

    public static function getByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }
}
