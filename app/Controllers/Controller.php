<?php

namespace App\Controllers;

/**
* 
*/
class Controller
{
	protected $container;

	public function __construct($container)
	{
		$this->container = $container;
	}
	public function is_weixin(){ 
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
    }   
    return false;
	}
	public function success($message = ''){
		return $this->response->write(
			<<<EOT
			<script>
			alert('$message');
			history.go(-1);
			</script>
EOT
		);
	}
	public function error($message =''){
		return $this->response->write(
			<<<EOT
			<script>
			alert('$message');
			history.go(-1);
			</script>
EOT
		);
	}
	public function __get($property)
	{
		if ($this->container->{$property}) {
			return $this->container->{$property};
		}
	}
}