<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patients';

    protected $fillable = [
        'user_id',
        'birth_date',
        'address',
        'cpf',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
