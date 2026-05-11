<?php

namespace Modules\Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Wallets\Database\Factories\SecurityFactory;

class security extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): SecurityFactory
    // {
    //     // return SecurityFactory::new();
    // }
}
