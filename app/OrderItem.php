<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function items()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return string
     */
    public function getPriceFormattedAttribute()
    {
        return money_format('%.0n', $this->price / 100);
    }

    /**
     * @return string
     */
    public function getTotalPriceFormattedAttribute()
    {
        return money_format('%.0n', $this->total_price / 100);
    }

    /**
     * @return string
     */
    public function getCurrencyFormattedAttribute()
    {
        return localeconv()['currency_symbol'];
    }
}
