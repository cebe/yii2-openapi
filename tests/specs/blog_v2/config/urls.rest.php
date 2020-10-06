<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET posts' => 'post/list',
    'GET posts/<id:\d+>' => 'post/view',
    'GET posts/<id:\d+>/relationships/category' => 'post/view-related-category',
    'GET posts/<id:\d+>/relationships/comments' => 'post/list-related-comments',
    'GET posts/<postId:\d+>/comments' => 'comment/list-for-post',
    'POST posts/<postId:\d+>/comments' => 'comment/create-for-post',
    'GET category/<categoryId:\d+>/posts/<id:\d+>' => 'post/view-for-category',
    'GET posts/<slug:[\w-]+>/comment/<id:\d+>' => 'comment/view-for-post',
    'DELETE posts/<slug:[\w-]+>/comment/<id:\d+>' => 'comment/delete-for-post',
    'PATCH posts/<slug:[\w-]+>/comment/<id:\d+>' => 'comment/update-for-post',
    'posts' => 'post/options',
    'posts/<id:\d+>' => 'post/options',
    'posts/<id:\d+>/relationships/category' => 'post/options',
    'posts/<id:\d+>/relationships/comments' => 'post/options',
    'posts/<postId:\d+>/comments' => 'comment/options',
    'category/<categoryId:\d+>/posts/<id:\d+>' => 'post/options',
    'posts/<slug:[\w-]+>/comment/<id:\d+>' => 'comment/options',
];
