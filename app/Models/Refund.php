<?php

namespace Modules\Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Wallets\Database\Factories\RefundFactory;

class Refund extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'transaction_id',
        'wallet_id',
        'total_amount',
        'status',

    ];

    protected static function newFactory(): RefundFactory
    {
        return RefundFactory::new();
    }

    /**
     * relation to  wallets
     */
    public function Wallets() : BelongsTo
    {
        return $this->belongsTo(Wallets::class);
    }

     /**
     * relation to  
     */
    public function transaction() : BelongsTo
    {
        return $this->belongsTo(transaction::class);
    }
}
