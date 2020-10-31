<ul style="margin: 10px 0; padding-left: 20px;">
	
	<?php if (! $this->posts): ?>
		<li>No results</li>
	<?php endif; ?>

	<?php foreach ($this->posts as $post): ?>
		<li>
			<a href="<?php echo get_permalink($post); ?>">
				<?php echo $post->post_title; ?>
			</a>
		</li>
	<?php endforeach; ?>

</ul>

<div style="margin-top: 10px">

	<button 
		<?php echo !$this->previous ? 'disabled' : ''; ?>
		yoyo:vars="pg: <?php echo $this->previous; ?>"
	>
		Previous
	</button>

	<button 
		yoyo:vars="pg: <?php echo $this->next; ?>"
	>
		Next
	</button>

</div>
