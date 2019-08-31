<?php

namespace Tortuga;

use App\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                    'id'   => $slot->start()->timestamp,
                    'slot' => $slot->start()->format('Y-m-d\TH:i:s.u\Z'),
                ]);
            }
        }

        return $availableSlots;
    }

    /**
     * @param bool $allDaySlots
     * @return TimeslotCollection
     * @throws \Exception
     */
    private function _getOpeningHoursSlots($allDaySlots = false): TimeslotCollection
    {
        if (!$this->settings->get(SettingsName::IS_OPEN_FOR_BOOKING())) {
            throw new \Exception('Shop is closed - no slots.');
        }

        // midnight - 3am still counts as previous day
        $dayOfWeek = strtolower((Carbon::now()->hour <= 3 ? Carbon::yesterday() : Carbon::now())->englishDayOfWeek);

        $openingHours = $this->settings->get(SettingsName::OPENING_HOURS());
        if (!isset($openingHours[$dayOfWeek])) {
            Log::error('Invalid configuration of opening hours.',
                ['dayOfWeek' => $dayOfWeek, 'openingHours' => $openingHours]);
            throw new \Exception('Invalid configuration of opening hours.');
        }

        $hourSlots = $openingHours[$dayOfWeek];

        // testing stuff
        if (config('tortuga.debug_slots')) {
            $hourSlots = [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2];
        }

        // build all the slots
        $hourSlots = array_reduce($hourSlots, function ($acc, $item) {
            $carbon = Carbon::createFromTime($item, 0, 0);

            // firstly, if we are after midnight still in night hours,
            // we need to put the hours in the correct date - starting
            // with the earliest opening hours, that would mean yesterday
            if (Carbon::now()->hour <= 3) {
                $carbon = $carbon->subDay(1);
            }

            // midnight - 4am hours means its tomorrow's date
            if ($item <= 4) {
                $carbon = $carbon->addDay();
            }

            array_push($acc, new Timeslot($carbon, 0, 30));

            $carbon = $carbon->addMinutes(30);
            array_push($acc, new Timeslot($carbon, 0, 30));
            return $acc;
        }, []);

        if (!$allDaySlots) {
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
     * Checks whether order can be delayed to supplied slot
     * @param Carbon $orderTime
     * @return bool
     */
    public function isOpenForDelayedOrder(Carbon $orderTime): bool
    {
        // check if slot exists
        try {
            $openingHoursSlots = $this->_getOpeningHoursSlots(true);
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
}