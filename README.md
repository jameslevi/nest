# Nest

![](https://img.shields.io/badge/packagist-v1.0.0-informational?style=flat&logo=<LOGO_NAME>&logoColor=white&color=2bbc8a) ![](https://img.shields.io/badge/license-MIT-informational?style=flat&logo=<LOGO_NAME>&logoColor=white&color=2bbc8a)  

Is a simple file-based PHP caching library.

## Features
1. Maximize the use of PHP's opcache capability.
2. Create multiple cache databases.
3. Easy integration with any PHP framework or no framework at all.

## Installation
1. You can install via composer.
```
composer require jameslevi/nest
```
2. If not using any framework, paste the following code to load the autoloader in your project.
```php
<?php

if(file_exists(__DIR__.'/vendor/autoload.php'))
{
    require __DIR__.'/vendor/autoload.php';
}
```
3. Import nest into your project.
```php
use Graphite\Component\Nest\Nest;
```
4. Set the default storage path for your project.
```php
Nest::setStoragePath(__DIR__ . "/storage/cache");
```
5. Set the default hash algorithm to use.
```php
Nest::setHashAlgorithm("md5");
```

## Basic Example
Let us try a simple caching for database configuration.
```php
<?php

use Graphite\Component\Nest\Nest;

// Set the default storage path.
Nest::setStoragePath(__DIR__ . "/storage/cache");

// Set the default hash algorithm.
Nest::setHashAlgorithm("md5");

// Create a new nest database instance.
$db = new Nest("db");

// Add the data to cache.
$db->add("host", "localhost");
$db->add("port", 8080);
$db->add("username", "root");
$db->add("password", "123");
$db->add("database", "users");
$db->add("charset", "utf-8");

// Generate or update the cache file.
$db->write();
```
This will generate a PHP file with the following content.
```php
<?php return array (
  '67b3dba8bc6778101892eb77249db32e' => 'localhost',
  '901555fb06e346cb065ceb9808dcfc25' => '3306',
  '14c4b06b824ec593239362517f538b29' => 'root',
  '5f4dcc3b5aa765d61d8327deb882cf99' => '123',
  '11e0eed8d3696c0a632f822df385ab3c' => 'users',
  'dbd153490a1c3720a970a611afc4371c' => 'utf-8',
);
```

## Getting Started
1. You can get values using the "get" method.
```php
$db->get("host") // Returns "localhost".
```
2. You can add new key-value using "add" method.
```php
$db->add("tables", ["user_logs", "user_contacts", "user_address"]) // The array will be converted into json string.
```
3. You can update key values using "set" method.
```php
$db->set("password", "abc") // Change the value of password from "123" to "abc".
```
4. All added or updated data will be only saved unless you call the "write" method.
```php
$db->write()
```
5. You can check if a key-value exists using "has" method.
```php
$db->has("charset") // Returns true because charset exists from our cache.
```
6. You can remove a key-value using "remove" method.
```php
$db->remove("port") // This will delete port from our cache.
```
7. You can return cache data as array using "toArray" method.
```php
$db->toArray()
```
8. You can return json formatted cache data using "toJson" method.
```php
$db->toJson()
```

## Using Nest Facade
1. You can return nest instance by calling a static method that defines the name of your cache database.
```php
Nest::db() // Is equal to "new Nest('db')"
```
2. You can return key-value by providing the first argument.
```php
Nest::db('charset') // Will return "utf-8".
```
3. You can update data by providingthe second argument.
```php
Nest::db('username', 'james')->write() // Will change the value of username from "root" to "james".
```
4. You can add new data by calling "add" method.
```php
Nest::db()->add('token', '1jds9ds93209sdds')->write()
```
5. You can remove key-value using "remove" method.
```php
Nest::db()->remove('token')->write()
```

## Clear Cache
1. You can clear a cache database using "destroy" method.
```php
Nest::destroy('db')
```
2. You can clear all your cache database using "destroyAll" method.
```php
Nest::destroyAll()
```

## Contribution
For issues, concerns and suggestions, you can email James Crisostomo via nerdlabenterprise@gmail.com.

## License
This package is an open-sourced software licensed under [MIT](https://opensource.org/licenses/MIT) License.
