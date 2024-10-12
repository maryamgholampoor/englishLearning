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
        $url = "https://portal.amootsms.com/rest/SendSimple";

        $url = $url."?"."Token=".urlencode("8AADD086A29A2C589E380BE2D9BE20822D403B38");
        $nowIran = new DateTime('now', new DateTimeZone('IRAN'));
        $url = $url."&"."SendDateTime=".urlencode($nowIran->format('c'));

        $url = $url."&"."SMSMessageText=".urlencode("پیامک تستی من");
        $url = $url."&"."LineNumber=public";

        $url = $url."&"."Mobiles=09038231952";

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
