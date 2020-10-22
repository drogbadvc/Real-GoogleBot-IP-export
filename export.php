#!/usr/bin/env php
<?php

require('ip_whois.php');

use MIKRI\Net\WhoisInfo;

$whois = new WhoisInfo();

//The path to the large file.
$fileName = isset($argv[1]) ? $argv[1] : die(' *Add a file as the first parameter and the number of lines as the second');
$nbLines = isset($argv[2]) ? $argv[2] : 0;
$filecsv = 'googleip.csv';

//Open the file in "reading only" mode.
$fileHandle = fopen($fileName, "r");

//If we failed to get a file handle, throw an Exception.
if ($fileHandle === false) {
    throw new Exception('Could not get file handle for: ' . $fileName);
}

$arr_ip = [];
$i = 1;
//While we haven't reach the end of the file.
while (!feof($fileHandle) && $i <= $nbLines) {
    $i++;
    //Read the current line in.
    $line = fgets($fileHandle);
    if (strpos($line, 'Googlebot')) {
        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $line, $ip_match)) {
            $ip = $ip_match[0];
            $info = $whois->getWhoisInfo($ip);
            if (isset($info['info']['OrgTechName'][0])) {
                if ($info['info']['OrgTechName'][0] == 'Google LLC') {
                    $googleornot = '1';
                }
            }
            if (isset($info['info']['OrgName'][0])) {
                if ($info['info']['OrgName'][0] == 'Google LLC') {
                    $googleornot = '1';
                }
            }
            if (!isset($googleornot)) {
                $googleornot = '0';
            }
            if (preg_match('(\[[^]]+\])', $line, $date)) {
                $re = '/[\+[0-9]{5}|\[|\]|]/';
                $dt = preg_replace($re, '', $date[0]);
                $arr_ip[] = [$dt, $ip, $googleornot];
            }
        }
    }
    //Do whatever you want to do with the line.
}
//Finally, close the file handle.
fclose($fileHandle);

$handle = fopen($filecsv, 'a+');

foreach ($arr_ip as $fields) {
    fputcsv($handle, $fields);
}