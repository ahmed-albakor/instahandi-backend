<?php

namespace App\Services\Helper;

class ResponseService
{
    public static function meta($collection)
    {
        return
            [
                'current_page' => $collection->currentPage(),
                'last_page' => $collection->lastPage(),
                'per_page' => $collection->perPage(),
                'total' => $collection->total(),
            ];
    }
}
