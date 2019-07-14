<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Tortuga\Order\OrderStatus;

class Order extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $casts = [
        'order_time'  => 'datetime:Y-m-d H:i:s',
        'is_takeaway' => 'boolean',
    ];

    /**
     * {@inheritDoc}
     */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrderedByTime(Builder $query)
    {
        return $query->orderBy('order_time', 'asc');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeFromNow(Builder $query)
    {
        return $query->where('order_time', '>=', Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s'));
    }

    /**
     * @param Builder $query
     * @param Carbon  $orderTime
     * @return Builder
     */
    public function scopeAreBlockingSlot(Builder $query, Carbon $orderTime)
    {
        return $query->where('order_time', '=', $orderTime)
            ->whereIn('status', [OrderStatus::RECEIVED(), OrderStatus::ACCEPTED(), OrderStatus::PROCESSING()]);
    }
}
