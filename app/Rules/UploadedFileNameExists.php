<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Runner\Extension\PharLoader;

class UploadedFileNameExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    private $disk;
    private $path;

    public function __construct( $path = '', $disk = 'public')
    {
        $this->disk = $disk;
        $this->path = $path;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $isFileExists = false;


        if (request()->hasFile($attribute)) {
            $uploadedFile = request()->file($attribute);

            if ($uploadedFile->isValid()) {
                $path = $this->path;
                $fileName = $uploadedFile->getClientOriginalName();
                $path = Str::endsWith( $path, '/') ? $path.$fileName : $path.'/'.$fileName;

                $isFileExists = Storage::disk($this->disk)
                    ->exists($path);
            }
        }
        if($isFileExists) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute with same name already exists, please choose a different name';
    }
}
