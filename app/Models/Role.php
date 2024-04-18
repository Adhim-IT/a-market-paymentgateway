<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        // Tambahkan atribut lain yang ingin Anda masukkan di sini
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // Tambahkan atribut yang ingin disembunyikan dari array di sini
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // Tambahkan konversi tipe data di sini
    ];

    /**
     * Get the users associated with the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
