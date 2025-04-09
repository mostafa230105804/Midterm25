<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products'; // Ensure this matches your database table name

    protected $fillable = [
        'code',
        'name',
        'price',
        'model',
        'description',
        'photo',
        'stock',
        'available_items',
    ];

    public function purchasers()
    {
        return $this->belongsToMany(User::class, 'purchases')
                    ->withPivot('created_at', 'updated_at');
    }

    public function buyers()
    {
        return $this->belongsToMany(User::class, 'purchases')
                    ->withPivot('quantity', 'created_at')
                    ->withTimestamps();
    }
}