# Simple Search for Laravel 5.* #
This package provides search argorithm for laravel projects. It\'s simple and clear. The magic is in using lemmas from query words, thanks for phpMorhy.

### What Simple Search can do? ###

* Find occurancies of lemmas in array of tables
* Count rating of found results
* Associate models to each result in collection
* Paginate collection of search results
* Integrate front end solution of live-search interface

### How do I get set up? ###

0. Clone package from repo
1. Set your composer file to autoload Simple Search classes and update autoload files

```
 "autoload": {
    "classmap":
      "path_to_package"
    ],
```

2. Add service provider to config/app.php

```
 Vladidit\SimpleSearch\SimpleSearchServiceProvider::class,
```
3. Publish assets for live search
```
 php artisan vendor:publish --tag=simple_search.assets
```
4. Publish config example
```
 php artisan vendor:publish --tag=simple_search.configs
```

That's all! Simple Search ready to use.

### How to use? ###

Simple Search provides few public methods to configure your search request on a fly.
Config files just a place for storing your search request settings and at this moment contains a number of tips and disclaimers.

So, first af all, you need to create an instance of SimpleSearch::class, set query string and search array.

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
    * Weight is a rating for every unique occurence of requested word in field. So if word present in two grammar form in one field - Simple Search will operate this occurensies as one.
    * For example, rating of field value ('prawn prawns') will be the same as field value ('prawn') of ('prawns').
    * As a result of search Simple Search give a summary rating for each row in table or model. This value is main parameter to order search results.
    * Row in table with occurance of requested word only in title will get lower rating then next row with occurancies in 'title' and 'summary', or 'description'.
    */
    
    'fields' => [
        'title' => 5,
        'summary' => 3,
        'description',
    ],
    
    /* define fields for fill your models
    * This fields will be selected from your target table and wil be used to fill associated model. 
    * If you need to select all fields to fill: set only one '*' value
    */
    
    'fill' => [
        'id',
        'title',
        'picture',
        'status',
        'summary',
    ],
],
```

After that you can get search results: 

```
$searchResult = $mySimpleSearch->searchOne()
```