<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariation extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     */
    protected $guarded = ['id'];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class);
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
    public function getCurrencyFormattedAttribute()
    {
        return localeconv()['currency_symbol'];
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsActive(Builder $query)
    {
        return $query->where('active', '=', 1);
    }

    /**
     * @param Builder $query
     * @param string  $slug
     * @return Builder
     */
    public function scopeOfSlug(Builder $query, string $slug)
    {
        return $query->where('slug', '=', $slug);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('sequence', 'asc');
    }
}
