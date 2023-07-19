<?php

class YandexCloudApi
{
    protected $token = 'y0_AgAAAAAN-K9DAAm3KAA12AAADhGaGI82NaryL2TbKJe-KBHnVbafWjkEI';
    protected $basicApiUrl = 'https://cloud-api.yandex.net/v1/disk/';

    /**
     * Method sendQueryYaDisk
     *
     * @param string $urlQuery URL для отправки запросов
     * @param array $arrQuery массив параметров
     * @param string $methodQuery метод отправки
     *
     * @return array
     */
    public function sendQueryYaDisk(string $methodAPI = '', array $arrQuery = [], string $methodQuery = 'GET'): array
    {
        if($methodQuery == 'POST') {
            $fullUrlQuery = $this->basicApiUrl . $methodAPI;
        } else {
            $fullUrlQuery = $this->basicApiUrl . $methodAPI . '?' . http_build_query($arrQuery);
        }

        $ch = curl_init($fullUrlQuery);
        switch ($methodQuery) {
            case 'PUT':
                curl_setopt($ch, CURLOPT_PUT, true);
                break;

            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
	            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrQuery));
                break;

            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: OAuth ' . $this->token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $resultQuery = curl_exec($ch);
        curl_close($ch);

        return (!empty($resultQuery)) ? json_decode($resultQuery, true) : [];
    }

    /**
     * Метод для загрузки файлов
     *
     * @param string $filePath путь до файла
     * @param string $dirPath путь до директории на Яндекс.Диск
     *
     * @return string
     */
    public function disk_resources_upload(string $filePath, string $dirPath = ''): string
    {
        $arrParams = [
            'path' => $dirPath . basename($filePath),
            'overwrite' => 'true',
        ];

        $urlQuery = 'https://cloud-api.yandex.net/v1/disk/resources/upload';
        $resultQuery = $this->sendQueryYaDisk('resources/upload', $arrParams);

        if (empty($resultQuery['error'])) {
            $fp = fopen($filePath, 'r');
        
            $ch = curl_init($resultQuery['href']);
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));
            curl_setopt($ch, CURLOPT_INFILE, $fp);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        
            return $http_code;
        } else {
            return $resultQuery['message'];
        }
    }
    
    /**
     * Метод для скачивания файлов на сервера
     *
     * @param string $filePath путь до файла в Яндекс.Диске
     * @param string $dirPath путь до директории на сервере
     *
     * @return array
     */
    public function disk_resources_download(string $filePath, string $dirPath = ''): array
    {
        $arrParams = [
            'path' => $filePath,
        ];
        $resultQuery = $this->sendQueryYaDisk('resources/download', $arrParams);

        if(empty($resultQuery['error'])) {
            $file_name = $dirPath . basename($filePath);
            $file = @fopen($file_name, 'w');
        
            $ch = curl_init($resultQuery['href']);
            curl_setopt($ch, CURLOPT_FILE, $file);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->token));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $resultQuery = curl_exec($ch);
            curl_close($ch);

            fclose($file);

            return [
                'message' => 'Файл успешно загружен',
                'path' => $file_name,
            ];
        } else {
            return $resultQuery;
        }
    }

}
