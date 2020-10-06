<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET pets' => 'pet/list',
    'POST pets' => 'pet/create',
    'GET pets/<petId:[\w-]+>' => 'pet/view',
    'DELETE pets/<petId:[\w-]+>' => 'pet/delete',
    'PATCH pets/<petId:[\w-]+>' => 'pet/update',
    'pets' => 'pet/options',
    'pets/<petId:[\w-]+>' => 'pet/options',
];
