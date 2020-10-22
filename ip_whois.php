<?php

namespace MIKRI\Net;

/**
 * Получение информации с Whois серверов
 * Работоспособность проверена на PHP 7.3.9 (cli)
 *
 * @author Михаил Рыжков <2007mik007@mail.ru>
 */
class WhoisInfo
{
    /**
     * Основной whois сервер, предоставляющий whois
     * сервер с информацией о запрошенном доменном
     * имени или IP адресе
     *
     * @var string
     */
    private $_mainServer;

    /**
     * Текст ошибки
     *
     * @var string
     */
    private $_errorMsg = "";

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct()
    {
        $this->_mainServer = "whois.iana.org";
    }

    /**
     * Получить последнюю ошибку
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->_errorMsg;
    }

    /**
     * Выполняем сетевой запрос, получает текстовую
     * информацию в ответе
     *
     * @param string $server Сервер для подключения
     * @param int    $port   Порт для подключения
     * @param string $domain Доменное имя для проверки
     *
     * @return array
     */
    private function _getInfoFromTCP($server, $port, $domain)
    {
        $fp = @fsockopen($server, $port);
        if (!$fp) {
            $errorInf = \error_get_last();
            $this->_errorMsg = "Ошибка при попытке подключения к серверу "
                . $server . ":" . $port . " (" . $errorInf['type'] . ") "
                . $errorInf['message'] . " в файле " . $errorInf['file']
                . " строка " . $errorInf['line'];
            return false;
        }

        fputs($fp, $domain . "\r\n");
        $answer = "";
        while (!feof($fp)) {
            $answer .= fgets($fp, 128);
        }

        return $answer;
    }

    /**
     * Получает whois сервер с информацией о доменном имени
     *
     * @param string $ipOrDomain Доменное имя или IP адрес для проверки
     *
     * @return string
     */
    private function _getWhoisServer($ipOrDomain)
    {
        $whoisServer = "";

        $strAnswer = $this->_getInfoFromTCP($this->_mainServer, 43, $ipOrDomain);
        $ansArray = preg_split("/[\r\n|\n]/", $strAnswer);
        foreach ($ansArray as $ansStr) {
            $tmpArray = explode(":", $ansStr, 2);
            foreach ($tmpArray as $key => $val) {
                $tmpArray[$key] = trim($val);
            }

            if ($tmpArray[0] == "% Error") {
                $this->_errorMsg = $tmpArray[1];
            }

            if ($tmpArray[0] == "whois") {
                $whoisServer = $tmpArray[1];
                return $whoisServer;
            }
        }

        return $whoisServer;
    }

    /**
     * Получить информацию с whois сервера
     *
     * @param string $ipOrDomain Доменное имя или IP адрес для проверки
     *
     * @return array
     */
    public function getWhoisInfo($ipOrDomain)
    {
        $resArray = [];
        $whoisServer = $this->_getWhoisServer($ipOrDomain);
        if ($whoisServer == "") {
            return false;
        }

        $resArray['whois-server'] = $whoisServer;
        $resArray['info'] = [];
        $strAnswer = $this->_getInfoFromTCP($whoisServer, 43, $ipOrDomain);
        $ansArray = preg_split("/[\r\n|\n]/", $strAnswer);
        foreach ($ansArray as $ansStr) {
            $ansStr = explode("%", trim($ansStr))[0];
            if (!empty($ansStr)) {
                $tmpArray = explode(":", $ansStr, 2);
                foreach ($tmpArray as &$tmpStr) {
                    $tmpStr = trim($tmpStr);
                }

                if (!empty($tmpArray[0]) && (count($tmpArray) > 1)) {
                    $resArray['info'][$tmpArray[0]][] = $tmpArray[1];
                }
            }
        }

        return $resArray;
    }
}