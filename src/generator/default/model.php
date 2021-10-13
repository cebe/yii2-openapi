<?= '<?php' ?>


namespace <?= $namespace ?>;

use yii\base\Model;

/**
 * <?= str_replace("\n", "\n * ", trim($description)) ?>

 *
 */
class <?= $className ?> extends Model
{
<?php foreach ($attributes as $attribute): ?>
    /**
     * @var <?= trim(($attribute['type'] ?? 'mixed') . ' ' . str_replace("\n", "\n     * ", rtrim($attribute['description']))) ?>

     */
    public $<?= $attribute['name'] ?>;
<?php endforeach; ?>


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
}
