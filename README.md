# yandex-cloud-api-php
Класс для работы с API Яндекс.Диск на PHP и Curl. Данный класс позволяет работать с добавлением, удалением и получением файлав с Яндекс диска.

Курс работе с API Яндекс.Диск - https://prog-time.ru/course_cat/yandeks-disk-api-php/

<!-- wp:heading -->
<h2 class="wp-block-heading">Класс для работы с Яндекс.Диск через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>
class YandexCloudApi
{
    protected $token = 'test_123';
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
    public function sendQueryYaDisk(string $methodAPI = '', array $arrQuery = &#91;], string $methodQuery = 'GET'): array
    {
        if($methodQuery == 'POST') {
            $fullUrlQuery = $this-&gt;basicApiUrl . $methodAPI;
        } else {
            $fullUrlQuery = $this-&gt;basicApiUrl . $methodAPI . '?' . http_build_query($arrQuery);
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

        curl_setopt($ch, CURLOPT_HTTPHEADER, &#91;'Authorization: OAuth ' . $this-&gt;token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $resultQuery = curl_exec($ch);
        curl_close($ch);

        return (!empty($resultQuery)) ? json_decode($resultQuery, true) : &#91;];
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
        $arrParams = &#91;
            'path' =&gt; $dirPath . basename($filePath),
            'overwrite' =&gt; 'true',
        ];

        $urlQuery = 'https://cloud-api.yandex.net/v1/disk/resources/upload';
        $resultQuery = $this-&gt;sendQueryYaDisk('resources/upload', $arrParams);

        if (empty($resultQuery&#91;'error'])) {
            $fp = fopen($filePath, 'r');
        
            $ch = curl_init($resultQuery&#91;'href']);
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
            return $resultQuery&#91;'message'];
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
        $arrParams = &#91;
            'path' =&gt; $filePath,
        ];
        $resultQuery = $this-&gt;sendQueryYaDisk('resources/download', $arrParams);

        if(empty($resultQuery&#91;'error'])) {
            $file_name = $dirPath . basename($filePath);
            $file = @fopen($file_name, 'w');
        
            $ch = curl_init($resultQuery&#91;'href']);
            curl_setopt($ch, CURLOPT_FILE, $file);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this-&gt;token));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $resultQuery = curl_exec($ch);
            curl_close($ch);

            fclose($file);

            return &#91;
                'message' =&gt; 'Файл успешно загружен',
                'path' =&gt; $file_name,
            ];
        } else {
            return $resultQuery;
        }
    }
    

}</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Инициализация класса</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$yaClass = new YandexCloudApi();</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Получения общей информации об аккаунте Яндекс.Диска через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$resultQuery = $yaClass-&gt;sendQueryYaDisk();</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Получение метаинформации о папках и файлах на Яндекс.Диске через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'path' =&gt; '/uploads',
    'fields' =&gt; 'name,_embedded.items.path',
    'limit' =&gt; 100,
    'offset' =&gt; 0,
    'preview_crop' =&gt; false,
    'preview_size' =&gt; '',
    'sort' =&gt; 'created'
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources', $arrParams);</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Получение метаинформации о папках и файлах в корзине на Яндекс.Диске через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'path' =&gt; '/',
    'fields' =&gt; 'name,_embedded.items.path',
    'limit' =&gt; 100,
    'offset' =&gt; 0,
    'preview_crop' =&gt; false,
    'preview_size' =&gt; '',
    'sort' =&gt; 'created'
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('trash/resources', $arrParams);</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Получение плоского списка всех файлов с Яндекс.Диска через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'limit' =&gt; 100,
    'media_type' =&gt; 'image',
    'offset' =&gt; 0,
    'fields' =&gt; 'name,_embedded.items.path',
    'preview_size' =&gt; '',
    'preview_crop' =&gt; false,
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources/files', $arrParams);</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Получение последних загруженных элементов на Яндекс.Диск через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'limit' =&gt; 10,
    'media_type' =&gt; 'image',
    'fields' =&gt; 'name,_embedded.items.path',
    'preview_size' =&gt; '',
    'preview_crop' =&gt; false,
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources/last-uploaded', $arrParams);</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Метод для создания директории на Яндекс.Диске через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'path' =&gt; '/uploads/prog_time',
    'fields' =&gt; 'name,_embedded.items.path',
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources', $arrParams, 'PUT');</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Метод для загрузки файлов на Яндекс.Диск через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$filePath = $_SERVER&#91;'DOCUMENT_ROOT'] . '/public/image.png';
$dirPath = '/uploads';
$resultQuery = $yaClass-&gt;disk_resources_upload($filePath, $dirPath);</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Метод для скачивания файлов с Яндекс.Диска на сервера через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$filePath = '/test.docx';
$dirPath = $_SERVER&#91;'DOCUMENT_ROOT'] . '/public';
$resultQuery = $yaClass-&gt;disk_resources_download($filePath, $dirPath);</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Удаление ресурса с Яндекс.Диск через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'path' =&gt; '/test.docx',
    'permanently' =&gt; false,
    'fields' =&gt; 'name,_embedded.items.path',
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources', $arrParams, 'DELETE');</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Публикация файла в Яндекс.Диске через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'path' =&gt; '/uploads/test.xlsx',
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources/publish', $arrParams, 'PUT');</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Снятие с публикации файла в Яндекс.Диске через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'path' =&gt; '/uploads/test.xlsx',
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources/unpublish', $arrParams, 'PUT');</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Получение списка публичных файлов с Яндекс.Диска через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'limit' =&gt; 10,
    'offset' =&gt; 0,
    'type' =&gt; 'dir',
    'fields' =&gt; 'name,_embedded.items.path',
    'preview_size' =&gt; '',
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('resources/public', $arrParams);</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Метод для востановления файла из корзины в Яндекс.Диске через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$arrParams = &#91;
    'path' =&gt; 'trash:/test.docx_f8fb153e7cb73695ee2fdada79a7871b0093596e',
    'name' =&gt; 'new_name.docx'
];
$resultQuery = $yaClass-&gt;sendQueryYaDisk('trash/resources/restore', $arrParams, 'PUT');</code></pre>
<!-- /wp:code -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Очистка корзины в Яндекс.Диске через API</h2>
<!-- /wp:heading -->

<!-- wp:code -->
<pre class="wp-block-code"><code>$resultQuery = $yaClass-&gt;sendQueryYaDisk('trash/resources', &#91;], 'DELETE');</code></pre>
<!-- /wp:code -->
