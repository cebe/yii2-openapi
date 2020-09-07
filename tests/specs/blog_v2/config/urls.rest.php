<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET posts' => 'post/list',
    'GET posts/<id:\d+>' => 'post/view',
    'GET posts/<postId:\d+>/comments' => 'comment/list-for-post',
    'POST posts/<postId:\d+>/comments' => 'comment/create-for-post',
    'GET posts/<postSlug:[\w-]+>/comment/<id:\d+>' => 'comment/view-for-post',
    'DELETE posts/<postSlug:[\w-]+>/comment/<id:\d+>' => 'comment/delete-for-post',
    'PATCH posts/<postSlug:[\w-]+>/comment/<id:\d+>' => 'comment/update-for-post',
    'posts' => 'post/options',
    'posts/<id:\d+>' => 'post/options',
    'posts/<postId:\d+>/comments' => 'comment/options',
    'posts/<postSlug:[\w-]+>/comment/<id:\d+>' => 'comment/options',
];
