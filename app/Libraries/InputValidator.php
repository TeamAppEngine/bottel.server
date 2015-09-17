<?php
/**
 * Created by PhpStorm.
 * User: MohammadSadjad
 * Date: 2/24/2015
 * Time: 4:51 PM
 */

namespace App\Http\Libraries;

class InputValidator {

    /**
     * Validates to see if an email is valid or not
     *
     * @param $email        string a value of an email
     * @return bool         true    -> email is valid
     *                      false   -> email is not valid
     */
    public static function isEmailValid($email){

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }
}