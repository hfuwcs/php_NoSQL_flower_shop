<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class PriceRangeFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        $priceMin = (float) request()->input('price_min', 0);
        $priceMax = (float) request()->input('price_max', 0);

        if ($priceMin > 0 || $priceMax > 0) {
             $query->filterByPriceRange($priceMin, $priceMax);
        }
       
        return $next($query);
    }
}