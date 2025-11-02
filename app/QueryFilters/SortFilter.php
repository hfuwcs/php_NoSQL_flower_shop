<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class SortFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        if (request()->has('sort_by')) {
            match (request()->input('sort_by')) {
                'price_asc'  => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                default      => $query->orderBy('created_at', 'desc'),
            };
        } else {
            // Sắp xếp mặc định
            $query->orderBy('created_at', 'desc');
        }

        return $next($query);
    }
}