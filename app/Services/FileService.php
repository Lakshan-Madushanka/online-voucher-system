<?php


namespace App\Services;


use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileService
{
    public function storeFileWithOriginalName(
        Request $request,
        UploadedFile $file,
        string $storagePath
    ) {
        if ($file->isValid()) {
            $imageName = $file->getClientOriginalName();
            $path      = $request->file('image')
                ->storeAs($storagePath, $imageName, 'public');


            return $path;
        }

        return '';
    }

    public function updateFileWithOriginalName(
        Voucher $voucher,
        Request $request,
        UploadedFile $image,
        FileService $fileService,
        string $storagePath

    ) {
        $oldImagePath = $voucher->image;

        if ($this->isFileExists($oldImagePath) && isset($oldImagePath)) {
            $newFileName = $request->file('image')->getClientOriginalName();
            $tempPath = $storagePath.'/'.uniqid();
            $oldImageName = str_replace($storagePath.'/', '', $oldImagePath);

            if($newFileName !== $oldImageName) {
                if ($fileService->isFileExists($storagePath.'/'.$newFileName)) {
                    throw ValidationException::withMessages([
                        'file' => __('validation.fileExists')
                    ]);
                };
            }
            $fileService->delete($oldImagePath);
        }
        //store new image
        $path = $fileService
            ->storeFileWithOriginalName($request, $image, $storagePath);

        return $path;
    }

    public function delete(string $path)
    {
        Storage::disk('public')->delete($path);
    }

    public function isFileExists(string $path)
    {
        return Storage::disk('public')->exists($path);
    }

    public function rename(string $oldPath, string $newPath)
    {
        return Storage::disk('public')->move($oldPath, $newPath);
    }
}