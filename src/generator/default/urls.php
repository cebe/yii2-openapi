<?= '<?php' ?>

/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
<?php $rules = \yii\helpers\VarDumper::export($urls);?>
return <?= str_replace('\\\\', '\\', $rules); ?>;
