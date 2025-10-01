<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = ['id', 'name'];

    public $incrementing = false;

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
