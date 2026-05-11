<?php

namespace Modules\Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Wallets\Database\Factories\PromotionFactory;

class promotion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): PromotionFactory
    // {
    //     // return PromotionFactory::new();
    // }
}
