<?php

class AperireModelUser extends AperireModel {

	public $id;

	public function __construct($conf) {
		foreach ($conf as $key=>$val){
			$this->$key = $val;
		}
	}

	public static function login($email='', $password='') {

		$db = Aperire::$db;
		$query = $db->select('*')
			->from(Aperire::$config->db->prefix.'users')
			->where('email=?',$email)
			->limit(1);

		$data = $db->query($query)->fetchAll();
		if (!isset($data[0])){
			throw new AperireException("Email not found");
		}
		if (md5($password) != $data[0]['password']){
			throw new AperireException("Password doesn't match");
		}

		$data[0]['model'] = 'user';

		Aperire::$session->user = AperireModel::factory($data[0]);

		return true;
	}

	public static function create($params) {
		$data = array();
		$error = '';
		$prefix = Aperire::$config->db->prefix;

		$mandatory = array('real_name', 'email', 'password');
		foreach ($mandatory as $key){
			if (empty($params[$key])){
				throw new AperireException('Field '.$key.' is mandatory');
			}
			else {
				$data[$key] = $params[$key];
			}
		}
		$db = Aperire::$db;

		if (empty($data['password']) or strlen($data['password'])<3){
			throw new AperireException('Please provide a password longer then 3 characters');
		}
		$data['password'] = md5($data['password']);

		if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			throw new AperireException('The email address is not a valid, please chek it.');
		}

		$query = $db->select('id')
				->from($prefix.'users')
				->where('email=?',$data['email'])
				->limit(1);

		//checks email
		if ($db->query($query)->fetchAll()) {
			throw new AperireException('The email '.$data['email'].' exists already. Please log in.');
		}


		//check password

		$query = $db->insert($prefix.'users', $data);
		return true;
	}

	public function getReal_name () {
		$row = $this->getUserRow();
		return $row[0]['real_name'];
	}

	public function getEmail () {
		$row = $this->getUserRow();
		return $row[0]['email'];
	}

	protected function getUserRow () {

		$documents = $this->getDocuments($id);
		$id = intval($id);
		if (!$id){
			throw new AperireException("User doesn't exists");
		}
		//cache the result
		if (!isset(self::$data)) {
			// get fresh result
			$db = Aperire::$db;
			$query = $db->select('*')
					->from(Aperire::$config->db->prefix.'users')
					->where('id=?',$id)
					->limit(1);

			self::$data['row'] = $db->query($query)->fetchAll();
		}
		return self::$data['row'];
	}
}