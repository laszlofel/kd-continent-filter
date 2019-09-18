<tr class="form-field term-continent-homepage-wrap">
	<th scope="row"><label for="slug">Kontinens kezdőlap</label></th>
	<td>
		<select name="kd_continent_homepage" style="width: 95%;">
			<option value=""></option>
			<?php foreach( $posts as $post ) { var_dump( $term->term_id, get_post_meta( $post->ID ) ); ?>
				<option value="<?php echo $post->ID ?>" <?php selected( $term->term_id, get_post_meta( $post->ID, 'kd_continent_homepage_of', true ) ) ?>><?php echo $post->post_title ?> (<?php echo $post->post_name ?>)</option>
			<?php } ?>
		</select>
		<p class="description">A kontinenshez tartozó kezdőlap.</p>
	</td>
</tr>