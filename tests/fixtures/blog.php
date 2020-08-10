<?php
//Data provider for AttributeResolver test for blog.yml spec
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\AttributeRelation;
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
                ->setSize(200)->setRequired()->setUnique()->setFakerStub('substr($faker->userName, 0, 200)'),
            'email' => (new Attribute('email', ['phpType' => 'string', 'dbType' => 'string']))
                ->setSize(200)->setUnique()->setRequired()->setFakerStub('substr($faker->safeEmail, 0, 200)'),
            'password' => (new Attribute('password', ['phpType' => 'string', 'dbType' => 'string']))
                ->setRequired()->setFakerStub('$faker->password'),
            'role' => (new Attribute('role', ['phpType' => 'string', 'dbType' => 'string']))
                ->setSize(20)
                ->setDefault('reader')
                ->setFakerStub('$faker->randomElement([\'admin\', \'editor\', \'reader\'])'),
            'created_at' => (new Attribute('created_at', ['phpType' => 'string', 'dbType' => 'datetime']))
                ->setDefault('CURRENT_TIMESTAMP')->setFakerStub('$faker->dateTimeThisCentury->format(\'Y-m-d H:i:s\')'),
        ],
        'relations' => [],
    ]),
    'category' => new DbModel([
        'name' => 'Category',
        'tableName' => 'categories',
        'description' => 'Category of posts',
        'attributes' => [
            'id' => (new Attribute('id', ['phpType' => 'int', 'dbType' => 'pk']))
                ->setReadOnly()->setRequired()->setIsPrimary()->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'title' => (new Attribute('title', ['phpType' => 'string', 'dbType' => 'string']))
                ->setRequired()->setUnique()->setSize(255)->setFakerStub('substr($faker->sentence, 0, 255)'),
            'active' => (new Attribute('active', ['phpType' => 'bool', 'dbType' => 'boolean']))
                ->setRequired()->setDefault(false)->setFakerStub('$faker->boolean'),
        ],
        'relations' => [
            'posts' => new AttributeRelation('posts', 'blog_posts', 'Post', 'hasMany', ['category_id' => 'id']),
        ],
    ]),
    'post' => new DbModel([
        'name' => 'Post',
        'tableName' => 'blog_posts',
        'description' => 'A blog post (uid used as pk for test purposes)',
        'attributes' => [
            'uid' => (new Attribute('uid', ['phpType' => 'int', 'dbType' => 'bigpk']))
                ->setReadOnly()->setRequired()->setIsPrimary()->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'title' => (new Attribute('title', ['phpType' => 'string', 'dbType' => 'string']))
                ->setRequired()->setUnique()->setSize(255)->setFakerStub('substr($faker->sentence, 0, 255)'),
            'slug' => (new Attribute('slug', ['phpType' => 'string', 'dbType' => 'string']))
                ->setUnique()->setSize(200)->setLimits(null, null, 1)->setFakerStub('substr($uniqueFaker->slug, 0, 200)'),
            'active' => (new Attribute('active', ['phpType' => 'bool', 'dbType' => 'boolean']))
                ->setRequired()->setDefault(false)->setFakerStub('$faker->boolean'),
            'category' => (new Attribute('category', ['phpType' => 'int', 'dbType' => 'integer']))
                ->asReference('Category')
                ->setRequired()
                ->setDescription('Category of posts')
                ->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'created_at' => (new Attribute('created_at', ['phpType' => 'string', 'dbType' => 'date']))
               ->setFakerStub('$faker->iso8601'),
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
            'post' => (new Attribute('post', ['phpType' => 'int', 'dbType' => 'bigint']))
                ->setRequired()
                ->asReference('Post')
                ->setDescription('A blog post (uid used as pk for test purposes)')
                ->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'author' => (new Attribute('author', ['phpType' => 'int', 'dbType' => 'integer']))
                ->setRequired()
                ->asReference('User')
                ->setDescription('The User')
                ->setFakerStub('$uniqueFaker->numberBetween(0, 2147483647)'),
            'message' => (new Attribute('message', ['phpType' => 'array', 'dbType' => 'json']))
                ->setRequired()->setDefault([])->setFakerStub('[]'),
            'created_at' => (new Attribute('created_at',['phpType' => 'int', 'dbType' => 'integer']))
                ->setRequired()->setFakerStub('$faker->unixTime'),
        ],
        'relations' => [
            'post' => new AttributeRelation('post', 'blog_posts', 'Post', 'hasOne', ['uid' => 'post_id']),
            'author' => new AttributeRelation('author', 'users', 'User', 'hasOne', ['id' => 'author_id']),
        ],
    ]),
];