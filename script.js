$(document).ready(function(){
	
	/*
	vars
	*/
	
	
	var conv_selector = '.converter';
	var conv_element = $(conv_selector);
	
	
	/*
	funcs
	*/
	
	
	var conv_wait = function(mode){
		switch(mode)
		{
			case 'set':
				conv_element.addClass('wait');
				conv_element.find('button, input, textarea, select, radio').prop('disabled',true);
				conv_element.css('opacity', '0.8');
			break;
			
			case 'unset':
				conv_element.removeClass('wait');
				conv_element.find('button, input, textarea, select, radio').prop('disabled',false);
				conv_element.css('opacity', '1');
			break;
			
			case 'is_wait':
				if(conv_element.hasClass('wait'))
				{
					return true;
				}
				else
				{
					return false;
				}
			break;
		}
	}
	
	var conv_history_item = function(time){
		
		conv_wait('set');
		
		$.post(js_params.url_ajax, {mode: 'history_item', time: time}, function(result) {
			
			conv_wait('unset'); 
			
			if(result.status == 'success')
			{
				alert(result.message);
			}			
			
		}, 'json').fail(function() {
			
			conv_wait('unset'); 
			
		});
	}
	
	var conv_history = function(){
		
		conv_wait('set');
		
		$.post(js_params.url_ajax, {mode: 'history'}, function(result) {
			
			conv_wait('unset'); 
			
			if(result.status == 'success')
			{
				if(!$('.calchistory').length)
				{
					conv_element.after('<hr /><div class="calchistory uk-text-small uk-text-muted">'+result.html+'</div>');
				}
				else
				{
					$('.calchistory').html(result.html);
				}
			}
			
		}, 'json').fail(function() {
			
			conv_wait('unset'); 
			
		});
	}
	
	var conv_start = function(direct){
		
		var direct = (typeof direct !== 'undefined') ?  direct : '';
		
		var data = {
			mode: 'calc',
			data: conv_element.find('form').serialize(),
			direct: direct,
		};
		
		conv_wait('set');
		
		$.post(js_params.url_ajax, data, function(result) {
			conv_wait('unset'); 
			
			if(result.status == 'success')
			{
				switch(direct)
				{
					case 'invert':
						conv_element.find('input[name="in[value]"]').val(result.data.calcresult);
					break;
					
					default:
						conv_element.find('input[name="out[value]"]').val(result.data.calcresult);
					break;
				}
				
				//calcformula
				if(!$('.calcformula').length)
				{
					conv_element.before('<div class="calcformula uk-text-small uk-text-muted">'+result.data.calcdesc+'</div><hr />');
				}
				else
				{
					$('.calcformula').html(result.data.calcdesc);
				}
				
				//calchistory
				conv_history();
			}
			else
			{
				alert(result.status+': '+result.message);
			}
			
		}, 'json').fail(function() {
			
			alert('error > conv_start > :(');
			conv_wait('unset'); 
			
		});
	}
	
	var conv_loadout = function(){
		var data = {
			mode: 'out',
			data: conv_element.find('form').serialize(),
		};
		
		conv_wait('set');
		
		$.post(js_params.url_ajax, data, function(result) {
			conv_wait('unset'); 
			
			if(result.status == 'success')
			{
				conv_element.find('select[name="out[currency]"]').html(result.data.out_currency_html);
				conv_element.find('select[name="out[currency]"]').val($('select[name="out[currency]"] option:first').val());
				
				conv_start();
			}
			else
			{
				alert(result.status+': '+result.message);
			}
			
		}, 'json').fail(function() {
			
			alert('error > conv_loadout > :(');
			conv_wait('unset'); 
			
		});
	}
	
	
	/*
	defender
	*/
	
	
	if(!conv_element.length)
	{
		return false;
	}
	
	
	/*
	init
	*/
	
	
	conv_wait('set');
	
	$.post(js_params.url_ajax, {mode: 'init'}, function(result) {
		
		conv_wait('unset'); 
		
		if(result.status == 'success')
		{
			conv_element.find('select[name="in[currency]"]').html(result.data.in_currency_html);
			conv_element.find('input[name="in[value]"]').val('1');
			conv_element.find('select[name="in[currency]"]').val($('select[name="in[currency]"] option:first').val());
			
			conv_loadout();
		}
		else
		{
			alert(result.status+': '+result.message);
		}
		
	}, 'json').fail(function() {
		
		alert('error > init > :(');
		conv_wait('unset'); 
		
	});
	
	
	/*
	logic
	*/
	
	//
	$('body').on('click', conv_selector+' form button', function(e) {
		e.preventDefault();
		conv_start();
	});
	
	//
	$('body').on('change', conv_selector+' select[name="in[currency]"], '+conv_selector+' select[name="out[currency]"]', function(e) {
		e.preventDefault();
		conv_start();
	});
	
	//
	var conv_keyup_out_delay;
	
	$('body').on('keyup', conv_selector+' input[name="out[value]"]', function(e) {
		e.preventDefault();	
		
		clearTimeout(conv_keyup_out_delay);
		
		conv_keyup_out_delay = setTimeout(function() {
			conv_start('invert');
		}, 700);
	});
	
	//
	$('body').on('click', '[data-history-view]', function(e) {
		e.preventDefault();
		
		if(conv_wait('is_wait'))
		{
			alert('please wait the page has not loaded yet');
			return false;
		}
		
		var time = $(this).attr('data-history-time');
		
		conv_history_item(time);
	});
});