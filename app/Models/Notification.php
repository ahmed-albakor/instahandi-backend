<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'creator_id',
        'image',
    ];


    protected $casts = [
        'id' => 'integer',
        'type' => 'string',
        'title' => 'string',
        'message' => 'string',
        'data' => 'array',
        'creator_id' => 'integer',
        'image' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
