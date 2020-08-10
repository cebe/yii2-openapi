<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET pets' => 'pet/index',
    'POST pets' => 'pet/create',
    'GET pets/<id>' => 'pet/view',
    'DELETE pets/<id>' => 'pet/delete',
    'PATCH pets/<id>' => 'pet/update',
    'GET petComments' => 'pet-comment/index',
    'GET pet-details' => 'pet-detail/index',
    'pets' => 'pet/options',
    'pets/<id>' => 'pet/options',
    'petComments' => 'pet-comment/options',
    'pet-details' => 'pet-detail/options',
];
