<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number', 'type', 'brand', 'model', 'capacity_kg', 'status', 'notes',
    ];

    public function containers()
    {
        return $this->hasMany(Container::class);
    }
}
