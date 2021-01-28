<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET me' => 'me/view',
    'GET auth/password/recovery' => 'auth/password-recovery',
    'POST auth/password/recovery' => 'auth/create-password-recovery',
    'GET auth/password/confirm-recovery/<token:[\w-]+>' => 'auth/password-confirm-recovery',
    'POST auth/new-password' => 'auth/create-new-password',
    'GET categories' => 'group/list',
    'POST categories' => 'group/create',
    'GET categories/<categoryId:\d+>/posts' => 'post/list-for-category',
    'POST categories/<categoryId:\d+>/posts' => 'post/create-for-category',
    'GET posts' => 'post/list',
    'POST posts' => 'post/create',
    'GET posts/<id:\d+>' => 'post/view',
    'DELETE posts/<id:\d+>' => 'post/delete',
    'PATCH posts/<id:\d+>' => 'post/update',
    'PUT posts/<id:\d+>/upload/cover' => 'post/update-upload-cover',
    'GET posts/<id:\d+>/relationships/author' => 'post/view-related-author',
    'GET post/<postId:\d+>/comments/<id:\d+>' => 'reply/view-for-post',
    'GET posts/<id:\d+>/relationships/comments' => 'post/list-related-comments',
    'GET posts/<id:\d+>/relationships/tags' => 'post/list-related-tags',
    'PATCH posts/<id:\d+>/relationships/tags' => 'post/update-related-tags',
    'me' => 'me/options',
    'auth/password/recovery' => 'auth/options',
    'auth/password/confirm-recovery/<token:[\w-]+>' => 'auth/options',
    'auth/new-password' => 'auth/options',
    'categories' => 'group/options',
    'categories/<categoryId:\d+>/posts' => 'post/options',
    'posts' => 'post/options',
    'posts/<id:\d+>' => 'post/options',
    'posts/<id:\d+>/upload/cover' => 'post/options',
    'posts/<id:\d+>/relationships/author' => 'post/options',
    'post/<postId:\d+>/comments/<id:\d+>' => 'reply/options',
    'posts/<id:\d+>/relationships/comments' => 'post/options',
    'posts/<id:\d+>/relationships/tags' => 'post/options',
];
