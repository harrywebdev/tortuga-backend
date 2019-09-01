<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Tortuga\CursorPaginator;

class CursorPaginationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Builder::macro('cursorPaginate', function ($limit, $columns) {
            $cursor = CursorPaginator::currentCursor();

            if ($cursor) {
                $apply = function ($query, $columns, $cursor) use (&$apply) {
                    $query->where(function ($query) use ($columns, $cursor, $apply) {
                        $column    = key($columns);
                        $direction = array_shift($columns);
                        $value     = array_shift($cursor);

                        // TODO: see if can  remove this conversion to Carbon altogether with (all is in UTC)
                        try {
                            $dateValue = Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $value);
                            $value     = $dateValue;
                        } catch (\Exception $e) {
                            //
                        }

                        $query->where($column, $direction === 'asc' ? '>' : '<', $value);

                        if (!empty($columns)) {
                            $query->orWhere($column, $value);
                            $apply($query, $columns, $cursor);
                        }
                    });
                };

                $apply($this, $columns, $cursor);
            }

            foreach ($columns as $column => $direction) {
                $this->orderBy($column, $direction);
            }

            $items = $this->limit($limit + 1)->get();

            // determine whether we reached the end of the items
            $isThereNoMoreToLoad = $items->count() <= $limit;

            // get rid of the extra item
            if (!$isThereNoMoreToLoad) {
                $items->pop();
            }

            // default is AFTER (with no cursor set)
            $requestDirection = CursorPaginator::isCursorBefore() ? 'before' : 'after';

            // determine previous and next cursors
            // 1) if the request is BEFORE (looking into the past with "DESC" direction
            //      then first record (the newest, e.g. 17:00) is "next" cursor,
            //      and last record (the oldest, e.g. 14:30) is "prev" cursor
            // 2) if the request is AFTER (looking into the future with "ASC" direction)
            //      then it's the other way around

            $prevCursor = null;
            $nextCursor = null;
            if ($items->count()) {
                // prevCursor
                $prevCursor = array_map(function ($column) use ($items, $requestDirection) {
                    return $requestDirection === 'before' ? $items->last()->{$column} : $items->first()->{$column};
                }, array_keys($columns));

                // nextCursor
                $nextCursor = array_map(function ($column) use ($items, $requestDirection) {
                    return $requestDirection === 'before' ? $items->first()->{$column} : $items->last()->{$column};
                }, array_keys($columns));
            }

            // if there is no more to load:
            // 1) if the request is BEFORE, then "prev" cursor is null (no more in the past)
            // 2) if the request is AFTER, then "next" cursor is null (no more in the future)
            // default is AFTER
            if ($isThereNoMoreToLoad) {
                return new CursorPaginator(
                    $items,
                    $requestDirection === 'after' ? null : $nextCursor,
                    $requestDirection === 'before' ? null : $prevCursor
                );
            }

            return new CursorPaginator($items, $nextCursor, $prevCursor);
        });
    }
}
