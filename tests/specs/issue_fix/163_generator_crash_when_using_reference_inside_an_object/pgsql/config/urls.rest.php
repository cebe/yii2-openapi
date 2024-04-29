<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET account/<accountId:\d+>/contacts' => 'contact/list-for-account',
    'GET account/<accountId:\d+>/contacts/<contactId:\d+>' => 'contact/view-for-account',
    'account/<accountId:\d+>/contacts' => 'contact/options',
    'account/<accountId:\d+>/contacts/<contactId:\d+>' => 'contact/options',
];
