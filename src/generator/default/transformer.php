<?php
/**
 * @var string $namespace (current namespace, maby be base, if transformers are extendable)
 * @var string $mainNamespace  (main namespace)
 * @var bool $extendable
 * @var \cebe\yii2openapi\lib\items\Transformer $transformer
*/
use yii\helpers\Inflector;

echo '<?php';
?>

namespace <?= $namespace ?>;

use League\Fractal\TransformerAbstract;
use <?=$transformer->modelFQN?>;
<?php if ($extendable === true && $transformer->shouldIncludeRelations()):?>
<?php foreach ($transformer->getUniqueTransformerClasses() as $transformerClass):?>
use <?=$mainNamespace?>\<?=$transformerClass?>;
<?php endforeach;?>
<?php endif;?>

class <?=$transformer->name?> extends TransformerAbstract
{
<?php if (!empty($transformer->availableRelations)):?>
    protected array $availableIncludes = ['<?=implode("', '", $transformer->availableRelations)?>'];
<?php else:?>
    protected array $availableIncludes = [];
<?php endif;?>
<?php if (!empty($transformer->defaultRelations)):?>
    protected array $defaultIncludes = ['<?=implode("', '", $transformer->defaultRelations)?>'];
<?php else:?>
    protected array $defaultIncludes = [];
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
<?php if ($relation->isHasOne()):?>
        if ($relation === null) {
            return $this->null();
        }
<?php if ($relation->getClassName() === $transformer->dbModel->getClassName()):?>
        $transformer = new static();
<?php else:?>
        $transformer = new <?=Inflector::singularize($relation->getClassName())?>Transformer();
<?php endif;?>
        return $this->item($relation, $transformer, '<?=$transformer->makeResourceKey($relation->getClassKey())?>');
<?php else:?>
<?php if ($relation->getClassName() === $transformer->dbModel->getClassName()):?>
        $transformer = new static();
<?php else:?>
        $transformer = new <?=Inflector::singularize($relation->getClassName())?>Transformer();
<?php endif;?>
        return $this->collection($relation, $transformer, '<?=$transformer->makeResourceKey($relation->getClassKey())?>');
<?php endif;?>
    }
<?php endforeach;?>
<?php foreach ($transformer->nonDbRelations as $relation):?>

    public function include<?=$relation->getCamelName()?>(<?=$transformer->dbModel->getClassName()?> $model)
    {
        $relation = $model-><?=Inflector::variablize($relation->getName())?>;
<?php if ($relation->isHasOne()):?>
        if ($relation === null) {
            return $this->null();
        }
<?php if ($relation->getClassName() === $transformer->dbModel->getClassName()):?>
        $transformer = new static();
<?php else:?>
        $transformer = new <?=Inflector::singularize($relation->getClassName())?>Transformer();
<?php endif;?>
        return $this->item($relation, $transformer, '<?=$transformer->makeResourceKey($relation->getClassKey())?>');
<?php else:?>
<?php if ($relation->getClassName() === $transformer->dbModel->getClassName()):?>
        $transformer = new static();
<?php else:?>
        $transformer = new <?=Inflector::singularize($relation->getClassName())?>Transformer();
<?php endif;?>
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
