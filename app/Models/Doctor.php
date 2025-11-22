<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctors';
    protected $fillable = [
        'user_id',
        'crm',
        'specialty',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
