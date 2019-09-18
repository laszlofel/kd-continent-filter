<div class="form-field term-continent-homepage-wrap">
	<label for="tag-continent-homepage">Kontinens kezdőlap</label>
	<select name="kd_continent_homepage" style="width: 95%;">
		<?php foreach( $posts as $post ) { ?>
			<option value="<?php $post->ID ?>"><?php echo $post->post_title ?> (<?php echo $post->post_name ?>)</option>
		<?php } ?>
	</select>
	<p>A kontinenshez tartozó kezdőlap.</p>
</div>