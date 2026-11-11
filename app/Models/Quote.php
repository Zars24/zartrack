<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'name', 'company', 'email', 'phone', 'service_type', 'origin',
        'destination', 'cargo_details', 'message', 'status',
    ];
}
