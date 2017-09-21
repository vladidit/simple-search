# Simple Search for Laravel #
This package provides search algorithm for laravel projects. It's simple and clear. The magic is in using lemmas from query words, thanks for phpMorhy and https://github.com/s-litvin for help.

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
      "PATH TO PACKAGES"
    ]
 }
```
* OR you can add repository to composer, set simple search as requred package and make composer update. As a result simple search will be instaled as vendor.
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/vladidit/simple-search.git"
        }
    ],
    "require": {
        "vladidit/simple_search": "0.*"
    }
}
```
* Add service provider to config/app.php

```
 Vladidit\SimpleSearch\SimpleSearchServiceProvider::class,
```
* If you need a front end solution for live-search, publish live search assets
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

$mySimpleSearch = new Search();
$mySimpleSearch->setQuery($query);
$mySimpleSearch->setSearchArray($searchArray);
```

SimpleSearch has a constructor, so you can create its instance with arguments: 

```
$mySimpleSearch = new Search(string $query, array $searchArray);
```

But before you need to create your search array to tell Simple Search where and what to look for.

There is an example of simple search array.

```
$searchArray = [
   /* define table
   * You can skip this parameter
   * if your model is an instance of Eloquent - Simple Search will call table from model
   */

   'table' => 'products',

   /* define model to associate with search results. You can skip this step if you don't need to use associated model with search results */

   'model' => 'Product',

   /* define fields and their weights
   * If you do not need to assign special weight for certain fields - you can leave array value just as field name and Simple Search will assign 1 as weight to this field
   * Weight is a rating for every unique occurrence of requested word in field. So if word presents in two grammar form in one field - Simple Search will operate this occurrences as one.
   * For example, rating of field value ('prawn prawns') will be the same as field value ('prawn') or ('prawns').
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

### Search results ###


### Adding scopes ###

You can add additional scopes for search iteration by using setScope() method or put scopes in scopes index in search array.
```
$mySimpleSearch->setScopes([
    function($query){
      return $query->where('YOUR PROPERTY 1 NAME', 'SOME VALUE');
    },
    ...
    function($query){
       return $query->where('YOUR PROPERTY 2 NAME', 'SOME VALUE');
    },
]);
```
As a result your search query will be extended by addition conditions.

### Set limits ###

Limits will help you to make search results shorter because of adding LIMIT to each sql query and, if you are using searchMany() method, to final merged collection.
```
$mySimpleSearch->setLimit(3);
```
