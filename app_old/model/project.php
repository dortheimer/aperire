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

	public function save() {

		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;

		$data = array(
			'name'=>$this->name,
			'description'=>$this->description,
			'udate'=> new Zend_db_expr('NOW()')
		);

		// create new object
		if (empty($this->id)){
			$data['cdate'] =  new Zend_db_expr('NOW()')	;
			$db->insert($pr.'projects',$data);
			$this->id = $db->lastInsertId();
		}
		else {
			$db->update($pr.'projects',$data, 'id=?',$this->id);
		}
		return true;
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


	public function getToolsRanked(){
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
			$temp_r =array();
			foreach ($relations as $tool_id => $rel_tools){
				foreach ($rel_tools as $tool_id_rel => $values){

					// get what's voted most
					$max = array_keys($values, max($values));

					if (sizeof($max)==1){
						// save to array if relation exists
						if ($max[0]>0){
							$temp[($max[0])][$tool_id][] = $tool_id_rel;
							$temp_r[($max[0])][$tool_id][$tool_id_rel] = intval($values[$max[0]]/(array_sum($values))*100)/100;
						}
					}
					// if we don't know
					else{
						// We need to figure out what's going on
						// 1 and 2 are similar, 0 doesn't matter but 3 is contradiction.
						// we check if count1+count2 >count3 then if count1>=count2 then count1 else count2

						//make sure we have values
						for ($i=0,$l=4;$i<4;$i++) if (!isset($values[$i])) $values[$i]=0;


						if ($values[1]+$values[2]>= $values[3]){
							// facilitation
							if ($values[1] >= $values[2]){
								$temp[1][$tool_id][] = $tool_id_rel;
								$temp_r[1][$tool_id][$tool_id_rel] = intval($values[0]/(array_sum($values))*100)/100;
							}
							//precondition
							else {
								$temp[2][$tool_id][] = $tool_id_rel;
								$temp_r[2][$tool_id][$tool_id_rel] = intval($values[1]/(array_sum($values))*100)/100;
							}
						}
						//contradiction
						else {
							$temp[3][$tool_id][] = $tool_id_rel;
							$temp_r[3][$tool_id][$tool_id_rel] = intval($values[2]/(array_sum($values))*100)/100;

						}
					}
				}

			}
			$relations = $temp_r;
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
			//sum up page rank for each node
			$orderOfActions = array(1,2,3);
			foreach ($orderOfActions as $kind){
				if (!empty($page_rank[$kind])){
					foreach ($page_rank[$kind] as $id => $value){
						if (!isset($page_rank['total'][$id])){
							$page_rank['total'][$id] = 0;
						}
	// 					echo $kind.' '.$page_rank['total'][$id].' - ';
						$value = floatval($value);
						switch ($kind){

							//facilitation
							case 1:
								$page_rank['total'][$id]+= $value;
								break;
							//precondition
							case 2:
								$page_rank['total'][$id] = $page_rank['total'][$id] + $value*1.2;
								break;
	// 						//Contradiction
	// 						case 4:
	// 							$page_rank['total'][$id] = $page_rank['total'][$id] * (1/$value);
	// 							break;
						}
	// 					echo $value.' '.' '.$page_rank['total'][$id]."\n";
					}
				}
			}
			//order by size:

			arsort($page_rank['total']);

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

		$rt = array(
				'nodes'=>$nodes,
				'relations'=>$relations,
				'relations_page_rank'=>$page_rank,
				'effect_page_rank'=>$effect_page_rank[0],
				'applicable_page_rank'=>$effect_page_rank[1]
		);
		return $rt;

	}

	public function getTools() {
		require_once BASE.'/app/model/tool.php';

		$pr = Aperire::$config->db->prefix;
		$db = Aperire::$db;
		$data = array();

		$query = $db->select('*')
			->from($pr.'tools')
			->where('project_id=?',$this->getId());
		$res = $db->query($query)->fetchAll();
		for ($i=0, $l=sizeof($res); $i<$l; $i++){
			$data[] = new AperireModelTool($res[$i]);
		}
		return $data;

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
				->order('RAND()');

		$query = $db->select("*")->from($query)->where('rate_0=0')->orWhere('rate_1=0')->limit(2);

		$res = $db->query($query)->fetchAll();
		if ($res and sizeof($res)==2){

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
			if ($res[0]['kind_0']*2 <= $res[0]['kind_1']*2 and $res[0]['kind_0']*2 <= $res[0]['kind_2']){
				$kind=0;
			}
			elseif($res[0]['kind_1']*2 <= $res[0]['kind_0']*2 and $res[0]['kind_1']*2 <= $res[0]['kind_2']){
				$kind=1;
			}

			// get weights needed
			$data[0] = $this->getTool($kind);
			$data[1] = $this->getToolRel($data[0], $kind);

		}
		return $data;
	}

	public function getContributors() {
		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;
		$id = $this->getId();

		$query = 'select * from '.$pr.'users
		inner join
		(select '.$pr.'tool_relations.user_id, count(*) as num, "relation" as kind from '.$pr.'tool_relations
		inner join '.$pr.'tools on '.$pr.'tools.id = '.$pr.'tool_relations.tool_id
		where project_id='.$id.' group by user_id
		union all
		select user_id, count(*) as num, "tool" as kind from '.$pr.'tools where project_id='.$id.' group by user_id
		) as d on d.user_id = '.$pr.'users.id';

		$res = $db->query($query)->fetchAll();
		//organize rows
		$data = array();
		foreach ($res as $res1) {
			$data[$res1['user_id']] = array(
					'id'=>$res1['user_id'],
					'real_name'=>$res1['real_name']
			);
			if ($res1['kind']=='tool'){
				$data[$res1['user_id']]['tools'] = $res1['num'];
			}
			elseif ($res1['kind']=='relation'){
				$data[$res1['user_id']]['relation'] = $res1['num'];
			}
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
			->order('rand()')
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

	public function add_tool(AperireModelTool $tool){

		$tool->project_id = $this->id;
		$tool->user_id = Aperire::$user->id;
		if ($tool->save())
			return true;
		else
			return false;
	}

	public function score () {
		// numer of tools ^ 2 + number of tools *2
		$db = Aperire::$db;
		$pr = Aperire::$config->db->prefix;

		$query = $db->select()->from($pr.'tools',array('c'=>new Zend_db_expr('count(*)')))
					->where('project_id=?',$this->getId());
		$res = $db->query($query)->fetchAll();

		$tools = $res[0]['c'];
		if (!$tools)
			return 0;

		$query = $db->select()->from($pr.'tool_relations',array('c'=>new Zend_db_expr('count(*)')))
					->join($pr.'tools',$pr.'tools.id='.$pr.'tool_relations.tool_id')
					->where('project_id=?',$this->getId());
		$res = $db->query($query)->fetchAll();

		$relations = $res[0]['c'];
		return round($relations / ($tools*$tools + $tools*2)*1000)/10;

	}


}