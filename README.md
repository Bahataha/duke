`````__`````# Laravel CRUD Generator

[![Total Downloads](https://poser.pugx.org/duke/crud-generator/d/total.svg)](https://packagist.org/packages/duke/crud-generator)
[![License](https://poser.pugx.org/duke/crud-generator/license.svg)](https://packagist.org/packages/duke/crud-generator)

## Installation
```
composer require duke/crud-generator
```

```
!!! Warning !!!
Если у вас есть css/app.css нужно переименовать его
php artisan vendor:publish --provider="Duke\CrudGenerator\CrudGeneratorServiceProvider" --force


```
```
in    app\Http\Kernel

protected $routeMiddleware = [
    ...
    'admin' => \App\Http\Middleware\isAdmin::class,
    'date' => \App\Http\Middleware\Date::class,
]
```
### Promo prject
```
php artisan promo:project
```
## Usage

CRUD fields from a JSON file:

```json
{
    "fields": [
        {
            "name": "title",
            "type": "string"
        },
        {
            "name": "content",
            "type": "text"
        },
        {
            "name": "category",
            "type": "select",
            "options": {
                "technology": "Technology",
                "tips": "Tips",
                "health": "Health"
            }
        },
        {
            "name": "user_id",
            "type": "bigint#unsigned"
        }
    ],
    "foreign_keys": [
        {
            "column": "user_id",
            "references": "id",
            "on": "users",
            "onDelete": "cascade"
        }
    ],
    "relationships": [
        {
            "name": "user",
            "type": "belongsTo",
            "class": "App\\User"
        }
    ],
    "validations": [
        {
            "field": "title",
            "rules": "required|max:10"
        }
    ]
}
```
```
php artisan crud:generate Posts --fields_from_file="duke/j.json" --view-path=admin --controller-namespace=Admin --route-group=admin --form-helper=html
```
"# duke" 
