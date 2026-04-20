<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory;

    protected $guarded = [];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
