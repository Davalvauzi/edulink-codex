<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialSubsection extends Model
{
    protected $fillable = [
        'material_id',
        'title',
        'description',
        'image_path',
        'image_name',
        'image_url',
        'position',
        'created_by',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(MaterialSubsectionProgress::class, 'material_subsection_id');
    }

    public function getImageSourceAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return $this->image_url;
    }
}
