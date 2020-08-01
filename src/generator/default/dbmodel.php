<?php
/**
 * @var \cebe\yii2openapi\lib\items\DbModel $model
 * @var string $namespace
 * @var string $relationNamespace
 **/
?>
<?= '<?php' ?>


namespace <?= $namespace ?>;

/**
 * <?= str_replace("\n", "\n * ", trim($model->description)) ?>

 *
<?php foreach ($model->attributes as $attribute): ?>
 * @property <?= $attribute->getFormattedDescription() ?>

<?php endforeach; ?>
 *
<?php foreach ($model->relations as $relationName => $relation): ?>
<?php if ($relation->isHasOne()):?>
 * @property \<?= trim($relationNamespace, '\\') ?>\<?= $relation->getClassName() ?> $<?= $relationName ?>
<?php else:?>
 * @property array|\<?= trim($relationNamespace, '\\') ?>\<?= $relation->getClassName() ?>[] $<?= $relationName ?>
<?php endif?>

<?php endforeach; ?>
 */
abstract class <?= $model->name ?> extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return <?= var_export($model->getTableAlias()) ?>;
    }

    public function rules()
    {
        return [
<?php
    $attributesByType = $model->getAttributesByType();
    if (!empty($attributesByType['string'])) {
        echo "            [['" . implode("', '", $attributesByType['string']) . "'], 'trim'],\n";
    }
    if (!empty($attributesByType['required'])) {
        echo "            [['" . implode("', '", $attributesByType['required']) . "'], 'required'],\n";
    }

    if (!empty($attributesByType['int'])) {
        echo "            [['" . implode("', '", $attributesByType['int']) . "'], 'integer'],\n";
    }
    foreach ($attributesByType['ref'] as $relation) {
        echo "            [['" . $relation['attr'] . "'], 'exist', 'targetRelation'=>'".$relation['rel']."'],\n";
    }

    if (!empty($attributesByType['string'])) {
        echo "            [['" . implode("', '", $attributesByType['string']) . "'], 'string'],\n";
    }

    if (!empty($attributesByType['float'])) {
        echo "            [['" . implode("', '", $attributesByType['float']) . "'], 'double'],\n";
    }
    if (!empty($attributesByType['bool'])) {
        echo "            [['" . implode("', '", $attributesByType['bool']) . "'], 'boolean'],\n";
    }
    if (!empty($attributesByType['safe'])) {
        echo "            // TODO define more concrete validation rules!\n";
        echo "            [['" . implode("','", $attributesByType['safe']) . "'], 'safe'],\n";
    }

?>
        ];
    }

<?php foreach ($model->relations as $relationName => $relation): ?>
    public function get<?= $relation->getCamelName() ?>()
    {
        return $this-><?= $relation->getMethod() ?>(\<?= trim($relationNamespace, '\\') ?>\<?= $relation->getClassName() ?>::class,<?php
            echo str_replace(
                    [',', '=>', ', ]'],
                    [', ', ' => ', ']'],
                    preg_replace('~\s+~', '', \yii\helpers\VarDumper::export($relation->getLink()))
            )
        ?>);
    }
<?php endforeach; ?>
}
