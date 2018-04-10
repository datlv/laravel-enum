# Laravel Enum

## Install

* **Thêm vào file composer.json của app**
```json
	"repositories": [
        {
            "type": "vcs",
            "url": "https://gitlab.com/datlv/laravel-enum.git"
        }
    ],
    "require": {
        "datlv/laravel-enum": "dev-master"
    }
```
``` bash
$ composer update
```

* **Thêm vào file config/app.php => 'providers'**
```php
	Datlv\Enum\ServiceProvider::class,
```

* **Publish config và database migrations**
```bash
$ php artisan vendor:publish
$ php artisan migrate
```

* **Đăng ký Resource sử dụng Enums**, vd: Article
_Thêm vào hàm boot() của **Article ServiceProvider**_
```php
Enum::registerResources([Article::class]);
```

## License

The MIT License (MIT). Please see [License](LICENSE.md) for more information.
