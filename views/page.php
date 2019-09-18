<div class="wrap">		
	<h2>Continent Filter</h2>
	<div id="poststuff">
		
		<form method="post">

			<div class="form-group">
				<label><strong>Available continents</strong></label>
				<div class="form-field">
					<?php foreach( $this->continent_codes as $code => $name ) { ?>

						<div><input type="checkbox" name="kd_available_continents[]" value="<?php echo $code ?>"> <?php echo $name ?></div>

					<?php } ?>
				</div>
			</div>
			<div class="form-group" style="margin-top: 20px;">
				<label><strong>Continent home pages</strong></label>
				<div class="form-field">
					<?php foreach( $this->continent_codes as $code => $name ) { ?>

						<div><?php echo $name ?>: <input type="text" style="width: 100%;max-width: 300px;" name="kd_continent_homes[<?php echo $code ?>]"></div>

					<?php } ?>
				</div>
			</div>
			<button name="kd_submit" style="margin-top: 30px;" class="button button-primary" type="submit">Save</button>
		</form>

	</div>
</div>