# Laravel Restful Controller
[![Build Status](https://travis-ci.com/TomHart/laravel-restful-controller.svg?branch=master)](https://travis-ci.com/TomHart/laravel-restful-controller)
[![codecov](https://codecov.io/gh/TomHart/laravel-restful-controller/branch/master/graph/badge.svg)](https://codecov.io/gh/TomHart/laravel-restful-controller)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TomHart/laravel-restful-controller/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TomHart/laravel-restful-controller/?branch=master)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/TomHart/laravel-restful-controller?color=green)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)


This library adds an `AbstractRestfulController` to to be basic heavy lifting of a CRUD controller. 


## Usage
* Create a controller extending from this, and implement the method `getModelClass`

```php
use TomHart\Restful\AbstractRestfulController;

class BlogController extends AbstractRestfulController 
{
    /**
     * What Model class to search for entities.
     * @return string
     */
    protected function getModelClass(): string
    {
        return Blog::class;
    }
}

```

* If you want it to render views for index, show, or store, add a `$views` property
```php

    /**
     * The views to render.
     * @var array
     */
    protected $views = [
        'index' => 'blog/index',
        'show' => 'blog/show',
        'store' => 'blog/store'
    ];
```
If `$views` is empty, or the specified view doesn't exist, then JSON is returned
* Define a resource route
```php
Route::resource('blog', 'BlogController');
```

### Relationships

The show route can return your models relationships. If you send a `X-Load-Relationship` header, 
with a comma separated value list of headers to load. See the `testRelationshipsCanBeReturned` test 
for an example.
