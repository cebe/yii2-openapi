<?php
//Data provider for AttributeResolver test for blog.yml spec
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\AttributeRelation;
use cebe\yii2openapi\lib\items\DbIndex;
use cebe\yii2openapi\lib\items\DbModel;

return [
    'user' => new DbModel([
        'name' => 'User',
        'tableName' => 'users',
        'description' => 'The User',
        'attributes' => [
            'id' => (new Attribute('id', ['phpType' => 'int', 'dbType' => 'pk']))
                ->setReadOnly()->setRequired()->setIsPrimary()->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'username' => (new Attribute('username', ['phpType' => 'string', 'dbType' => 'string']))
                ->setSize(200)->setRequired()->setFakerStub('substr($faker->userName, 0, 200)'),
            'email' => (new Attribute('email', ['phpType' => 'string', 'dbType' => 'string']))
                ->setSize(200)->setRequired()->setFakerStub('substr($faker->safeEmail, 0, 200)'),
            'password' => (new Attribute('password', ['phpType' => 'string', 'dbType' => 'string']))
                ->setRequired()->setFakerStub('$faker->password'),
            'role' => (new Attribute('role', ['phpType' => 'string', 'dbType' => 'string']))
                ->setSize(20)
                ->setDefault('reader')
                ->setFakerStub('$faker->randomElement([\'admin\', \'editor\', \'reader\'])'),
            'flags' => (new Attribute('flags', ['phpType'=>'int', 'dbType'=>'integer']))->setDefault(0)->setFakerStub
            ('$faker->numberBetween(0, 2147483647)'),
            'created_at' => (new Attribute('created_at', ['phpType' => 'string', 'dbType' => 'datetime']))
                ->setDefault('CURRENT_TIMESTAMP')->setFakerStub('$faker->dateTimeThisYear(\'now\', \'UTC\')->format(DATE_ATOM)'),
        ],
        'relations' => [],
        'indexes' => [
            'users_email_key' => DbIndex::make('users', ['email'], null, true),
            'users_username_key' => DbIndex::make('users', ['username'], null, true),
            'users_role_flags_index' => DbIndex::make('users', ['role', 'flags'])
        ]
    ]),
    'category' => new DbModel([
        'name' => 'Category',
        'tableName' => 'categories',
        'description' => 'Category of posts',
        'attributes' => [
            'id' => (new Attribute('id', ['phpType' => 'int', 'dbType' => 'pk']))
                ->setReadOnly()->setRequired()->setIsPrimary()->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'title' => (new Attribute('title', ['phpType' => 'string', 'dbType' => 'string']))
                ->setRequired()->setSize(255)->setFakerStub('substr($faker->sentence, 0, 255)'),
            'active' => (new Attribute('active', ['phpType' => 'bool', 'dbType' => 'boolean']))
                ->setRequired()->setDefault(false)->setFakerStub('$faker->boolean'),
        ],
        'relations' => [
            'posts' => new AttributeRelation('posts', 'blog_posts', 'Post', 'hasMany', ['category_id' => 'id']),
        ],
        'indexes' => [
            'categories_active_index' => DbIndex::make('categories', ['active']),
            'categories_title_key' => DbIndex::make('categories', ['title'], null, true)
        ]
    ]),
    'post' => new DbModel([
        'name' => 'Post',
        'tableName' => 'blog_posts',
        'description' => 'A blog post (uid used as pk for test purposes)',
        'attributes' => [
            'uid' => (new Attribute('uid', ['phpType' => 'string', 'dbType' => 'string']))
                ->setReadOnly()->setRequired()->setIsPrimary()->setSize(128)
                ->setFakerStub('substr($uniqueFaker->sha256, 0, 128)'),
            'title' => (new Attribute('title', ['phpType' => 'string', 'dbType' => 'string']))
                ->setRequired()->setSize(255)->setFakerStub('substr($faker->sentence, 0, 255)'),
            'slug' => (new Attribute('slug', ['phpType' => 'string', 'dbType' => 'string']))
                ->setSize(200)->setLimits(null, null, 1)->setFakerStub('substr($uniqueFaker->slug, 0, 200)'),
            'active' => (new Attribute('active', ['phpType' => 'bool', 'dbType' => 'boolean']))
                ->setRequired()->setDefault(false)->setFakerStub('$faker->boolean'),
            'category' => (new Attribute('category', ['phpType' => 'int', 'dbType' => 'integer']))
                ->asReference('Category')
                ->setRequired()
                ->setDescription('Category of posts')
                ->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'created_at' => (new Attribute('created_at', ['phpType' => 'string', 'dbType' => 'date']))
               ->setFakerStub('$faker->dateTimeThisCentury->format(\'Y-m-d\')'),
            'created_by' => (new Attribute('created_by', ['phpType' => 'int', 'dbType' => 'integer']))
                ->asReference('User')
                ->setDescription('The User')
                ->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
        ],
        'relations' => [
            'category' => new AttributeRelation('category',
                'categories',
                'Category',
                'hasOne',
                ['id' => 'category_id']),
            'created_by' => new AttributeRelation('created_by', 'users', 'User', 'hasOne', ['id' => 'created_by_id']),
            'comments' => new AttributeRelation('comments', 'post_comments', 'Comment', 'hasMany', ['post_id' => 'uid']),
        ],
        'indexes' => [
            'blog_posts_title_key' => DbIndex::make('blog_posts', ['title'], null, true),
            'blog_posts_slug_key' => DbIndex::make('blog_posts', ['slug'], null, true)
        ]
    ]),
    'comment' => new DbModel([
        'name' => 'Comment',
        'tableName' => 'post_comments',
        'description' => '',
        'attributes' => [
            'id' => (new Attribute('id', ['phpType' => 'int', 'dbType' => 'bigpk']))
                ->setReadOnly(true)
                ->setRequired(true)
                ->setIsPrimary(true)
                ->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'post' => (new Attribute('post', ['phpType' => 'string', 'dbType' => 'string']))
                ->setRequired()
                ->setSize(128)
                ->asReference('Post')
                ->setDescription('A blog post (uid used as pk for test purposes)')
                ->setFakerStub('substr($uniqueFaker->sha256, 0, 128)'),
            'author' => (new Attribute('author', ['phpType' => 'int', 'dbType' => 'integer']))
                ->setRequired()
                ->asReference('User')
                ->setDescription('The User')
                ->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'message' => (new Attribute('message', ['phpType' => 'array', 'dbType' => 'json']))
                ->setRequired()->setDefault([])->setFakerStub('[]'),
            'meta_data' => (new Attribute('meta_data', ['phpType' => 'array', 'dbType' => 'json']))
                ->setDefault([])->setFakerStub('[]'),
            'created_at' => (new Attribute('created_at',['phpType' => 'int', 'dbType' => 'integer']))
                ->setRequired()->setFakerStub('$faker->unixTime'),
        ],
        'relations' => [
            'post' => new AttributeRelation('post', 'blog_posts', 'Post', 'hasOne', ['uid' => 'post_id']),
            'author' => new AttributeRelation('author', 'users', 'User', 'hasOne', ['id' => 'author_id']),
        ],
        'indexes' => []
    ]),
];
