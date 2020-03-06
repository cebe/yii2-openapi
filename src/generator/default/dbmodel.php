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

    public function rules()
    {
        return [
<?php
    $safeAttributes = [];
    $requiredAttributes = [];
    $integerAttributes = [];
    $stringAttributes = [];

    foreach ($attributes as $attribute) {
        if ($attribute['readOnly']) {
            continue;
        }
        if ($attribute['required']) {
            $requiredAttributes[$attribute['name']] = $attribute['name'];
        }
        switch ($attribute['type']) {
            case 'integer':
                $integerAttributes[$attribute['name']] = $attribute['name'];
                break;
            case 'string':
                $stringAttributes[$attribute['name']] = $attribute['name'];
                break;
            default:
            case 'array':
                $safeAttributes[$attribute['name']] = $attribute['name'];
                break;
        }
    }
    if (!empty($stringAttributes)) {
        echo "            [['" . implode("', '", $stringAttributes) . "'], 'trim'],\n";
    }
    if (!empty($requiredAttributes)) {
        echo "            [['" . implode("', '", $requiredAttributes) . "'], 'required'],\n";
    }
    if (!empty($stringAttributes)) {
        echo "            [['" . implode("', '", $stringAttributes) . "'], 'string'],\n";
    }
    if (!empty($integerAttributes)) {
        echo "            [['" . implode("', '", $integerAttributes) . "'], 'integer'],\n";
    }
    if (!empty($safeAttributes)) {
        echo "            // TODO define more concreate validation rules!\n";
        echo "            [['" . implode("','", $safeAttributes) . "'], 'safe'],\n";
    }

?>
        ];
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
