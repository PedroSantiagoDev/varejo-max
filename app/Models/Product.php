<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = ['id', 'name', 'unit_price'];

    public $incrementing = false;

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
