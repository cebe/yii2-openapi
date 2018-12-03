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

}
