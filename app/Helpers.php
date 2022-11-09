<?php

/*
 * custom helpers
 */
if (!function_exists('mb_str_pad')) {
    function mb_str_pad($input, $pad_string, $pad_length, $replace_pad_string = " ", $encoding = "UTF-8")
    {
        $before = str_repeat($pad_string, $input);
        $mb_length = mb_strlen($before, $encoding);
        return $before . str_repeat($replace_pad_string, $pad_length - $mb_length);
    }
}

if (!function_exists('outPutCsv')) {
    function outPutCsv(Array $lists, Array $header = [], $fileName = '')
    {
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($lists, $header) {
            $stream = fopen('php://output', 'w');
            stream_filter_prepend($stream, 'convert.iconv.utf-8/cp932//TRANSLIT');
            if (!empty($header)) {
                fputcsv($stream, $header);
            }
            foreach ($lists as $list) {
                fputcsv($stream, $list);
            };
            fclose($stream);
        });
        $fileName = (empty($fileName)) ? date('ymd') : $fileName;
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName . '.csv');
        return $response;
    }
}
