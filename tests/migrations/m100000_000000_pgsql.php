<?php
use yii\db\Expression;
use yii\db\Migration;
use yii\db\Schema;
use yii\helpers\Json;

/**
 * Initial migration for blog_v2 (based on blog.yaml), so result for blog_v2 should be as secondary mogration
 **/
class m100000_000000_pgsql extends Migration
{
    public function init()
    {
        $this->db = 'pgsql';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable('{{%v2_categories}}',
            [
                'id' => $this->primaryKey(),
                'title' => $this->string(255)->notNull()->unique(),
                'active' => $this->boolean()->notNull()->defaultValue(false),
            ]);
        $this->createTable('{{%v2_users}}',
            [
                'id' => $this->primaryKey(),
                'username' => $this->string(200)->notNull()->unique(),
                'email' => $this->string(200)->notNull()->unique(),
                'password' => $this->string()->notNull(),
                'role' => $this->string(20)->null()->defaultValue('reader'),
                'flags' => $this->integer()->null()->defaultValue(0),
                'created_at' => $this->timestamp()->null()->defaultExpression("CURRENT_TIMESTAMP"),
            ]);
        $this->createTable('{{%v2_posts}}',
            [
                'uid' => $this->bigPrimaryKey(),
                'title' => $this->string(255)->notNull()->unique(),
                'slug' => $this->string(200)->null()->defaultValue(null)->unique(),
                'category_id' => $this->integer()->notNull(),
                'active' => $this->boolean()->notNull()->defaultValue(false),
                'created_at' => $this->date()->null()->defaultValue(null),
                'created_by_id' => $this->integer()->null()->defaultValue(null),
            ]);
        $this->addForeignKey('fk_v2_posts_category_id_v2_categories_id',
            '{{%v2_posts}}',
            'category_id',
            '{{%v2_categories}}',
            'id');
        $this->addForeignKey('fk_v2_posts_created_by_id_v2_users_id',
            '{{%v2_posts}}',
            'created_by_id',
            '{{%v2_users}}',
            'id');
        $this->createTable('{{%v2_comments}}',
            [
                'id' => $this->bigPrimaryKey(),
                'post_id' => $this->bigInteger()->notNull(),
                'author_id' => $this->integer()->notNull(),
                'message' => $this->json()->notNull()->defaultValue('{}'),
                'meta_data' => $this->json()->notNull()->defaultValue('[]'),
                'created_at' => $this->integer()->notNull(),
            ]);
        $this->addForeignKey('fk_v2_comments_post_id_v2_posts_uid',
            '{{%v2_comments}}',
            'post_id',
            '{{%v2_posts}}',
            'uid');
        $this->addForeignKey('fk_v2_comments_author_id_v2_users_id',
            '{{%v2_comments}}',
            'author_id',
            '{{%v2_users}}',
            'id');
        $this->createTable('{{%v2_fakerable}}',
            [
                'id' => $this->bigPrimaryKey(),
                'active' => $this->boolean()->null()->defaultValue(null),
                'floatval' => $this->float()->null()->defaultValue(null),
                'floatval_lim' => $this->float()->null()->defaultValue(null),
                'doubleval' => $this->double()->null()->defaultValue(null),
                'int_min' => $this->integer()->null()->defaultValue(3),
                'int_max' => $this->integer()->null()->defaultValue(null),
                'int_minmax' => $this->integer()->null()->defaultValue(null),
                'int_created_at' => $this->integer()->null()->defaultValue(null),
                'int_simple' => $this->integer()->null()->defaultValue(null),
                'str_text' => $this->text()->null()->defaultValue(null),
                'str_varchar' => $this->string(100)->null()->defaultValue(null),
                'str_date' => $this->date()->null()->defaultValue(null),
                'str_datetime' => $this->timestamp()->null()->defaultValue(null),
                'str_country' => $this->text()->null()->defaultValue(null),
            ]);

        $rawTableName = $this->db->schema->getRawTableName('{{%v3_pgcustom}}');
        $enumTypeName = 'enum_'.$rawTableName.'_status';
        $this->execute('CREATE TYPE "'.$enumTypeName.'" AS ENUM(\'active\', \'draft\')');
        $this->createTable('{{%v3_pgcustom}}',
            [
                'id' => $this->bigPrimaryKey(),
                'num' => $this->integer()->defaultValue(0),
                'json1' => $this->json(),
                'json2' => $this->json()->null()->defaultValue(null),
                'json3' => $this->json()->defaultValue(Json::encode(['foo' => 'bar', 'bar' => 'baz'])),
                'json4' => "json DEFAULT '" . new Expression(Json::encode(['ffo' => 'bar'])) . "'",
                'status' => '"'.$enumTypeName.'"',
                'status_x' => 'varchar(10)',
                'search' => 'tsvector'
            ]);
        $columns = [
            'field_' . Schema::TYPE_PK => $this->primaryKey(),
            'field_' . Schema::TYPE_CHAR => $this->char(),
            'field_' . Schema::TYPE_STRING => $this->string(),
            'field_' . Schema::TYPE_TEXT => $this->text(),
            'field_' . Schema::TYPE_TINYINT => $this->tinyInteger(),
            'field_' . Schema::TYPE_SMALLINT => $this->smallInteger(),
            'field_' . Schema::TYPE_INTEGER => $this->integer(),
            'field_' . Schema::TYPE_BIGINT => $this->bigInteger(),
            'field_' . Schema::TYPE_FLOAT => $this->float(),
            'field_' . Schema::TYPE_DOUBLE => $this->double(),
            'field_' . Schema::TYPE_DECIMAL => $this->decimal(),
            'field_' . Schema::TYPE_DATETIME => $this->dateTime(),
            'field_' . Schema::TYPE_TIMESTAMP => $this->timestamp(),
            'field_' . Schema::TYPE_TIME => $this->time(),
            'field_' . Schema::TYPE_DATE => $this->date(),
            'field_' . Schema::TYPE_BINARY => $this->binary(),
            'field_' . Schema::TYPE_BOOLEAN => $this->boolean(),
            'field_' . Schema::TYPE_MONEY => $this->money(),
        ];
        $this->createTable('{{%default_sizes}}', $columns);
    }

    public function safeDown()
    {
        $this->dropTable('{{%v2_fakerable}}');
        $this->dropForeignKey('fk_v2_comments_author_id_v2_users_id', '{{%v2_comments}}');
        $this->dropForeignKey('fk_v2_comments_post_id_v2_posts_uid', '{{%v2_comments}}');
        $this->dropTable('{{%v2_comments}}');
        $this->dropForeignKey('fk_v2_posts_created_by_id_v2_users_id', '{{%v2_posts}}');
        $this->dropForeignKey('fk_v2_posts_category_id_v2_categories_id', '{{%v2_posts}}');
        $this->dropTable('{{%v2_posts}}');
        $this->dropTable('{{%v2_users}}');
        $this->dropTable('{{%v2_categories}}');
        $this->dropTable('{{%v3_pgcustom}}');
        $rawTableName = $this->db->schema->getRawTableName('{{%v3_pgcustom}}');
        $this->execute('DROP TYPE "enum_'.$rawTableName.'_status"');
        $this->dropTable('{{%default_sizes}}');
    }
}
