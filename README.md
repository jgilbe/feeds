# Feeds

Generates and stores/sends feed data from BigCommerce to somewhere else.

### Currently implemented:
* Google Shopping - Streams to SFTP. Requires brand and barcodes to be set on products (or they'll be filtered out).

### Installation and Usage

Requires php8.4, composer and curl extension.

Copy .env.php.sample to .env.php and fill in the required values.

```$ php google.php``` or execute via cron on required schedule.
