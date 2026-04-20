<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'operating_system' => 'array',
            'specifications' => 'array',
            'other_data' => 'array',
            'accessory' => 'array',
        ];
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
