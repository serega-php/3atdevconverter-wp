<?php 
class Converter
{
    var $dir = array(
		'history'   => '_history',
		'exchrates' => '_exchrates',
	);
	
	var $currency = array(
		'btc' => array(			
			'name' => 'Bitcoin',
			'abbr' => 'BTC', 
			'url'  => 'btc', 
			'dir'  => '_btc', 
			'logo' => 'https://uploads.onpub.ru/3atdev/btc.png',
			'grab' => array(
				'graburl' => '',
				'cookie'  => array('currency' => 'BTC'),
			),
			'pars' => array(
				'needle' => '"/currencies/bitcoin/"',
			),			
		),
		'eth' => array(			
			'name' => 'Ethereum',
			'abbr' => 'ETH', 
			'url'  => 'eth',
			'dir'  => '_eth', 
			'logo' => 'https://uploads.onpub.ru/3atdev/eth.png',
			'grab' => array(
				'graburl' => '',
				'cookie'  => array('currency' => 'ETH'),
			),
			'pars' => array(
				'needle'  => '"/currencies/ethereum/"',
			),
		),
		'ada' => array(			
			'name' => 'Cardano',
			'abbr' => 'ADA', 
			'url'  => 'ada',
			'dir'  => '_ada',
			'logo' => 'https://uploads.onpub.ru/3atdev/ada.png',
			'grab' => '',
			'pars' => array(
				'needle' => '"/currencies/cardano/"',
			),
		),
		'usdt' => array(			
			'name' => 'Tether',
			'abbr' => 'USDT', 
			'url'  => 'usdt',
			'dir'  => '_usdt',
			'logo' => '',
			'grab' => '',
			'pars' => array(
				'needle' => '"/currencies/tether/"',
			),
		),
	);
	
	var $timename_file_ext = 'txt';
	
	// --------------------------------------------------------------------
	
	public $ready, $historylimit, $cronmaxiteration, $crondelaygrabsec;
	
	// --------------------------------------------------------------------
	
	private $basepath;
	
	// --------------------------------------------------------------------
	
	protected function _initialization()
    {
        $this->ready = TRUE;
		
		if(!is_dir($this->basepath))
		{
			$this->ready = FALSE;
		}
		
		if(!is_dir($this->basepath.$this->dir['history']))
		{
			if(!$this->_create_dir($this->basepath.$this->dir['history']))
			{
				$this->ready = FALSE;
			}
		}
		
		if(!is_dir($this->basepath.$this->dir['exchrates']))
		{
			if(!$this->_create_dir($this->basepath.$this->dir['exchrates']))
			{
				$this->ready = FALSE;
			}
		}
		
		if(!file_exists($this->basepath.'/index.html'))
		{
			$this->_create_file($this->basepath.'/index.html', '<html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
		}
    }
	
	public function __construct($params = '')
    {
        $this->ready = NULL;
		
		$this->basepath = dirname(__FILE__).'/';
		
		if(isset($params['basepath']) && is_string($params['basepath']))
		{
			if(is_dir($params['basepath']))
			{
				$this->basepath = $params['basepath'];
			}			
		}
		
        $this->_initialization();
    }
	
	// --------------------------------------------------------------------
	
	private function _create_dir($path)
	{
		if(mkdir($path, 0755))
		{
			$this->_create_file($path.'/index.html', 'Who are you? I didn\'t call you!');
			
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function _create_file($path, $content)
	{
		$fr = fopen($path,'w');
		fwrite($fr, $content);
		
		if(fclose($fr))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function _clean_overflow_timename_files($path, $max = 10)
	{
		$files = scandir($path, 1);
		
		$i = 1;
		foreach($files as $file)
		{
			if(is_numeric(pathinfo($file, PATHINFO_FILENAME)))
			{
				if($i > $max)
				{
					unlink($path.'/'.$file);
				}
				
				//
				$i++;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	private function _get_lasttime_timename_file($path, $params = '')
	{
		$files = scandir($path, 1);
		
		foreach($files as $file)
		{
			$time = pathinfo($file, PATHINFO_FILENAME);
			
			if(is_numeric($time))
			{
				$time = intval($time);
				
				if($time > 0)
				{
					if(isset($params['returnfullpath']))
					{
						return $path.'/'.$file;
					}
					else
					{
						return $time;
					}					
				}				
			}
		}
		
		return FALSE;
	}
	
	// --------------------------------------------------------------------
	
	private function _save_timename_file($dirpath, $content)
	{
		$fr = fopen($dirpath.'/'.time().'.'.$this->timename_file_ext,'w');
		fwrite($fr, $content);
		
		if(fclose($fr))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function _grab_exchange_rates($graburl, $params = '')
	{
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if(isset($params['graburl']) && !empty($params['graburl']))
		{
			curl_setopt($ch, CURLOPT_URL, $params['graburl']);	
		}
		else
		{
			curl_setopt($ch, CURLOPT_URL, $graburl);	
		}
		
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  //!!10 sec
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);//!!10 sec
		
		if(isset($params['cookie']) && is_array($params['cookie']) && count($params['cookie']))
		{
			$cookie = array();
			
			foreach($params['cookie'] as $key => $val) 
			{
				$cookie[] = $key.'='.$val;
			}
			
			$cookie = implode('; ', $cookie);
			
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		$response = trim($response);
		
		if(!empty($response))
		{
			return $response;
		}
		else
		{
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function _parse_exchange_rates($rawdata, $params = '')
	{
		//1.1
		preg_match_all('@<table class="cmc-table(.+?)</table>@is', $rawdata, $match, PREG_SET_ORDER);
					
		if(isset($match[0][0]))
		{
			$data = trim($match[0][0]);
			
			if(!empty($data))
			{
				//2.1
				preg_match_all('@<tbody(.+?)</tbody>@is', $data, $match, PREG_SET_ORDER);
				
				if(isset($match[0][0]))
				{
					$data = trim($match[0][0]);
					
					if(!empty($data))
					{
						//3.1
						preg_match_all('@<tr>(.+?)</tr>@is', $data, $match, PREG_SET_ORDER);
						
						if(isset($match) && is_array($match) && count($match))
						{
							//3.2
							$data = array();
							
							foreach($match as $item)
							{
								$item = $item[0];
								
								//debug
								//$data[] = $item."\r\n\r\n\r\n-----------\r\n\r\n\r\n"; continue;
								
								//trade
								$trade = '';
								
								foreach($this->currency as $currency_key => $currency)
								{
									if(strpos($item, $currency['pars']['needle']))
									{
										$trade = $currency_key;
										break;
									}
								}
								
								//price
								$price = '';
								
								$tmp = '';
								preg_match_all('@<div class="price___(.+?)</div>@is', $item, $tmp, PREG_SET_ORDER);
								if(isset($tmp[0][0]))
								{
									$tmp = trim($tmp[0][0]);
									$tmp = strip_tags($tmp);
									$tmp = preg_replace('/[^0-9.]/', '', $tmp);
									$tmp = trim($tmp);
									
									if(!empty($tmp))
									{
										$price = $tmp;
									}
								}
								
								//
								if(!empty($trade) && !empty($price))
								{
									$data[$trade] = $price;
								}								
							}
							
							if(!empty($data))
							{
								return json_encode($data);
							}
						}
					}
				}
			}			
		}
		
		return FALSE;
	}
	
	// --------------------------------------------------------------------
	
	public function get_currency()
	{
		return $this->currency;
	}
	
	// --------------------------------------------------------------------
	
	public function view_history_item($time)
	{
		$filepath = $this->basepath.$this->dir['history'].'/'.$time.'.'.$this->timename_file_ext;
		
		if(file_exists($filepath))
		{
			$content = file_get_contents($filepath);
			$content = trim($content);
			$content = json_decode($content);
			
			if(is_object($content) && !isset($content->time))
			{
				$content->time = $time;
			}
			
			return $content;
		}
	}
	
	// --------------------------------------------------------------------
	
	public function get_history($limit = 10)
	{
		if($this->historylimit)
		{
			$limit = $this->historylimit;
		}
		
		$history = array();
		
		$files = scandir($this->basepath.$this->dir['history'], 1);
		
		$i = 1;
		foreach($files as $file)
		{
			if(is_numeric(pathinfo($file, PATHINFO_FILENAME)))
			{
				if($i > $limit)
				{
					break;
				}
				
				$content = file_get_contents($this->basepath.$this->dir['history'].'/'.$file);
				$content = trim($content);
				$content = json_decode($content);
				
				if(is_object($content))
				{
					$history[pathinfo($file, PATHINFO_FILENAME)] = $content;
				}
				
				//
				$i++;
			}
		}
		
		return $history;
	}
	
	// --------------------------------------------------------------------
	
	public function get_rates($currency_key)
	{
		if(!isset($this->currency[$currency_key]))
		{
			return FALSE;
		}
		
		$response = $this->_get_lasttime_timename_file($this->basepath.$this->dir['exchrates'].'/'.$this->currency[$currency_key]['dir'], array('returnfullpath' => true));
		
		if(!$response || !file_exists($response))
		{
			return FALSE;
		}
		
		$response = file_get_contents($response);		
		$response = trim($response);
		$response = json_decode($response);
		
		return $response;
	}
	
	// --------------------------------------------------------------------
	
	public function calculate($in_currency_key, $out_currency_key, $value, $direct, $params = '')
	{
		$value = (float)$value;
		$rates = $this->get_rates($in_currency_key);
		$currency = $this->get_currency();
		
		if(is_object($rates))
		{
			if(isset($rates->{$out_currency_key}))
			{
				$return = array(
					'args' => array($in_currency_key, $out_currency_key, $value, $direct, $params),
					'result' => 0,
					'formula' => '',
					'in' => $currency[$in_currency_key],
					'out' => $currency[$out_currency_key],
				);
				
				switch($direct)
				{
					case 'invert':
						$value = $value / (float)$rates->{$out_currency_key};
					break;
				}
				
				$return['result'] = $value * (float)$rates->{$out_currency_key};
				
				//because exp number usualy
				$return['result'] = sprintf('%f', $return['result']);
				$return['result'] = rtrim($return['result'], '0');
				$return['result'] = rtrim($return['result'], '.');
				
				$return['formula'] = $value.' '.$currency[$in_currency_key]['abbr'].' x '.$rates->{$out_currency_key}.' '.$currency[$out_currency_key]['abbr'].' = '.$return['result'].' '.$currency[$in_currency_key]['abbr'].'';
				
				//save & claen old in history
				if($return['result'] > 0)
				{
					//save
					$this->_save_timename_file($this->basepath.$this->dir['history'], json_encode($return));
					
					//clean overflow files after save
					$this->_clean_overflow_timename_files($this->basepath.$this->dir['history']);
				}
				
				return $return;
			}
		}
		
		return false;
	}
	
	// --------------------------------------------------------------------
	
	public function cron($params = '')
	{
		$settings = array(
			'graburl' => 'https://coinmarketcap.com/',
			'lastcronfilename' => '_lastcron.txt',
			'maxiteration' => 2,
			'delaygrabsec'    => 60,
		);
		
		if($this->cronmaxiteration && is_int($this->cronmaxiteration) && $this->cronmaxiteration > 1)
		{
			$settings['maxiteration'] = $this->cronmaxiteration;
		}
		
		if($this->crondelaygrabsec && is_int($this->crondelaygrabsec) && $this->crondelaygrabsec > 0)
		{
			$settings['delaygrabsec'] = $this->crondelaygrabsec;
		}
		
		//
		if(isset($params['returnsettings']))
		{
			return $settings;
		}
		
		//breakpoint
		$breakpoint = FALSE;
		if(file_exists($this->basepath.$this->dir['exchrates'].'/'.$settings['lastcronfilename']))
		{
			$breakpoint = file_get_contents($this->basepath.$this->dir['exchrates'].'/'.$settings['lastcronfilename']);
			
			//it valid?
			if(!isset($this->currency[$breakpoint]))
			{
				$breakpoint = FALSE;
			}
			
			//it last?
			end($this->currency);
			if(key($this->currency) == $breakpoint)
			{
				$breakpoint = FALSE;
			}
			reset($this->currency);
		}
		
		$log = '';
		$iteration = 1;
		foreach($this->currency as $currency_key => $currency)
		{
			//defender
			if($iteration > $settings['maxiteration'])
			{
				break;
			}
			
			//breakpoint
			if(isset($breakpoint) && $breakpoint)
			{
				if($breakpoint !== $currency_key)
				{
					continue;
				}
				else
				{
					unset($breakpoint);
					continue;
				}
			}
			
			//currency dir
			if(!is_dir($this->basepath.$this->dir['exchrates'].'/'.$currency['dir']))
			{
				if(!$this->_create_dir($this->basepath.$this->dir['exchrates'].'/'.$currency['dir']))
				{
					continue;
				}
			}
			
			//check delay for grab
			$lasttime = $this->_get_lasttime_timename_file($this->basepath.$this->dir['exchrates'].'/'.$currency['dir']);
			
			if($lasttime)
			{
				if((time() - $lasttime) < $settings['delaygrabsec'])
				{
					continue;
				}
			}
			
			//grab & prepare & save currency exchange rates
			if(count($currency['grab']))
			{
				//grab
				$response = $this->_grab_exchange_rates($settings['graburl'], $currency['grab']);
			
				if($response)
				{
					//prepare
					$response = $this->_parse_exchange_rates($response, $currency['pars']);
					
					if($response)
					{
						//save
						$this->_save_timename_file($this->basepath.$this->dir['exchrates'].'/'.$currency['dir'], $response);
						
						//clean overflow files after save
						$this->_clean_overflow_timename_files($this->basepath.$this->dir['exchrates'].'/'.$currency['dir']);
					}
				}
			}
			
			//system
			$this->_create_file($this->basepath.$this->dir['exchrates'].'/'.$settings['lastcronfilename'], $currency_key);
			
			//
			$iteration++;
		}
		
		$log .= 'useful iteration: '.($iteration - 1)."\r\n";
		
		return trim($log);
	}
	
	// --------------------------------------------------------------------
	
	function profiler()
    {
        $debug = array(
			'time' => time(),
			'datetime' => date('Y-m-d H:i:s'),
			'cronsettings' => $this->cron(array('returnsettings' => true)),			
			'data' => array(),
		);
		
		foreach($this->dir as $key => $dir)
		{
			$content_1 = scandir($this->basepath.$dir, 1);
			
			if(is_array($content_1) && count($content_1))
			{
				//level 1
				$content_1 = array_diff($content_1, array('.', '..'));
				
				foreach($content_1 as $elem_1)
				{
					if(is_dir($this->basepath.$dir.'/'.$elem_1))
					{
						$debug['data'][$dir][$elem_1] = array();
						
						//level 2
						$content_2 = scandir($this->basepath.$dir.'/'.$elem_1);
						
						if(is_array($content_2) && count($content_2))
						{
							$content_2 = array_diff($content_2, array('.', '..'));
							$content_2 = array_values($content_2);
							
							$debug['data'][$dir][$elem_1] = $content_2;
						}
					}
					else
					{
						$debug['data'][$dir][$elem_1] = $elem_1;
					}					
				}
			}
		}
		
		echo '<textarea style="width:100%; height:100%; background-color:#000; color:#0C0;">'.print_r($debug, true).'</textarea>';
    }
}
?>