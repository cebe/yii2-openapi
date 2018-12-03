<?php

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator \cebe\yii2openapi\generator\ApiGenerator */

echo $form->field($generator, 'openApiPath')->error(['encode' => false]);
echo $form->field($generator, 'ignoreSpecErrors')->checkbox();
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $form->field($generator, 'generateUrls')->checkbox() ?>
    </div>
    <div class="panel-body">
        <?= $form->field($generator, 'urlConfigFile') ?>
    </div>
</div>
<?php
echo $form->field($generator, 'generateControllers')->checkbox();
echo $form->field($generator, 'generateModels')->checkbox();
echo $form->field($generator, 'generateMigrations')->checkbox();

$this->registerCss(<<<CSS
    .panel-heading .form-group,
    .panel-heading .form-group label,
    .panel-heading .form-group .help-block {
        margin-bottom: 0;
    } 
CSS
);

$this->registerJs(<<<JS
    
    togglePanel = function() {
        $(this).parents('.panel').find('.panel-body input').prop('disabled', !this.checked);
        $(this).parents('.panel').find('.panel-body label').prop('disabled', !this.checked);
        if (this.checked) {
            $(this).parents('.panel').find('.panel-body').slideDown();
        } else {
            $(this).parents('.panel').find('.panel-body').slideUp();
        }
    };
    $('.panel-heading .form-group input[type=checkbox]').each(togglePanel);
    $('.panel-heading .form-group input[type=checkbox]').on('click', togglePanel);
    
JS
);





