<?php

class AperireModel {

	static public function factory ($conf = null){

		$modelClassName = 'AperireModel'.$conf['model'];

		if (!require_once BASE.'/app/model/'.$conf['model'].'.php') {
			throw new AperireException("Page not found: Model doesn't exist");
		}

		return new $modelClassName($conf);
	}

	public function url(){
		return 'http://'.$_SERVER['SERVER_NAME'];
	}
}