<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use SoftDeletes;

    protected $fillable = ['question', 'answer', 'admin_id'];

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];


    protected $casts = [
        'id' => 'integer',
        'question' => 'string',
        'answer' => 'string',
        'admin_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
