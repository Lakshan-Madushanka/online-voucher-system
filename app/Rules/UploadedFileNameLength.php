<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UploadedFileNameLength implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $uploadedFleLength = 0;

        if (request()->hasFile($attribute)) {
            $uploadedFile = request()->file($attribute);

            if ($uploadedFile->isValid()) {
                $uploadedFleLength
                    = strlen($uploadedFile->getClientOriginalName());
            }
        }

        if ($uploadedFleLength === 0 || $uploadedFleLength > 25) {
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
        return ':attribute name length should less than or equal 25.';
    }
}
