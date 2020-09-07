<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET pets' => 'pet/list',
    'POST pets' => 'pet/create',
    'GET pets/<id:[\w-]+>' => 'pet/view',
    'DELETE pets/<id:[\w-]+>' => 'pet/delete',
    'PATCH pets/<id:[\w-]+>' => 'pet/update',
    'GET petComments' => 'pet-comment/list',
    'GET pet-details' => 'pet-detail/list',
    'pets' => 'pet/options',
    'pets/<id:[\w-]+>' => 'pet/options',
    'petComments' => 'pet-comment/options',
    'pet-details' => 'pet-detail/options',
];
