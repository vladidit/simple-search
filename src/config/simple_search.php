<?php

/*
 * Config example for Simple Search
 *
*/

return [
    'products' => [
        'table' => 'Put here target table name',
        'model' => 'Put here target model name for filling with search results',
        'fields' => [
            'title' => 5,
            'summary' => 3,
            'description',
        ],
        'fill' => [
            '*',
        ],
        'scopes' => [
            function($query){
                return $query->where('property', 1);
            },
        ],
    ]
];