<?php
/**
 * @var \cebe\yii2openapi\lib\items\MigrationModel $migration
 * @var string $namespace
 * @var bool $isTransactional
 **/
?>
<?= '<?php' ?>

<?php if (isset($namespace)) {
    echo "\nnamespace $namespace;\n";
} ?>

/**
 * <?= $migration->getDescription() ?>

 */
class <?= $migration->fileClassName ?> extends \yii\db\Migration
{
    public function <?=$isTransactional? 'safeUp':'up'?>()
    {
<?= $migration->upCodeString ?>

    }

    public function <?=$isTransactional? 'safeDown':'down'?>()
    {
<?= $migration->downCodeString ?>

    }
}
