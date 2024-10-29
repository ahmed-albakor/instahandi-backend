<?php

namespace App\Services\Helper;

use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    public static function applyFilters(
        Builder $query,
        array $filters = [],
        array $searchFields = [],
        array $numericFields = [],
        array $dateFields = [],
        array $exactMatchFields = [],
        array $inFields = [],
        $defaultSortField = 'created_at',
        $sortOrder = 'desc'
    ) {
        $query = FilterService::applySearch($query, $filters['search'] ?? null, $searchFields);
        $query = FilterService::applyNumericFilters($query, $filters, $numericFields);
        $query = FilterService::applyDateFilters($query, $filters, $dateFields);
        $query = FilterService::applyExactMatchFilters($query, $filters, $exactMatchFields);
        $query = FilterService::applyInFilters($query, $filters, $inFields);

        $allowedSortFields = array_merge($searchFields, $numericFields, $dateFields, $exactMatchFields, $inFields);
        $sortField = in_array($filters['sortField'] ?? $defaultSortField, $allowedSortFields)
            ? ($filters['sortField'] ?? $defaultSortField)
            : $defaultSortField;


        return FilterService::applySorting($query, $sortField, $sortOrder)->paginate($filters['limit'] ?? 20);
    }

    protected static function applySearch(Builder $query, $search = null, array $fields = [])
    {
        if ($search) {
            $query->where(function ($q) use ($search, $fields) {
                foreach ($fields as $field) {
                    $q->orWhere($field, 'like', "%$search%");
                }
            });
        }
        return $query;
    }

    protected static function applyNumericFilters(Builder $query, array $filters, array $numericFields = [])
    {
        foreach ($numericFields as $field) {
            if (isset($filters["{$field}_min"])) {
                $query->where($field, '>=', $filters["{$field}_min"]);
            }
            if (isset($filters["{$field}_max"])) {
                $query->where($field, '<=', $filters["{$field}_max"]);
            }
        }
        return $query;
    }

    protected static function applyDateFilters(Builder $query, array $filters, array $dateFields = [])
    {
        foreach ($dateFields as $field) {
            if (isset($filters["{$field}_from"])) {
                $query->whereDate($field, '>=', $filters["{$field}_from"]);
            }
            if (isset($filters["{$field}_to"])) {
                $query->whereDate($field, '<=', $filters["{$field}_to"]);
            }
        }
        return $query;
    }

    protected static function applyExactMatchFilters(Builder $query, array $filters, array $exactMatchFields = [])
    {
        foreach ($exactMatchFields as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        return $query;
    }

    protected static function applyInFilters(Builder $query, array $filters, array $inFields = [])
    {
        foreach ($inFields as $field) {
            if (isset($filters[$field]) && is_array($filters[$field])) {
                $query->whereIn($field, $filters[$field]);
            }
        }
        return $query;
    }

    protected static function applySorting(Builder $query, $sortField, $sortOrder)
    {
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
        return $query->orderBy($sortField, $sortOrder);
    }
}
