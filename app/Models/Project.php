<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Project — rancangan ide proposal Agentic AI.
 *
 * @property int $id
 * @property int $user_id
 * @property string $judul
 * @property string|null $deskripsi
 * @property string $tema_agent
 * @property string $api_key_secure
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Kolom yang boleh diisi massal (mass assignment guard).
     *
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'deskripsi',
        'tema_agent',
        'api_key_secure',
        'user_id',
    ];

    /**
     * Kolom yang disembunyikan dari serialisasi JSON/array.
     *
     * @var list<string>
     */
    protected $hidden = [
        'api_key_secure',
    ];

    /**
     * Cast atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_key_secure' => 'encrypted',
        ];
    }

    /**
     * Relasi ke pemilik project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
