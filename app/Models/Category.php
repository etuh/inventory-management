<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'specification_fields' => 'array',
        ];
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
