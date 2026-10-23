<?php

namespace App\Models;

use App\Enums\StatusKehadiran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kehadiran extends Model
{
    protected $table = 'kehadiran';

    protected $fillable = [
        'pertemuan_id',
        'kelas_id',
        'mahasiswa_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusKehadiran::class,
        ];
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
