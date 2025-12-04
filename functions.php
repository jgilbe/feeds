<?php
use phpseclib3\Net\SFTP;

function getBcBrands(array $bcCreds, string $page = '')
{
    $filters = [];
    if($page != '') {
        $filters['page'] = $page;
    }
    $query = http_build_query($filters);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL =>  $bcCreds['path'] . "v3/catalog/brands?$query",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Content-Type: application/json",
            "X-Auth-Token: " . $bcCreds['token']
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        die("cURL Error #:" . $err);
    } else {
        return json_decode($response, true);
    }
}

function getBcProducts(array $bcCreds, string $page = '')
{
    $curl = curl_init();

    $filters = [
        'is_visible' => 'true',
        'include' => 'primary_image'
    ];

    if(strlen($page) > 0) {
        $filters['page'] = $page;
    }

    $query = http_build_query($filters);

    curl_setopt_array($curl, [
        CURLOPT_URL => $bcCreds['path'] . "v3/catalog/products?" . $query,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Content-Type: application/json",
            "X-Auth-Token: " . $bcCreds['token']
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        die("cURL Error #:" . $err);
    } else {
        return json_decode($response, true);
    }
}


function fileToSftp(array $sftp, string $inputFile, string $outputFile)
{
    $s = new SFTP($sftp['host'], $sftp['port']);
    $s->login($sftp['username'], $sftp['password']);
    return $s->put($outputFile, $inputFile, SFTP::SOURCE_LOCAL_FILE);
}