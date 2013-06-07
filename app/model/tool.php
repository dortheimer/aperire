<?php
class AperireModelTool extends AperireModel {

	public function __construct($conf){
		foreach ($conf as $key => $val){
			$this->$key = $val;
		}
	}
}