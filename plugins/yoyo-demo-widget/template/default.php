<h3>Yoyo Demo Widget</h3>

<?php
$yoyoComponents = [
	['heading' => 'Posts', 'name' => 'posts'],
	['heading' => 'Counter', 'name' => 'counter'],
	['heading' => 'Image Upload', 'name' => 'upload'],
];
?>

<div style="margin: 20px; display: flex; flex-flow: row wrap; justify-content: space-between;">

<?php foreach($yoyoComponents as $component): ?>

	<div style="flex: 0 0 33%; padding: 0 1rem;">

		<h3><?php echo $component['heading']; ?></h3>

		<div style="margin: 2rem 0 3rem 0;">
			<?php echo Yoyo\yoyo_render($component['name'],[
				'yoyo:source'=>'plugin.yoyo-demo-widget'
			]); ?>
		</div>

	</div>

<?php endforeach;