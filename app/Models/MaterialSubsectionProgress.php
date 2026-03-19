<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialSubsectionProgress extends Model
{
    protected $table = 'material_subsection_progress';

    protected $fillable = [
        'material_subsection_id',
        'user_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function subsection(): BelongsTo
    {
        return $this->belongsTo(MaterialSubsection::class, 'material_subsection_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
