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
            File::makeDirectory($pathFolder, 0755, true, true);
        }
    }


    public static function storeImage($image, $folder, $name = null)
    {
        if (!$image || !$image->isValid()) {
            abort(response()->json(
                [
                    'success' => false,
                    'message' => 'Invalid file provided.',
                ],
                422
            ));
        }

        $imageName = $name ? $name . '-' . uniqid() . '.' . $image->getClientOriginalExtension()
            : uniqid() . '.' . $image->getClientOriginalExtension();

        $path = $image->storeAs($folder, $imageName, 'public');

        return $path;
    }




    public static function updateImage($image, $folder, $oldImageName): string|null
    {
        return Storage::delete("public/" . $oldImageName) ? ImageService::storeImage($image, $folder) : null;
    }

    public static function removeImages($ids)
    {
        if (!is_array($ids)) {
            abort(
                response()->json([
                    'success' => false,
                    'message' =>  'يجب تقديم معرفات الصور كمصفوفة غير فارغة.',
                ], 422),
            );
        }

        $existingImages = Image::whereIn('id', $ids)->get();

        if (count($ids) !== $existingImages->count()) {
            abort(
                response()->json([
                    'success' => false,
                    'message' => 'بعض الصور المطلوب حذفها غير موجودة.',
                ], 422),
            );
        }



        foreach ($existingImages as $image) {
            // if (Storage::exists("public/" . $image->getRawOriginal('path'))) {
            //     Storage::delete("public/" . $image->getRawOriginal('path'));
            // }
            $image->delete();
        }
    }
}
