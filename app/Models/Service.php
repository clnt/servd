<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Service extends Model
{
    use HasFactory;

    public const TYPE_CORE = 'core';
    public const TYPE_DATABASE = 'database';
    public const TYPE_MEMORY_STORE = 'memory_store';
    public const TYPE_OTHER = 'other';

    public const DEFINED_BY_APPLICATION = 'application';
    public const DEFINED_BY_USER = 'user';

    /** @var string[] */
    protected $fillable = [
        'type',
        'defined_by',
        'name',
        'service_name',
        'version',
        'port',
        'enabled',
        'has_volume',
        'should_build',
        'service_folders',
        'single_stub',
        'available_versions',
    ];

    public static array $nodeVersions = [
        'Node.js v12' => '12',
        'Node.js v14 (LTS)' => '14',
        'Node.js v16 (LTS)' => '16',
        'Node.js v17 (Current)' => '17',
    ];

    public static array $composerVersions = [
        'Do not install' => '0',
        'Composer v1 (Latest 1.x)' => '1',
        'Composer v2 (Latest 2.2.x LTS)' => '2',
    ];

    public static function getServiceTypes(): array
    {
        return [
            self::TYPE_DATABASE,
            self::TYPE_MEMORY_STORE,
            self::TYPE_OTHER,
        ];
    }

    public static function setupPredefinedServices(): void
    {
        collect(config('services'))->each(static function (array $service): void {
            if ($service['service_name'] === 'servd' && self::where('service_name', 'servd')->exists()) {
                return;
            }

            self::updateOrCreate(
                [
                    'service_name' => $service['service_name'],
                    'version' => $service['version'],
                ],
                [
                    'type' => $service['type'],
                    'defined_by' => self::DEFINED_BY_APPLICATION,
                    'name' => $service['name'],
                    'description' => $service['description'] ?? null,
                    'port' => $service['port'],
                    'has_volume' => $service['has_volume'],
                    'should_build' => isset($service['should_build']) && $service['should_build'],
                    'service_folders' => $service['service_folders'] ?? null,
                    'single_stub' => $service['single_stub'] ?? false,
                    'available_versions' => isset(
                        $service['available_versions']
                    ) ? implode(',', $service['available_versions']) : null,
                ]
            );
        });
    }

    public static function getServiceChoices(string $type): array
    {
        return self::byType($type)
            ->distinct('service_name')
            ->get()
            ->mapWithKeys(function (Service $service): array {
                return [
                    $service->service_name => filled(
                        $service->description
                    ) ? $service->name . ' - ' . $service->description : $service->name,
                ];
            })->toArray();
    }

    public static function getPhpVersions(): array
    {
        return config('services')[0]['available_versions'];
    }

    public static function getPhpVersionChoices(): array
    {
        return collect(self::getPhpVersions())
            ->mapWithKeys(function (string $version): array {
                return [$version => $version];
            })->toArray();
    }

    public static function getNodeVersionChoices(): array
    {
        return collect(self::$nodeVersions)
            ->mapWithKeys(function (string $version, string $label): array {
                return [$version => $label];
            })->toArray();
    }

    public static function getComposerVersionChoices(): array
    {
        return collect(self::$composerVersions)
            ->mapWithKeys(function (string $version, string $label): array {
                return [$version => $label];
            })->toArray();
    }

    public static function getAvailableVersions(string $service): ?array
    {
        $definedService = collect(config('services'))->where('service_name', $service)->first();

        if ($definedService === null) {
            return null;
        }

        if (Arr::has($definedService, 'available_versions') === false) {
            return ['latest'];
        }

        return collect(Arr::get($definedService, 'available_versions'))->mapWithKeys(
            fn (string $version, $key): array => [is_int($key) ? $version : $key => $version]
        )->toArray();
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', 1);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->enabled;
    }
}
