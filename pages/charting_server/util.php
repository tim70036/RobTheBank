<?php
# Some util functions
function replace_unicode_escape_sequence($match) 
{
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}
function unicode_decode($str) 
{
    return preg_replace_callback('/u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
}
function toTwTime($dateStr)
{
	$date = new DateTime($dateStr, new DateTimeZone('UTC'));
	$date->setTimezone(new DateTimeZone("Asia/Taipei"));
	return $date;
}

# Http response code function
if (!function_exists('http_response_code'))
{
    function http_response_code($newcode = NULL)
    {
        static $code = 200;
        if($newcode !== NULL)
        {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }       
        return $code;
    }
}
?>