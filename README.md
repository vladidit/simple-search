# Simple Search for Laravel #
This package provides search algorithm for laravel projects. It's simple and clear. The magic is in using lemmas from query words, thanks for phpMorhy.

### Required ###
Laravel 5.*

### What Simple Search can do? ###

* Find occurrences of lemmas in array of tables
* Count rating of found results
* Associate models to each result in collection
* Paginate collection of search results
* Integrate front end solution of live-search interface

### How do I get set up? ###

* Clone package from repo and set your composer for autoload Simple Search classes and update it

```
 "autoload": {
    "classmap":
      "path_to_packages"
    ]
 }
```
* Or you can add repository to composer and make composer update
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/vladidit/simplesearch.git"
        }
    ],
    "require": {
        "vladidit/simplesearch": "1.*"
    }
}
```
* Add service provider to config/app.php

```
 Vladidit\SimpleSearch\SimpleSearchServiceProvider::class,
```
* If you need a front end solution for live-search, publish assets for live search
```
 php artisan vendor:publish --tag=simple_search.assets
```
* Publish config examples
```
 php artisan vendor:publish --tag=simple_search.configs
```

That's all! Simple Search ready to use.

### How to use? ###

Simple Search provides few public methods to configure your search request on a fly.
Config files just a place for storing your search request settings and at this moment contains a number of tips and disclaimers.

So, first of all, you need to create an instance of SimpleSearch::class, set query string and search array.

```
use Vladidit\SimpleSearch\Search;

$mySimpleSearch = new SimpleSearch();
$mySimpleSearch->setQuery($query);
$mySimpleSearch->setSearchArray($searchArray);
```

SimpleSearch has a constructor, so you can create its instance with arguments: 

```
$mySimpleSearch = new SimpleSearch(string $query, array $searchArray);
```

But before you need to create your search array to tell Simple Search where and what to search.

There is an example of simple search array.

```
$searchArray = [
   /* define table
   * You can skip this parameter
   * if your model is an instance of Eloquent - Simple Search will call table from model
   */

   'table' => 'products',

   /* define model to associate with search results. */

   'model' => 'Product',

   /* define fields and their weights
   * If you do not need to assign special weight for certain fields - you can leave array value just as field name and Simple Search will assign 1 as weight to this field
   * Weight is a rating for every unique occurrence of requested word in field. So if word present in two grammar form in one field - Simple Search will operate this occurrences as one.
   * For example, rating of field value ('prawn prawns') will be the same as field value ('prawn') of ('prawns').
   * As a result of search Simple Search give a summary rating for each row in table or model. This value is main parameter to order search results.
   * Row in table with occurrence of requested word only in title will get lower rating then next row with occurrences in 'title' and 'summary', or 'description'.
   */

   'fields' => [
       'title' => 5,
       'summary' => 3,
       'description',
   ],

   /* define fields for fill your models
   * This fields will be selected from your target table and will be used to fill associated model.
   * If you need to select all fields to fill: set only one '*' value
   */

   'fill' => [
       'id',
       'title',
       'picture',
       'status',
       'summary',
   ],

   /* define scopes to use in search iteration */

   'scopes' => [
       function($query){
           return $query->where('YOUR PROPERTY NAME', 'SOME VALUE');
       },
   ],
];   
```

After that you can get search results: 

```
$searchResult = $mySimpleSearch->searchOne()
```

### Adding scopes ###

### Set limits ###

### Ignore words and get total count ###

### Extend your search query ###

### Paginate results ###

### Mark text ###