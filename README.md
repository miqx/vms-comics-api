# vms-comics-api
    Test application from Visual Domain

## Installation
    Create .env file fill up these fields and configure database options
```env
    MARVEL_URL={marvel api url}
    MARVEL_PUBLIC_KEY={marvel public key}
    MARVEL_PRIVATE_KEY={marvel private key}
```
```
   * Run 'composer install'.
   * Run 'php artisan migrate' to migrate data tables.
   * Run 'php artisan key:generate'.
```


## Usage
    * run this command to populate database.
```shellscript
    php artisan marvel:fetch-data {--qty=} (optional can specify number of comics)
``` 

