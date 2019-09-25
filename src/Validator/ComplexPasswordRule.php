<?php
namespace Exceedone\Exment\Validator;

use Illuminate\Contracts\Validation\Rule;
use Exceedone\Exment\Providers\CustomUserProvider;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Model\PasswordHistory;

/**
 * ComplexPasswordRule
 */
class ComplexPasswordRule implements Rule
{
    public function __construct()
    {
    }

    /**
    * Check Validation
    *
    * @param  string  $attribute
    * @param  mixed  $value
    * @return bool
    */
    public function passes($attribute, $value)
    {
        if (is_null($value)) {
            return true;
        }

        $char_cnt = collect(['a-z', 'A-Z', '0-9', '^a-zA-Z0-9'])->filter(function($regstr) use($value) {
            return preg_match("/[$regstr]+/", $value);
        })->count();

        if($char_cnt < 3){
            return false;
        }

        if(strlen($value) < 12){
            return false;
        }

        return true;
    }

    /**
     * get validation error message
     *
     * @return string
     */
    public function message()
    {
        return exmtrans('error.complex_password');
    }
}
