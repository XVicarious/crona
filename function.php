<?php
function validateLogin($pass, $hashedPass, $salt, $hashMethod = 'sha1')
{
    if (function_exists('hash') && in_array($hashMethod, hash_algos())) {
        return ($hashedPass === hash($hashMethod, $salt . $pass));
    }
    return ($hashedPass === sha1($salt . $pass));
}
function randomSalt($len = 8)
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()_+-=";
    $stringLength = strlen($chars) - 1;
    $str = '';
    for ($i = 0; $i < $len; ++$i) {
        $str .= $chars[rand(0, $stringLength)];
    }
    return $str;
}
function createHash($string, $hashMethod = 'sha1', $saltLength = 8)
{
    $salt = randomSalt($saltLength);
    if (function_exists('hash') && in_array($hashMethod, hash_algos())) {
        return hash($hashMethod, $salt . $string);
    }
    return sha1($salt . $string);
}
function randomString()
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $stringLength = strlen($chars) - 1;
    $str = '';
    for ($i = 0; $i < 32; ++$i) {
        $str .= $chars[rand(0, $stringLength)];
    }
    return $str;
}
