<?php

require_once '.env.php';
require_once 'functions.php';
require_once 'vendor/autoload.php';

$outputFile = 'google-shopping.tsv';
$baseUrl = 'https://www.quantumav.co.uk';

/* Get product data from BC and extract required fields. */
$products = [];
$page = 0;

echo 'Getting brand data from BC...' . PHP_EOL;
$data = getBcBrands($bcCreds);
$brands = $data['data'];
$pages = $data['meta']['pagination']['total_pages'];
while($page < $pages) {
    $page++;
    $data = getBcBrands($bcCreds, $page);
    $brands = array_merge($brands, $data['data']);
}
foreach($brands as $b) {
    $parsed[$b['id']] = $b['name'];
}
$brands = $parsed;

echo 'Getting products from BC...' . PHP_EOL;
$page = 0;
$data = getBcProducts($bcCreds);
$products = $data['data'];
$pages = $data['meta']['pagination']['total_pages'];
while($page < $pages) {
    $page++;
    $data = getBcProducts($bcCreds, $page);
    $products = array_merge($products, $data['data']);
}

if(count($products) == 0) {
    echo "No products found." . PHP_EOL;
    die();
}
echo "Found " . count($products) . " total products.";

echo 'Generating feed data...' . PHP_EOL;
$keys = ['id', 'title', 'description', 'price', 'sale_price', 'condition', 'link', 'availability', 'image_link', 'brand', 'gtin'];
$output = [];
foreach($products as $p) {
    $data = [];
    $data['id'] = $p['sku'];
    $data['title'] = $p['name'];
    $data['description'] = str_replace("\t", ' ', substr($p['description'], 0, 4995));
    $data['price'] = $p['price'] . ' GBP';
    if($p['sale_price'] > 0 && $p['sale_price'] < $p['price']) {
        $data['sale_price'] = $p['sale_price'];
    } else {
        $data['sale_price'] = '';
    }
    $data['condition'] = strtolower($p['condition']);
    if($p['custom_url']) {
        $data['link'] = $baseUrl . $p['custom_url']['url'];
    } else {
        //Occasionally a product doesn't have any URL, ignore these.
        continue;
    }
    $data['availability'] = ($p['inventory_level'] > 0) ? 'in_stock' : 'out_of_stock';
    if($p['primary_image']) {
        $data['image_link'] = $p['primary_image']['url_standard'];
    } else {
        //No image, no inclusion.
        continue;
    }

    if(isset($brands[$p['brand_id']])) {
        $data['brand'] = $brands[$p['brand_id']];
    } else {
        //No brand, no inclusion.
        continue;
    }
    if($p['gtin']) {
        $data['gtin'] = $p['gtin'];
    } elseif($p['upc']) {
        $data['gtin'] = $p['upc'];
    } else {
        //No GTIN, no inclusion.
        continue;
    }
    $output[] = $data;
}

echo 'Writing to temporary file...' . PHP_EOL;
echo count($output) . ' products.' . PHP_EOL;
$tmp = tmpfile();
fputcsv($tmp, $keys, "|",'"', "\\", "\n");
foreach($output as $o) {
    fputcsv($tmp, $o, "|",'"', "\\", "\n");
}
$inputFile = stream_get_meta_data($tmp)['uri'];

if(fileToSftp($sftp, $inputFile, $outputFile)) {
    echo "File uploaded successfully." . PHP_EOL;
} else {
    echo "File upload failed." . PHP_EOL;
}
fclose($tmp);