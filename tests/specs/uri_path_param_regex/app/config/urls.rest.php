<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET domains' => 'domain/list',
    'GET domains/<domainname:[a-z-.]+>' => 'domain/view',
    'GET domains_2/<domainname:[a-z0-9-_]+>' => 'domains-2/view',
    'GET domains_3/<domainname:[\w-]+>' => 'domains-3/view',
    'GET age/<age:\d+>' => 'age/view',
    'GET age_without_start_end_char/<age:[0-13]+>' => 'age-without-start-end-char/view',
    'GET north-american-telephone-number-with-an-optional-area-code/<number:(\\([0-9]{3}\\))?[0-9]{3}-[0-9]{4}+>' => 'north-american-telephone-number-with-an-optional-area-code/view',
    'domains' => 'domain/options',
    'domains/<domainname:[a-z-.]+>' => 'domain/options',
    'domains_2/<domainname:[a-z0-9-_]+>' => 'domains-2/options',
    'domains_3/<domainname:[\w-]+>' => 'domains-3/options',
    'age/<age:\d+>' => 'age/options',
    'age_without_start_end_char/<age:[0-13]+>' => 'age-without-start-end-char/options',
    'north-american-telephone-number-with-an-optional-area-code/<number:(\\([0-9]{3}\\))?[0-9]{3}-[0-9]{4}+>' => 'north-american-telephone-number-with-an-optional-area-code/options',
];
