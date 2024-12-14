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
                    if (strpos($field, '.') !== false) {
                        // Handle relationship fields
                        [$relation, $relationField] = explode('.', $field, 2);
                        $q->orWhereHas($relation, function ($query) use ($relationField, $search) {
                            $query->where($relationField, 'like', "%$search%");
                        });
                    } else {
                        // Handle direct fields
                        $q->orWhere($field, 'like', "%$search%");
                    }
                }
            });
        }
        return $query;
    }

    protected static function applyNumericFilters(Builder $query, array $filters, array $numericFields = [])
    {
        foreach ($numericFields as $field) {
            if (strpos($field, '.') !== false) {
                [$relation, $relationField] = explode('.', $field, 2);
                if (isset($filters["{$field}_min"])) {
                    $query->whereHas($relation, function ($q) use ($relationField, $filters, $field) {
                        $q->where($relationField, '>=', $filters["{$field}_min"]);
                    });
                }
                if (isset($filters["{$field}_max"])) {
                    $query->whereHas($relation, function ($q) use ($relationField, $filters, $field) {
                        $q->where($relationField, '<=', $filters["{$field}_max"]);
                    });
                }
            } else {
                if (isset($filters["{$field}_min"])) {
                    $query->where($field, '>=', $filters["{$field}_min"]);
                }
                if (isset($filters["{$field}_max"])) {
                    $query->where($field, '<=', $filters["{$field}_max"]);
                }
            }
        }
        return $query;
    }

    protected static function applyDateFilters(Builder $query, array $filters, array $dateFields = [])
    {
        foreach ($dateFields as $field) {
            if (strpos($field, '.') !== false) {
                [$relation, $relationField] = explode('.', $field, 2);
                if (isset($filters["{$field}_from"])) {
                    $query->whereHas($relation, function ($q) use ($relationField, $filters, $field) {
                        $q->whereDate($relationField, '>=', $filters["{$field}_from"]);
                    });
                }
                if (isset($filters["{$field}_to"])) {
                    $query->whereHas($relation, function ($q) use ($relationField, $filters, $field) {
                        $q->whereDate($relationField, '<=', $filters["{$field}_to"]);
                    });
                }
            } else {
                if (isset($filters["{$field}_from"])) {
                    $query->whereDate($field, '>=', $filters["{$field}_from"]);
                }
                if (isset($filters["{$field}_to"])) {
                    $query->whereDate($field, '<=', $filters["{$field}_to"]);
                }
            }
        }
        return $query;
    }

    protected static function applyExactMatchFilters(Builder $query, array $filters, array $exactMatchFields = [])
    {
        foreach ($exactMatchFields as $field) {
            if (strpos($field, '.') !== false) {
                [$relation, $relationField] = explode('.', $field, 2);
                if (isset($filters[$field])) {
                    $query->whereHas($relation, function ($q) use ($relationField, $filters, $field) {
                        $q->where($relationField, $filters[$field]);
                    });
                }
            } else {
                if (isset($filters[$field])) {
                    $query->where($field, $filters[$field]);
                }
            }
        }
        return $query;
    }

    protected static function applyInFilters(Builder $query, array $filters, array $inFields = [])
    {
        foreach ($inFields as $field) {
            $inKey = "in_{$field}";
            $notInKey = "not_in_{$field}";

            // Handle 'in' filters
            if (isset($filters[$inKey]) && is_array($filters[$inKey])) {
                if (strpos($field, '.') !== false) {
                    [$relation, $relationField] = explode('.', $field, 2);
                    $query->whereHas($relation, function ($q) use ($relationField, $filters, $inKey) {
                        $q->whereIn($relationField, $filters[$inKey]);
                    });
                } else {
                    $query->whereIn($field, $filters[$inKey]);
                }
            }

            // Handle 'not_in' filters
            if (isset($filters[$notInKey]) && is_array($filters[$notInKey])) {
                if (strpos($field, '.') !== false) {
                    [$relation, $relationField] = explode('.', $field, 2);
                    $query->whereHas($relation, function ($q) use ($relationField, $filters, $notInKey) {
                        $q->whereNotIn($relationField, $filters[$notInKey]);
                    });
                } else {
                    $query->whereNotIn($field, $filters[$notInKey]);
                }
            }
        }

        return $query;
    }


    protected static function applySorting(Builder $query, $sortField, $sortOrder)
    {
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
        if (strpos($sortField, '.') !== false) {
            [$relation, $relationField] = explode('.', $sortField, 2);
            $query->with([$relation => function ($q) use ($relationField, $sortOrder) {
                $q->orderBy($relationField, $sortOrder);
            }]);
        } else {
            $query->orderBy($sortField, $sortOrder);
        }
        return $query;
    }
}
