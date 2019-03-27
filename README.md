# API Builder for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/api-builder.svg?style=flat-square)](https://packagist.org/packages/spatie/api-builder)
[![Build Status](https://img.shields.io/travis/spatie/api-builder/master.svg?style=flat-square)](https://travis-ci.org/spatie/api-builder)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/api-builder.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/api-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/api-builder.svg?style=flat-square)](https://packagist.org/packages/spatie/api-builder)

API builder for Laravel aims to provide a quick and standard way of creating APIs. When you create an API for your 
resource using this package, you will get the standard CRUD endpoints and is flexible enough to adapt to the needs of
your business logic.

## Installation

You can install the package via composer:

```bash
composer require coreproc/api-builder
```

## Usage

To start using API builder, simply create a controller, have it extend the `ApiBuilderController` class, then define the
necessary information regarding the resource. Here is what a basic controller should look like:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Transformers\PostTransformer;
use CoreProc\ApiBuilder\Http\Controllers\ApiBuilderController;

class PostsController extends ApiBuilderController
{
    public static $model = Post::class;

    public static $transformer = PostTransformer::class;

    protected $allowedParams = [
        'user_id',
        'title',
        'short_description',
        'content',
        'published_at',
    ];
}
```

This controller assumes that you have a `Post` model and a `PostTransformer` transformer. If you don't know about
transformers, you can find more information [here](https://fractal.thephpleague.com/transformers/).

Now, to make the above controller accessible, all we need to do is define the route in `routes/api.php`.

```php
Route::apiResource('posts', 'Api\PostsController');
```

Once defined in your routes, you will now get the following endpoints:

```php
GET     /api/posts          Get a paginated list of the posts in your database
POST    /api/posts          Create a new Post entry
GET     /api/posts/{id}     Get the details of the specified Post resource
PUT     /api/posts/{id}     Update the Post resource
DEL     /api/posts/{id}     Delete the Post resource
```

### Querying / Filtering

One of the main points of this package is to have a querying/filtering feature capable enough so that API consumers can
have complete control over what they are looking for.

Inspired by GraphQL, the same way of adding operands to the parameters have been applied.

For example, if you want to query for all the Posts from a particular user ID, you would do this:

```
GET /api/posts?user_id=1
```

But if you wanted to query for all Posts where the user ID is greater than 5 (for example), you would do it like this:

```
GET /api/posts?user_id_gt=5
```

The same would be applied for all the parameters defined in your `$allowedParams` variable of your controller. You are 
required to define which parameters are allowed to be passed through the query if you want to use this feature.

Here is another example. If you would like to get Posts where the title contains the word "test", here is how you would
do it:

```
GET /api/posts?title_contains=test
```

Here is the complete list of operands that you can use:

```
not                 Not equal
lt                  Less than
lte                 Less than or equal to
gt                  Greater than
gte                 Greater than or equal to
contains            Uses LIKE with wildcard on both sides of the query value
not_contains        Inverse of contains
starts_with         Uses LIKE with wildcard at the end of the query value
not_starts_with     Inverse of starts_with
ends_with           Uses LIKE with wildcard at the beginning of the query value
not_ends_with       Inverse of ends_with
in                  Where IN given a set of values. This must be passed as an array.
not_in              Where NOT IN given a set of values. This must be pased as an array.
```

Now if you wanted to modify / apply a filter when indexing the resource by default, you can do so by overriding the 
`indexQuery()` method in your controller:

```php
class PostsController extends ApiBuilderController {

    ...
    
    protected static function indexQuery(Request $request, Builder $query)
    {
        return $query->where('user_id', $request->user()->id);
    }
```

By doing the above, indexing the resource will only yeild results that have the user ID of the currently logged in user.
You can do additional logic here as well.

#### Dates

Querying / Filtering of dates are also allowed and operands can be used as well. However, you will have to define the
fields which are dates:

```php
class PostsController extends ApiBuilderController {

    ...
    
    protected $dates = [
        'published_at',
    ];
```

Once that is defined, you can pass any value that `Carbon::parse()` can parse. For example:

```
GET /api/posts?published_at_gte=Mar1
```

#### Sorting

Sorting is also possible when going through the index of your resource. Here is an example:

```
GET /api/posts?sort=published_at
```

This will sort all Posts by their published date in ascending order. To change the direction of the order, pass the
`desc` value along like so:

```
GET /api/posts?sort=published_at,desc
```

Please note that this package will be reserving the `sort` keyword for its sorting feature. (Roadmap: make this 
configurable)

#### Null values

To pass null values, this package reserves the `null` value to the query values. Everything that is passed with the
`null` string will be converted into a NULL value in the backend. Here is an example on how to use it:

```
GET /api/posts?published_at_not=null
```

#### Limiting results

You can also limit your results by passing the `limit` parameter:

```
GET /api/posts?limit=1
```

#### Pagination

By default the index of the resource returns a paginated result. The default number of results per page is 15. (Roadmap:
make this configurable)

To increase the number of results per page, you can pass the `per_page` parameter:

```
GET /api/posts?per_page=100
``` 

Pages can be navigated by defining the page number:

```
GET /api/posts?page=2
```

### Creating a resource

// TODO

#### Creation Rules

// TODO

### Viewing a resource

// TODO

### Updating a resource

// TODO

#### Update rules

// TODO

### Deleting a resource

// TODO

### Authorization

// TODO

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email chris.bautista@coreproc.ph instead of using the issue tracker.

## About CoreProc

CoreProc is a software development company that provides software development services to startups, digital/ad agencies, and enterprises.

Learn more about us on our [website](https://coreproc.com).

## Credits

- [Chris Bautista](https://github.com/chrisbjr)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
