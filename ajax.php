<?php if(!defined('_MY')) exit('Goodbye!');

$json = array('status' => 'error', 'message' => 'this is default error', 'data' => array(), 'html' => '');

$mode = '';
if(isset($_POST['mode']))
{
	$mode = $_POST['mode'];
}

$data = '';
if(isset($_POST['data']))
{
	parse_str($_POST['data'], $data);
}

switch($mode)
{
	case 'init':
		$response = $converter->get_currency();
		
		if(is_array($response) && count($response))
		{
			$json['status'] = 'success';
			$json['data']['in_currency_html'] = '';
			
			foreach($response as $currency)
			{
				$json['data']['in_currency_html'] .= '<option value="'.$currency['url'].'">'.$currency['name'].'</option>';		  
			}
		}
	break;
	
	case 'history':
		$response = $converter->get_history();
		
		if(is_array($response) && count($response))
		{
			$json['status'] = 'success';
			
			foreach($response as $time => $data)
			{
				$json['html'] .= '<div class="uk-grid-small" uk-grid>';
					$json['html'] .= '<div class="uk-width-expand" data-uk-leader="fill: ."><span uk-icon="history"></span> '.date('Y-m-d H:i:s', $time).'</div>';
					$json['html'] .= '<div><a href="#" title="View row" data-history-view data-history-time="'.$time.'">'.$data->args[2].' '.$data->in->abbr.' to '.$data->out->abbr.'</a></div>';
				$json['html'] .= '</div>';
			}
		}
	break;
	
	case 'history_item':
		if(isset($_POST['time']))
		{
			$response = $converter->view_history_item($_POST['time']);
		
			if(is_object($response))
			{
				$json['status']  = 'success';
				$json['message'] = date('Y-m-d H:i:s', $response->time).': '.$response->formula;
			}
		}
	break;
	
	case 'out':
		if(isset($data['in']['currency']))
		{
			$response = $converter->get_currency();
			
			if(is_array($response) && count($response))
			{
				$in_currency_key = '';
				
				foreach($response as $currency_key => $currency)
				{
					if($currency['url'] == $data['in']['currency'])
					{
						$in_currency_key = $currency_key;
						break;
					}
				}
				
				if($in_currency_key)
				{
					$rates = $converter->get_rates($in_currency_key);
					
					if(is_object($rates))
					{
						$response = $converter->get_currency();
						
						if(is_array($response) && count($response))
						{
							$json['data']['out_currency_html'] = '';
						
							foreach($response as $currency_key => $currency)
							{
								if(isset($rates->{$currency_key}))
								{
									$json['data']['out_currency_html'] .= '<option value="'.$currency['url'].'">'.$currency['name'].'</option>';
								}		  
							}
							
							if(!empty($json['data']['out_currency_html']))
							{
								$json['status'] = 'success';
							}
						}
					}
					else
					{
						$json['message'] = 'no rates! start cron for get rates';
					}
				}
			}
		}		
	break;
	
	case 'calc':
		if(isset($data['in']['currency']) && isset($data['out']['currency']))
		{
			$allcurrency = $converter->get_currency();
			
			if(is_array($allcurrency) && count($allcurrency))
			{
				$in_currency_key = '';
				$out_currency_key = '';
				
				foreach($allcurrency as $currency_key => $currency)
				{
					if($currency['url'] == $data['in']['currency'])
					{
						$in_currency_key = $currency_key;
					}
					
					if($currency['url'] == $data['out']['currency'])
					{
						$out_currency_key = $currency_key;
					}
				}
				
				
				if($in_currency_key && $out_currency_key)
				{
					$json['data']['direct'] = '';
						
					if(isset($_POST['direct']))
					{
						$json['data']['direct'] = $_POST['direct'];
					}
					
					$json['data']['calcresult'] = 0;
					$json['data']['calcformula'] = '';
					$json['data']['calcdesc'] = '';
					$json['data']['value'] = 0;
					
					switch($json['data']['direct'])
					{
						//out to in
						case 'invert':
							if(isset($data['out']['value']))
							{
								$json['data']['value'] = $data['out']['value'];
							}
						break;
						
						//in to out
						default:
							if(isset($data['in']['value']))
							{
								$json['data']['value'] = $data['in']['value'];
							}
						break;
					}
					
					$response = $converter->calculate($in_currency_key, $out_currency_key, $json['data']['value'], $json['data']['direct']);
					
					if(is_array($response))
					{
						$json['data']['calcresult'] = $response['result'];
						$json['data']['calcformula'] = $response['formula'];
						
						$json['data']['calcdesc'] .= '<div class="uk-child-width-1-2 uk-grid-divider uk-flex-middle uk-grid-match" data-uk-grid>';
						$json['data']['calcdesc'] .= '<div>How much is '.$json['data']['value'].' '.$allcurrency[$in_currency_key]['name'].' in '.$allcurrency[$out_currency_key]['name'].'?</div>';
						$json['data']['calcdesc'] .= '<div>'.$json['data']['calcformula'].'</div>';
						$json['data']['calcdesc'] .= '</div>';
					}
					
					if($json['data']['calcresult'] > 0)
					{
						$json['status'] = 'success';
					}
				}
			}		
		}
	break;
} 

die(json_encode($json));
?>