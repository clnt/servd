<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Certificate extends Model
{
    use HasFactory;

    /** @var string[] */
    protected $fillable = [
        'project_id',
        'common_name',
        'container_path',
        'path',
        'expires',
    ];

    protected $dates = [
        'expires',
    ];

    public function fileExists(): bool
    {
        return File::exists($this->path);
    }
}
