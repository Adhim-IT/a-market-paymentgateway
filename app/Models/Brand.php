<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'is_active',
    ];

    // Metode untuk mengatur nilai slug secara otomatis saat membuat model
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            // Jika nilai slug belum ditentukan, atur dari nama merek
            if (!$brand->slug) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

