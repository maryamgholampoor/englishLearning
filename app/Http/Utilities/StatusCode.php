<?php

namespace App\Http\Utilities;

trait StatusCode
{
    /**
     * @param $code
     * @return int
     * @throws Exception
     */
    public function getStatusCodeByCodeName($code)
    {
        switch ($code) {
            case 'Continue':
                $status_code = 100;
                break;
            case 'Switching Protocols':
                $status_code = 101;
                break;
            case 'OK':
                $status_code = 200;
                break;
            case 'Created':
                $status_code = 201;
                break;
            case 'Accepted':
                $status_code = 202;
                break;
            case 'Non-Authoritative Information':
                $status_code = 203;
                break;
            case 'No Content':
                $status_code = 204;
                break;
            case 'Reset Content':
                $status_code = 205;
                break;
            case 'Partial Content':
                $status_code = 206;
                break;
            case 'Multiple Choices':
                $status_code = 300;
                break;
            case 'Moved Permanently':
                $status_code = 301;
                break;
            case 'Moved Temporarily':
                $status_code = 302;
                break;
            case 'See Other':
                $status_code = 303;
                break;
            case 'Not Modified':
                $status_code = 304;
                break;
            case 'Use Proxy':
                $status_code = 305;
                break;
            case 'Bad Request':
                $status_code = 400;
                break;
            case 'Unauthorized':
                $status_code = 401;
                break;
            case 'Payment Required':
                $status_code = 402;
                break;
            case 'Forbidden':
                $status_code = 403;
                break;
            case 'Not Found':
                $status_code = 404;
                break;
            case 'Method Not Allowed':
                $status_code = 405;
                break;
            case 'Not Acceptable':
                $status_code = 406;
                break;
            case 'Proxy Authentication Required':
                $status_code = 407;
                break;
            case 'Request Time-out':
                $status_code = 408;
                break;
            case 'Conflict':
                $status_code = 409;
                break;
            case 'Gone':
                $status_code = 410;
                break;
            case 'Length Required':
                $status_code = 411;
                break;
            case 'Precondition Failed':
                $status_code = 412;
                break;
            case 'Request Entity Too Large':
                $status_code = 413;
                break;
            case 'Request-URI Too Large':
                $status_code = 414;
                break;
            case 'Unsupported Media Type':
                $status_code = 415;
                break;
            case 'Internal Server Error':
                $status_code = 500;
                break;
            case 'Not Implemented':
                $status_code = 501;
                break;
            case 'Bad Gateway':
                $status_code = 502;
                break;
            case 'Service Unavailable':
                $status_code = 503;
                break;
            case 'Gateway Time-out':
                $status_code = 504;
                break;
            case 'HTTP Version not supported':
                $status_code = 505;
                break;
            default:
                throw new \Exception('Unknown http status code "' . $code . '"');
                break;
        }
        return $status_code;
    }

    /**
     * @param $code
     * @return string
     * @throws Exception
     */
    public function getStatusCodeByCode($code)
    {
        switch ($code) {
            case 100:
                $code_name = 'Continue';
                break;
            case 101:
                $code_name = 'Switching Protocols';
                break;
            case 200:
                $code_name = 'OK';
                break;
            case 201:
                $code_name = 'Created';
                break;
            case 202:
                $code_name = 'Accepted';
                break;
            case 203:
                $code_name = 'Non-Authoritative Information';
                break;
            case 204:
                $code_name = 'No Content';
                break;
            case 205:
                $code_name = 'Reset Content';
                break;
            case 206:
                $code_name = 'Partial Content';
                break;
            case 300:
                $code_name = 'Multiple Choices';
                break;
            case 301:
                $code_name = 'Moved Permanently';
                break;
            case 302:
                $code_name = 'Moved Temporarily';
                break;
            case 303:
                $code_name = 'See Other';
                break;
            case 304:
                $code_name = 'Not Modified';
                break;
            case 305:
                $code_name = 'Use Proxy';
                break;
            case 400:
                $code_name = 'Bad Request';
                break;
            case 401:
                $code_name = 'Unauthorized';
                break;
            case 402:
                $code_name = 'Payment Required';
                break;
            case 403:
                $code_name = 'Forbidden';
                break;
            case 404:
                $code_name = 'Not Found';
                break;
            case 405:
                $code_name = 'Method Not Allowed';
                break;
            case 406:
                $code_name = 'Not Acceptable';
                break;
            case 407:
                $code_name = 'Proxy Authentication Required';
                break;
            case 408:
                $code_name = 'Request Time-out';
                break;
            case 409:
                $code_name = 'Conflict';
                break;
            case 410:
                $code_name = 'Gone';
                break;
            case 411:
                $code_name = 'Length Required';
                break;
            case 412:
                $code_name = 'Precondition Failed';
                break;
            case 413:
                $code_name = 'Request Entity Too Large';
                break;
            case 414:
                $code_name = 'Request-URI Too Large';
                break;
            case 415:
                $code_name = 'Unsupported Media Type';
                break;
            case 500:
                $code_name = 'Internal Server Error';
                break;
            case 501:
                $code_name = 'Not Implemented';
                break;
            case 502:
                $code_name = 'Bad Gateway';
                break;
            case 503:
                $code_name = 'Service Unavailable';
                break;
            case 504:
                $code_name = 'Gateway Time-out';
                break;
            case 505:
                $code_name = 'HTTP Version not supported';
                break;
            default:
                throw new \Exception('Unknown http status code "' . $code . '"');
                break;
        }
        return $code_name;
    }
}
