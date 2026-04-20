<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceFactory> */
    use HasFactory;

    protected $table = 'devices';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'specification_fields' => 'array',
            'include_operating_system' => 'boolean',
            'other_data_fields' => 'array',
        ];
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
