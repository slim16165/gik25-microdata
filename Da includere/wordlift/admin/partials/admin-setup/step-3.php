<!-- Pane 3 content -->
<script type="text/html" id="page-2">
	<h2 class="page-title">
		<?php esc_html_e( 'License Key', 'wordlift' ); ?>
	</h2>

	<p class="page-txt">
		<?php
		esc_html_e( 'Local mode: License key is optional. You can leave this field empty or enter any value. The plugin will work in local-only mode without cloud services.', 'wordlift' );
		?>
	</p>
	<input
		type="text"
		data-wl-key="wl-key"
		class="valid untouched"
		id="key"
		name="key"
		value="local-mode"
		autocomplete="off"
		placeholder="<?php echo esc_attr_x( 'License Key (optional)', 'Input text placeholder', 'wordlift' ); ?>"
	>
	<div>
		<p class="wl-val-key-error">
			
		</p>
	</div>
	<div class="btn-wrapper">
		<input
			id="btn-license-key-next"
			type="button"
			data-wl-next="wl-next"
			class="button wl-default-action"
			value="<?php esc_attr_e( 'Next', 'wordlift' ); ?>"
		>
	</div>
</script>
