<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiSecretValidateRule implements Rule
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
        $appId = request()->app_id;
        $secret = request()->secret;

        if (!$appId || !$secret) {
            return false;
        }


        $hashedSecret = DB::table('allowed_apps')->where(['app_id' => $appId])
            ->value('secret');

        if (!$secret) {
            return false;
        }

        return Hash::check($secret, $hashedSecret);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Credentials';
    }
}
