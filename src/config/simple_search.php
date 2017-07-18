<?php

/*
 * Config example for Simple Search
 *
*/

return [
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
    ]
];