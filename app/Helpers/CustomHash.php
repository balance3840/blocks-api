<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;

/**
 * Class CustomHash
 * @package App\Helpers
 */
class CustomHash
{
    /**
     * @param String $value value to encrypt.
     * @param int $rounds number of rounds. Default set to 12.
     * @return string value hashed.
     */
    public static function make(String $value, $rounds = 12) : String {
        return Hash::make($value, [
            'rounds' => $rounds
        ]);
    }

    /**
     * @param String $value plain text value.
     * @param String $hash hashed value
     * @return bool
     */
    public static function check(String $value, String $hash) : bool {
        return Hash::check($value, $hash);
    }
}
