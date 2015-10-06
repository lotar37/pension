<?php
/* @var $this RanksController */
/* @var $model Ranks */

$this->breadcrumbs=array(
	'Ranks'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List Ranks', 'url'=>array('index')),
	array('label'=>'Create Ranks', 'url'=>array('create')),
	array('label'=>'Update Ranks', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Ranks', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Ranks', 'url'=>array('admin')),
);
?>

<h1>View Ranks #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'rubr',
		'full_name',
		'print_name',
	),
)); ?>
