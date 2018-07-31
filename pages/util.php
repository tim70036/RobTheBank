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

# Set time to valid trading session 9:00~13:30
function checkRecordTime($timeStr)
{
    # Validation, prevent shit time
    $dateObj = DateTime::createFromFormat('d.m.Y H:i', "10.10.2010 " . $timeStr);
    if ( !($dateObj !== false && $dateObj && $dateObj->format('G') == intval($timeStr)) )
    {
        # Set it to the start time of trading session, if it is invalid time
        $timeStr = "09:00";
    }

    $hour = intval(substr($timeStr, 0 , 2));
    $min = intval(substr($timeStr, 3, 2));

    # Validation, trading session is from 9:00 ~ 13:30
    if($hour > 13)
        $timeStr = "13:30";
    else if($hour < 9)
        $timeStr = "09:00";
    else if($hour === 13 && $min > 30)
        $timeStr = "13:30";

    return $timeStr;
}

# Check the upload file for safety
function checkFileUpload($filename, &$errorMsg)
{
    try {
        
        //var_dump($_FILES[$filename]);

        # Undefined | Multiple Files | $_FILES Corruption Attack
        # If this request falls under any of them, treat it invalid.
        if (
            !isset($_FILES[$filename]['error']) ||
            is_array($_FILES[$filename]['error'])
        ) {
            throw new RuntimeException('Invalid parameters.');
        }

        # Check $_FILES[$filename]['error'] value.
        switch ($_FILES[$filename]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }

        # You should also check filesize here. 
        if ($_FILES[$filename]['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }

        # DO NOT TRUST $_FILES[$filename]['mime'] VALUE !!
        # Check MIME Type by yourself.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        //var_dump($finfo->file($_FILES[$filename]['tmp_name']));
        if (false === $ext = array_search(
            $finfo->file($_FILES[$filename]['tmp_name']),
            array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'csv' => 'text/plain'
            ),
            true
        )) {
            throw new RuntimeException('Invalid file format.');
        }

        # You should name it uniquely.
        # DO NOT USE $_FILES[$filename]['name'] WITHOUT ANY VALIDATION !!
        # On this example, obtain safe unique name from its binary data.
        // if (!move_uploaded_file(
        //     $_FILES[$filename]['tmp_name'],
        //     sprintf('./uploads/%s.%s',
        //         sha1_file($_FILES[$filename]['tmp_name']),
        //         $ext
        //     )
        // )) {
        //     throw new RuntimeException('Failed to move uploaded file.');
        // }

        // echo 'File is uploaded successfully.';

    } catch (RuntimeException $e) {

        $errorMsg =  $e->getMessage();
        return false;
    }
    return true;
}

# Directly change the all element of an array, big5 -> utf8
function arrayBig5ToUtf8(&$array) 
{
    foreach( $array as &$value)
     {
        if (is_array($value)) 
        {
            arrayBig5ToUtf8($value);
        } else
        {
            $value = mb_convert_encoding($value, "UTF-8", "BIG5");
        }
    }
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