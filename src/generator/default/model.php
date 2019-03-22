<?= '<?php' ?>


namespace <?= $namespace ?>;

/**
 * <?= str_replace("\n", "\n * ", trim($description)) ?>

 *
<?php foreach ($attributes as $attribute): ?>
 * @property <?= $attribute['type'] ?? 'mixed' ?> $<?= str_replace("\n", "\n * ", rtrim($attribute['name'] . ' ' . $attribute['description'])) ?>

<?php endforeach; ?>
 */
class <?= $className ?> extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return <?= var_export($tableName) ?>;
    }

<?php foreach ($relations as $relationName => $relation): ?>
    public function get<?= ucfirst($relationName) ?>()
    {
        return $this-><?= $relation['method'] ?>(<?= $relation['class'] ?>::class, <?php
            echo str_replace(
                    [',', '=>', ', ]'],
                    [', ', ' => ', ']'],
                    preg_replace('~\s+~', '', \yii\helpers\VarDumper::export($relation['link']))
            )
        ?>);
    }

<?php endforeach; ?>
}
