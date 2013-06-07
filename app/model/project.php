<?php

class AperireModelProject extends AperireModel {

	static public function getProjects() {

		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;


		// get fresh result

		$query = $db->select('*')
		->from($pr.'projects');

		$rows = $db->query($query)->fetchAll();
		$data = array();
		for ($i=0, $l=sizeof($rows) ; $i<$l ; $i++){
			$data[$i] = new AperireModelProject($rows[$i]);
		}

		return $data;
	}

	public function __construct($conf){
		foreach ($conf as $key => $val){
			$this->$key = $val;
		}
	}

	public function get ($value) {
		if (empty($this->$value)){
			$this->loadFromDb();
		}
		return $this->$value;
	}

	public function getName() {
		return $this->get('name');
	}
	public function getId() {
		return $this->get('id');
	}

	public function getDescription () {
		return $this->get('description');
	}

	protected function loadFromDb () {

		if (empty($this->id)){
			throw new AperireException('Project cannot load from DB bucause got no id');
		}

		$db = Aperire::$db;
		$query = $db->select('*')
				->from(Aperire::$config->db->prefix.'projects')
				->where('id=?',$this->id)
				->limit(1);

		$data = $db->query($query)->fetchAll();
		foreach ($data[0] as $key => $val){
			$this->$key = $val;
		}
		return true;
	}


	public function getTools(){
		require_once BASE.'/app/model/tool.php';
		require_once BASE.'/lib/pageRank.php';

		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;

		$data 				= new stdClass();
		$nodes 				= array();
		$relations 			= array();
		$page_rank 			= array();
		$effect_page_rank 	= array(array(1),array(1));

		// get nodes
		$query = $db->select()->from($pr.'tools')->where('project_id=?',$this->getId());
		$rows = $db->query($query)->fetchAll();
		foreach ($rows as $row){
			$nodes[$row['id']] = $row['name'];
		}
		if (!isset($nodes)){
			return array();
		}

		// get relations
		$query = $db->select()->from($pr.'relations')
			->join($pr.'tools', $pr.'tools.id='.$pr.'relations.tool_id',array('name'))
			->where('project_id=?',$this->getId());
		$rows = $db->query($query)->fetchAll();

		//build hash table
		foreach ($rows as $row){
			if ($row['tool_id'] != $row['tool_id_rel']) {
				if (isset($relations[$row['tool_id']][$row['tool_id_rel']][$row['value']])){
					$relations[$row['tool_id']][$row['tool_id_rel']][$row['value']]++;
				}
				else {
					$relations[$row['tool_id']][$row['tool_id_rel']][$row['value']]=1;
				}
			}
		}

		// if we got relations
		if (sizeof($rows)){
			/** We check on contradictions and do some normalization
			* 0 no relation
			* 1 Precondition
			* 2 Facilitation
			* 3 Contradiction
			* How do we fix this conflict?
			* We check what has the most votes
			* If we are even? We should display a problem link =4
			**/
			$temp =array();
			foreach ($relations as $tool_id => $rel_tools){
				foreach ($rel_tools as $tool_id_rel => $values){
					$max = array_keys($values, max($values));
					if (sizeof($max)==1){
						// save to array if relation exists
						if ($max[0]>0)
							$temp[($max[0])][$tool_id][] = $tool_id_rel;
					}
					else
						$temp[4][$tool_id][] = $tool_id_rel;
				}

			}
			$relations = $temp;
			// calculate page rank for these networks
			foreach ($temp as $net_key => $network){
				$network_keys = array_keys($network);
				foreach ($network as $sub_network){
					foreach ($sub_network as $test_key){
						if (!in_array($test_key, $network_keys)) {
							$network[$test_key] = array();
	// 						echo 'f';
						}
					}
				}
				$page_rank[$net_key] = calculatePageRank($network);
			}


			// calculate efficiency and effectivness
			$query = $db->select()->from($pr.'effective')
			->join($pr.'tools', $pr.'tools.id='.$pr.'effective.tool_id',array('name'))
			->where($pr.'tools.project_id=?',$this->getId())
			->order('tool_id');
			$rows = $db->query($query)->fetchAll();

			$rank_data = array();
			foreach ($rows as $row){
				if ($row['tool_id'] == $row['tool_id_rel']){
					continue;
				}
				if ($row['tool_id']==$row['value']){
					$winner = $row['tool_id'];
					$looser = $row['tool_id_rel'];
				}
				else{
					$winner = $row['tool_id_rel'];
					$looser = $row['tool_id'];
				}

				if (isset($data->rank_data[$row['rel_kind']][$winner][$looser])){
					$rank_data[$row['rel_kind']][$winner][$looser]++;
				}
				else {
					$rank_data[$row['rel_kind']][$winner][$looser] = 1;
				}
			}

			//prepare data and calculate page rank
			foreach ($rank_data as $rel_kind => $network){
				$network_keys = array_keys($network);
				foreach ($network as $tool_id => $edges){

					//make sure the node exists in parent array
					foreach ($edges as $test_key=>$test){
						if (!in_array($test_key, $network_keys)) {
							$rank_data[$rel_kind][$test_key] = array();
						}
					}

					// set keys as values
					$rank_data[$rel_kind][$tool_id] = array_keys($edges);
				}
				$effect_page_rank[$rel_kind] = calculatePageRank($rank_data[$rel_kind]);
			}

			$i=0;
			foreach ($effect_page_rank as $kind => $array){
				foreach ($array as $node => $rank){
					$effect_page_rank['comuputed'][$node] =
						($rank + (isset($data->page_rank['comuputed'][$node]) ? $data->page_rank['comuputed'][$node] : 0) * $i)/($i + 1);
				}
				$i++;
			}
		}
// 		$data = new stdClass();

		return array(
				'nodes'=>$nodes,
				'relations'=>$relations,
				'relations_page_rank'=>$page_rank,
				'effect_page_rank'=>$effect_page_rank[0],
				'applicable_page_rank'=>$effect_page_rank[1]
		);

	}


	public function getRandomTools() {
		include BASE.'/app/model/tool.php';

		$pr = Aperire::$config->db->prefix;
		$db = Aperire::$db;
		$data = array();

		//What do we need to do?
		//Check if we have minimum weights for all tools
		//Check how many relations do we have.
		// If we have more relations then weighs we will ask for more weights
		// Minimum weighs 1 relation for each tool for each goal
		$query = $db->select('*')
			->from($pr.'tools')
			->joinleft($pr.'tool_relations',
				'('.$pr.'tools.id='.$pr.'tool_relations.tool_id OR '.$pr.'tools.id='.$pr.'tool_relations.tool_id_rel) and rel_kind in(0,1)',
				array(
						'rate_0'=>new Zend_db_expr('SUM(IF(rel_kind=0,1,0))'),
						'rate_1'=>new Zend_db_expr('SUM(IF(rel_kind=1,1,0))'),
				))
				->where('project_id=?',$this->getId())
				->group($pr.'tools.id')
				->limit(2);

		$query = $db->select("*")->from($query)->where('rate_0=0')->orWhere('rate_1=0');

		$res = $db->query($query)->fetchAll();
		if ($res){

			// set the right kind of question
			if ($res[0]['rate_0'] <= $res[0]['rate_1']){
				$res[0]['kind'] = $res[1]['kind'] = 0;
			}
			else{
				$res[0]['kind'] = $res[1]['kind'] = 1;
			}

			$data = array(
				new AperireModelTool($res[0]),
				new AperireModelTool($res[1])
			);
		}

		// No minimum weight so we check relations status
		else{
			$query = $db->select('*')
				->from($pr.'tools')
				->join($pr.'tool_relations',
					$pr.'tools.id='.$pr.'tool_relations.tool_id',
					array(
							'kind_0'=>new Zend_db_expr('SUM(IF(rel_kind=0,1,0))'),
							'kind_1'=>new Zend_db_expr('SUM(IF(rel_kind=1,1,0))'),
							'kind_2'=>new Zend_db_expr('SUM(IF(rel_kind=2,1,0))'),
// 							'kind_3'=>new Zend_db_expr('SUM(IF(rel_kind=3,1,0))'),
					))
					->where('project_id=?',$this->getId());
			$res = $db->query($query)->fetchAll();


			// Find out required kind
			$kind=2;
			if ($res[0]['kind_0'] <= $res[0]['kind_1'] and $res[0]['kind_0'] <= $res[0]['kind_2']){
				$kind=0;
			}
			elseif($res[0]['kind_1'] <= $res[0]['kind_0'] and $res[0]['kind_1'] <= $res[0]['kind_2']){
				$kind=1;
			}

			// get weights needed
			$data[0] = $this->getTool($kind);
			$data[1] = $this->getToolRel($data[0], $kind);

		}
		return $data;
	}

	protected function getTool($kind=0) {
		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;

		$query = $db->select('*')
			->from($pr.'tools')
			->joinleft($pr.'tool_relations',
				$pr.'tools.id='.$pr.'tool_relations.tool_id',
				array(
						'kind' => new Zend_db_expr('"'.$kind.'"'),
						'relations'=>new Zend_db_expr('SUM(IF(rel_kind='.$kind.',1,0))'),
				))
			->where('project_id=?',$this->getId())
			->group($pr.'tools.id')
			->order('relations');

		$res = $db->query($query)->fetchAll();
		return new AperireModelTool($res[0]);
	}
	/**
	 * get another tool that there's no relation to
	 * @param unknown $tool
	 * @return AperireModelTool
	 */
	protected function getToolRel(AperireModelTool $tool,$kind=0) {
		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;

		$query = $db->select('*')
			->from($pr.'tools')
			->joinleft($pr.'tool_relations',
				'apr_tools.id = apr_tool_relations.tool_id and rel_kind='.$kind.' and apr_tool_relations.tool_id_rel='.$tool->id,
				array(
					's' => new Zend_db_expr('sum(if(rel_kind,1,0))')
				)
			)
			->where($pr.'tools.id<>?',$tool->id)
			->where($pr.'tools.project_id=?',$this->getId())
			->group($pr.'tools.id')
			->order('s')
			->limit(1);
		$res = $db->query($query)->fetchAll();
		return new AperireModelTool($res[0]);
	}

	public function url($action = 'view') {
		return parent::url(). '/projects/'.$action.'/?id='.$this->id;

	}

	/**
	 * Relation are created and used in two ways.
	 * One is to know which tools relation were created in the past
	 * The other thing is to learn about the nature of the tools
	 *
	 * We put in the data base the actual data from the form.
	 *
	 * @param unknown $rel_kind
	 * @param unknown $tool_id
	 * @param unknown $tool_id_rel
	 * @param unknown $value
	 */
	public function create_relation($rel_kind, $tool_id, $tool_id_rel,$value){
		$db = Aperire::$db;

		$query = $db->insert(Aperire::$config->db->prefix.'tool_relations',
				array(
						'rel_kind'=>$rel_kind,
						'tool_id'=>$tool_id,
						'tool_id_rel'=>$tool_id_rel,
						'value'=>$value,
						'user_id'=>Aperire::$user->id,
						'cdate'=>new Zend_db_expr('NOW()'))
		);

	}

	public function create_tool($tool){
		$db = Aperire::$db;
		$query = $db->insert(Aperire::$config->db->prefix.'tools',
				array(
						'project_id'=>$this->id,
						'name'=>$tool,
						'user_id'=>Aperire::$user->id,
						'cdate'=>new Zend_db_expr('NOW()'))
		);
	}

	public function score () {
		// numer of tools ^ 2 + number of tools *2
		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;

		$query = $db->select()->from($pr.'tools',array('c'=>new Zend_db_expr('count(*)')))
					->where('project_id=?',$this->getId());
		$res = $db->query($query)->fetchAll();

		$tools = $res[0]['c'];

		$query = $db->select()->from($pr.'tool_relations',array('c'=>new Zend_db_expr('count(*)')))
					->join($pr.'tools',$pr.'tools.id='.$pr.'tool_relations.tool_id')
					->where('project_id=?',$this->getId());
		$res = $db->query($query)->fetchAll();

		$relations = $res[0]['c'];
		return round($relations / ($tools*$tools + $tools*2)*1000)/10;

	}


}