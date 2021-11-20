<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET api/v1/pets' => 'api/v1/pet/list',
    'POST api/v1/pets' => 'api/v1/pet/create',
    'GET animals/pets/<id:[\w-]+>' => 'pet/view',
    'DELETE animals/pets/<id:[\w-]+>' => 'pet/delete',
    'PATCH animals/pets/<id:[\w-]+>' => 'pet/update',
    'GET petComments' => 'pet-comment/list',
    'GET info/pet-details' => 'petinfo/pet-detail/list',
    'api/v1/pets' => 'api/v1/pet/options',
    'animals/pets/<id:[\w-]+>' => 'pet/options',
    'petComments' => 'pet-comment/options',
    'info/pet-details' => 'petinfo/pet-detail/options',
];
