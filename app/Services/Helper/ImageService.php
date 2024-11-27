<?php

namespace App\Services\Helper;

use App\Models\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    private static function MakeFolder($folderName)
    {
        $pathFolder = storage_path(sprintf('app/public/%s', $folderName));
        if (!File::isDirectory($pathFolder)) {
            File::makeDirectory($pathFolder);
        }
    }

    public static function storeImage($image, $folder, $name = null)
    {
        self::MakeFolder($folder);
        $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
        if ($name) {
            $imageName = $name . '-' . $imageName;
        }
        // $imageName = $name != null ? $name : uniqid() . '.' . $image->getClientOriginalExtension();
        $new_path = storage_path(sprintf('app/public/%s/%s', $folder, $imageName));
        move_uploaded_file($image, $new_path);
        return sprintf('%s/%s', $folder, $imageName);
    }

    public static function updateImage($image, $folder, $oldImageName): string|null
    {
        return Storage::delete("public/" . $oldImageName) ? ImageService::storeImage($image, $folder) : null;
    }

    public static function removeImages($ids)
    {
        if (!is_array($ids)) {
            abort([
                'success' => false,
                'message' => 'يجب تقديم معرفات الصور كمصفوفة غير فارغة.',
            ]);
        }

        $existingImages = Image::whereIn('id', $ids)->get();

        if (count($ids) !== $existingImages->count()) {
            abort([
                'success' => false,
                'message' => 'بعض الصور المطلوب حذفها غير موجودة.',
            ]);
        }

        foreach ($existingImages as $image) {
            // if (Storage::exists("public/" . $image->getRawOriginal('path'))) {
            //     Storage::delete("public/" . $image->getRawOriginal('path'));
            // }
            $image->delete();
        }
    }
}
