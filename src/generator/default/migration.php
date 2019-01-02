<?= '<?php' ?>


/**
 * <?= $description ?>

 */
class <?= $className ?> extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('<?= $tableName ?>', [
<?php foreach ($attributes as $attribute): ?>
            '<?= $attribute['dbName'] ?>' => '<?= $attribute['dbType'] ?>',
<?php endforeach; ?>
        ]);

        // TODO generate foreign keys
    }

    public function down()
    {
        $this->dropTable('<?= $tableName ?>');
    }
}
