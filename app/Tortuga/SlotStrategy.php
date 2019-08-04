<?php

namespace Tortuga;

use App\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Timeslot\Timeslot;
use Timeslot\TimeslotCollection;
use Tortuga\Order\OrderStatus;

class SlotStrategy
{
    /**
     * @var AppSettings
     */
    private $settings;

    /**
     * SlotStrategy constructor.
     * @param AppSettings $settings
     */
    public function __construct(AppSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Collection
     */
    public function getAvailableSlots(): Collection
    {
        try {
            $openingHoursSlots = $this->_getOpeningHoursSlots();
        } catch (\Exception $e) {
            return Collection::make([]);
        }

        // get counts in the slots range and check number of orders
        // slots with more than 3 orders are not available
        $ordersBySlots = DB::table('orders')
            ->select(DB::raw('COUNT(id) AS count, order_time'))
            ->where('order_time', '>=', $openingHoursSlots->start()->toDateTimeString())
            ->where('order_time', '<=', $openingHoursSlots->end()->toDateTimeString())
            ->whereIn('status', [OrderStatus::RECEIVED(), OrderStatus::ACCEPTED(), OrderStatus::PROCESSING()])
            ->groupBy('order_time')
            ->pluck('count', 'order_time');

        $availableSlots = Collection::make([]);
        foreach ($openingHoursSlots as $slot) {
            $slotString = $slot->start()->toDateTimeString();

            // check if slo is free (zero or less than max orders in the slot)
            if (!isset($ordersBySlots[$slotString]) ||
                $ordersBySlots[$slotString] < $this->settings->get(SettingsName::MAX_ORDERS_PER_SLOT())
            ) {
                $availableSlots->add([
                    'id'       => $slot->start()->timestamp,
                    'datetime' => $slotString,
                    'slot'     => $slot->start()->format('H:i'),
                ]);
            }
        }

        return $availableSlots;
    }

    /**
     * @return TimeslotCollection
     * @throws \Exception
     */
    private function _getOpeningHoursSlots(): TimeslotCollection
    {
        if (!$this->settings->get(SettingsName::IS_OPEN_FOR_BOOKING())) {
            throw new \Exception('Shop is closed - no slots.');
        }

        // midnight - 3am still counts as previous day
        $dayOfWeek = (Carbon::now()->hour <= 3 ? Carbon::yesterday() : Carbon::now())->dayOfWeekIso;
        $hourSlots = [];

        // FIX: hardcoded opening hours
        switch ($dayOfWeek) {
            // Su, Mo closed
            case 7:
            case 1:
                break;
            // Tu, We, Th
            case 2:
            case 3:
            case 4:
                $hourSlots = [11, 12, 17, 18, 19, 20];
                break;
            // Fr
            case 5:
                $hourSlots = [11, 12, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2];
                break;
            // Sa
            case 6:
                $hourSlots = [18, 19, 20, 21, 22, 23];
                break;
        }

        // testing stuff
        if (env('APP_ENV') === 'local') {
            $hourSlots = [11, 12, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2];
        }

        // build all the slots
        $hourSlots = array_reduce($hourSlots, function ($acc, $item) {
            $carbon = Carbon::createFromTime($item, 0, 0);

            // midnight - 4am hours means its tomorrow's date
            if ($item <= 4) {
                $carbon = $carbon->addDay();
            }

            array_push($acc, new Timeslot($carbon, 0, 30));

            $carbon = $carbon->addMinutes(30);
            array_push($acc, new Timeslot($carbon, 0, 30));
            return $acc;
        }, []);

        // filter old slots
        // addMinutes: 30 minutes buffer from now to available order time
        // ceilMinute: 30 minutes rounding for nearest slot
        /** @var Carbon $nearestSlotTime */
        $nearestSlotTime = Carbon::now()->addMinutes(30)->ceilMinute(30);

        $hourSlots = array_filter($hourSlots, function (Timeslot $timeslot) use ($nearestSlotTime) {
            return $nearestSlotTime->diffInSeconds($timeslot->start(), false) >= 0;
        });

        if (!count($hourSlots)) {
            throw new \Exception('No slots found.');
        }

        $slotCollection = new TimeslotCollection(array_shift($hourSlots));

        foreach ($hourSlots as $slot) {
            $slotCollection->add($slot);
        }

        return $slotCollection;
    }

    /**
     * Check for specific slot (cheaper than `getAvailableSlots()`
     *
     * @param Carbon $orderTime
     * @return bool
     */
    public function isSlotAvailable(Carbon $orderTime): bool
    {
        // check for overloading
        if (Order::areBlockingSlot($orderTime)->count() >= $this->settings->get(SettingsName::MAX_ORDERS_PER_SLOT())) {
            return false;
        }

        // check if slot exists
        try {
            $openingHoursSlots = $this->_getOpeningHoursSlots();
        } catch (\Exception $e) {
            return false;
        }

        // check if desired slot even exists
        foreach ($openingHoursSlots as $openingHoursSlot) {
            if ($openingHoursSlot->start()->equalTo($orderTime)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $shortString HH:MM format
     * @return Carbon
     */
    public function createOrderTimeFromShortString($shortString): Carbon
    {
        $orderTime = Carbon::now();
        list ($orderTimeHour, $orderTimeMinutes) = explode(':', $shortString);

        // add day if *now* is not after midnight but the order time is
        // (the order time slot should be with the correct date)
        // TODO: threshold: 3am (should be the last opening hour if it's next day)
        if ($orderTime->hour > 3 && (int)$orderTimeHour <= 3) {
            $orderTime->addDay();
        }

        // set correct date for order time
        $orderTime->hour   = (int)$orderTimeHour;
        $orderTime->minute = (int)$orderTimeMinutes;
        $orderTime->second = 0;

        return $orderTime;
    }
}