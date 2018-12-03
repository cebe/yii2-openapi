<?= '<?php' ?>


namespace <?= $namespace ?>;

/**
 * <?= $description ?>

 *
<?php foreach($attributes as $attribute): ?>
 * @var <?= $attribute['type'] ?? 'mixed' ?> $<?= rtrim($attribute['name'] . ' ' . $attribute['description']) ?>

<?php endforeach; ?>
 */
class <?= $className ?> extends \yii\db\ActiveRecord
{
    // TODO implement

<?php foreach($relations as $relationName => $relation): ?>
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
