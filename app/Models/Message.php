<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Message extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = false;
    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function (Model $model) {
    //         $model->setAttribute($model->getKeyName(), Uuid::uuid4());
    //     });
    // }
}
