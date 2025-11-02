<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class CategoryFilter
{
    /**
     * Handle the incoming request.
     *
     * @param  Builder  $query
     * @param  Closure  $next
     * @return Builder
     */
    public function handle(Builder $query, Closure $next): Builder
    {
        if (request()->has('category')) {
            $category = request()->input('category');
            if (!empty($category)) {
                $query->filterByCategory($category);
            }
        }

        return $next($query);
    }
}