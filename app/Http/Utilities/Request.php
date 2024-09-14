<?php

namespace App\Http\Utilities;

use DateTime;
use DateTimeZone;

trait Request
{
    /**
     * @param $mobile
     * @param $code
     * @return true
     * @throws \Exception
     */
    public function sendLoginCode($mobile, $code)
    {
        $url = env('SMS_PANEL_URL');
        $url = $url . "?" . "Token=" . env('SMS_TOKEN');
        $url = $url . "&" . "PatternValues=$mobile," . $code;
        $url = $url . "&" . "Mobile=$mobile";
        $url = $url . "&" . "PatternCodeID=2268";
        $json = file_get_contents($url);
        $result = json_decode($json);
        if ($result->Status == 'Failed') {
            throw new \Exception('The result of send otp code has error, data is: ' . $result->Data);
        }
        return true;
    }

    /**
     * @param $mobile
     * @param $text
     * @param $patternCodeId
     * @return bool
     */
    public function sendMessageRegisterCompleted($mobile, $text, $patternCodeId)
    {
        $url = env('SMS_PANEL_URL');
        $url = $url . "?" . "Token=" . env('SMS_TOKEN');
        $url = $url . "&" . "PatternValues=" . urlencode($text);
        $url = $url . "&" . "Mobile=$mobile";
        $url = $url . "&" . "PatternCodeID=$patternCodeId";
        $json = file_get_contents($url);
        $result = json_decode($json);
        if ($result->Status == 'Failed') {
            return false;
        }
        return true;
    }
}
