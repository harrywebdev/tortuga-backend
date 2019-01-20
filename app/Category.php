<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function productVariations()
    {
        return $this->hasManyThrough(ProductVariation::class, Product::class);
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
