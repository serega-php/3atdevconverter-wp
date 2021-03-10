<?php
/*
 * Plugin Name: WP Converter
 */
 
function WPConverter_head()
{
	$html = '  
    <link rel="stylesheet" href="https://static.onpub.ru/uikit/uikit-3.4.2/css/uikit.min.css" />
	<script src="https://static.onpub.ru/uikit/uikit-3.4.2/js/uikit.min.js"></script>
	<script src="https://static.onpub.ru/uikit/uikit-3.4.2/js/uikit-icons.min.js"></script>
    
    <script>
		var js_params = {
		   url_ajax: \''.site_url().'/wp-content/plugins/wp-converter/index.php?ajax\',
		   }; 
	</script>
	
	<script src="'.site_url().'/wp-content/plugins/wp-converter/script.js"></script>
	';
	
	$html = trim($html);
	
	echo $html;
}

function WPConverter_footer()
{
	$html = '
	<div class="uk-section">
	  <div class="uk-container">
		  <div class="converter uk-padding uk-background-primary uk-light">
			  <form>
				  <div class="uk-grid uk-flex-middle" data-uk-grid>
					  <div class="uk-width-1-2 uk-width-expand@m">
						  <input class="uk-input" name="in[value]" value="" type="text" placeholder="" autocomplete="off">
					  </div>
					  <div class="uk-width-1-2 uk-width-1-4@m">
						  <select class="uk-select" name="in[currency]">
						  </select>	
					  </div>
					  <div class="uk-width-1-1 uk-width-auto@m uk-text-center">
						  <button class="uk-button uk-button-default"><span class="uk-hidden@m" data-uk-icon="icon: chevron-down;"></span><span class="uk-visible@m" data-uk-icon="icon: chevron-right;"></span></button>
					  </div>
					  <div class="uk-width-1-2 uk-width-expand@m">
						  <input class="uk-input" name="out[value]" value="" type="text" placeholder="" autocomplete="off">
					  </div>
					  <div class="uk-width-1-2 uk-width-1-4@m">
						  <select class="uk-select" name="out[currency]">
						  </select>	
					  </div>
				  </div>
			  </form>
		  </div>
	  </div>
	</div>
	';
	
	$html = trim($html);
	
	echo $html;
}
 
add_action('wp_head', 'WPConverter_head');
add_action('wp_footer', 'WPConverter_footer');
?>