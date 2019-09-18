<select name="kd_continent_code" class="widefat" style="margin-top: 10px;display: block;">
	<?php foreach( $this->continent_codes as $code => $name ) { ?>
		<option value="<?php echo $code ?>" <?php selected( $code, get_post_meta( $post->ID, 'kd_continent_code', true ) ) ?>><?php echo $name ?></option>
	<?php } ?>
</select>