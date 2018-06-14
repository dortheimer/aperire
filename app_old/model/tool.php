<?php
class AperireModelTool extends AperireModel {

	public function __construct($conf){
		foreach ($conf as $key => $val){
			$this->$key = $val;
		}
	}

	public function save() {
		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;

		$data = array(
			'project_id'=>$this->project_id,
			'name' => $this->name,
			'description' => $this->description
		);

		// create new object
		if (empty($this->id)){
			$data['cdate'] =  new Zend_db_expr('NOW()')	;
			$db->insert($pr.'tools',$data);
			$this->id = $db->lastInsertId();
		}
		else {
			$db->update($pr.'tools',$data, 'id=?',$this->id);
		}
		return true;
	}
}