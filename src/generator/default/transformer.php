<?php
/**
 * @var string $namespace
 * @var \cebe\yii2openapi\lib\items\Transformer $transformer
*/
use yii\helpers\Inflector;

echo '<?php';
?>

namespace <?= $namespace ?>;

use League\Fractal\TransformerAbstract;
use <?=$transformer->modelFQN?>;

class <?=$transformer->name?> extends TransformerAbstract
{
<?php if (!empty($transformer->availableRelations)):?>
    protected $availableIncludes = ['<?=implode("', '", $transformer->availableRelations)?>'];
<?php else:?>
    protected $availableIncludes = [];
<?php endif;?>
<?php if (!empty($transformer->defaultRelations)):?>
    protected $defaultIncludes = ['<?=implode("', '", $transformer->defaultRelations)?>'];
<?php else:?>
    protected $defaultIncludes = [];
<?php endif;?>

    public function transform(<?=$transformer->dbModel->getClassName()?> $model)
    {
        return $model->getAttributes();
    }
<?php if ($transformer->shouldIncludeRelations()):?>
<?php foreach ($transformer->relations as $relation):?>

    public function include<?=$relation->getCamelName()?>(<?=$transformer->dbModel->getClassName()?> $model)
    {
        $relation = $model-><?=Inflector::variablize($relation->getName())?>;
        $transformer = new <?=Inflector::singularize($relation->getClassName())?>Transformer();
<?php if ($relation->isHasOne()):?>
        return $this->item($relation, $transformer, '<?=$transformer->makeResourceKey($relation->getClassKey())?>');
<?php else:?>
        return $this->collection($relation, $transformer, '<?=$transformer->makeResourceKey($relation->getClassKey())?>');
<?php endif;?>
    }
<?php endforeach;?>
<?php foreach ($transformer->many2Many as $relation):?>

    public function include<?=$relation->getCamelName()?>(<?=$transformer->dbModel->getClassName()?> $model)
    {
        $relation = $model-><?=Inflector::variablize($relation->name)?>;
        $transformer = new <?=Inflector::singularize($relation->getRelatedClassName())?>Transformer();
        return $this->collection($relation, $transformer, '<?=$transformer->makeResourceKey($relation->relatedSchemaName)?>');
    }
<?php endforeach;?>
<?php endif;?>
}
