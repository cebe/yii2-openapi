<?php
//Data provider for AttributeResolver test for ref_noobject.yml spec

use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\AttributeRelation;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\NonDbRelation;

return [
    'PetStatistic' => new DbModel([
        'pkName' => 'id',
        'name' => 'PetStatistic',
        'tableName' => '',
        'description' => 'Non-Db model',
        'isNotDb' => true,
        'attributes' => [
            'id' => (new Attribute('id', ['phpType' => 'int', 'dbType' => 'bigpk']))
                ->setReadOnly()->setIsPrimary(),
            'title' => (new Attribute('title', ['phpType' => 'string', 'dbType' => 'text'])),
            'dogsCount' => (new Attribute('dogsCount', ['phpType' => 'int', 'dbType' => 'integer'])),
            'catsCount' => (new Attribute('catsCount', ['phpType' => 'int', 'dbType' => 'integer'])),
            'summary' => (new Attribute('summary', ['phpType' => 'string', 'dbType' => 'text'])),
            'parentPet' => (new Attribute('parentPet', ['phpType' => 'int', 'dbType' => 'bigint']))
                ->asReference('Pet')->setDescription('A Pet'),
        ],
        'relations' => [
            'parentPet' => new AttributeRelation('parentPet', 'pets', 'Pet', 'hasOne', ['id' => 'parentPet_id']),
            'favoritePets' => new AttributeRelation('favoritePets', 'pets', 'Pet', 'hasMany', ['pet_statistic_id' => 'id']),
        ],
        'nonDbRelations' => [
            'topDoctors' => new NonDbRelation('topDoctors', 'Doctor', 'hasMany'),
        ]
        ]),
    'personWatch' => new DbModel([
        'pkName' => 'id',
        'name' => 'PersonWatch',
        'tableName' => '',
        'description' => 'Information about a user watching a Person',
        'isNotDb' => true,
        'attributes' => [
            'personId' => (new Attribute('personId', ['phpType' => 'string', 'dbType' => 'string']))
                ->setReadOnly(false)->setRequired(false)->setDescription('The MongoDB Identifier'),
            'userId' => (new Attribute('userId', ['phpType' => 'string', 'dbType' => 'string']))
                ->setReadOnly(false)->setRequired(false)->setDescription('The MongoDB Identifier'),
            'someProp' => (new Attribute('someProp', ['phpType' => 'int', 'dbType' => 'integer']))
                ->setReadOnly(false)->setRequired(false)
        ]
    ])
];