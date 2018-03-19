<?php

/* Direct database */
/* Developed by Predrag Jovicic */

class Database{
	
	private $base;
	private $table;
	private $username;
	private $password;
	private $rows;
	private	$result;
	private $rows_count;
	private $max_rows = 200;
	private $rows_config = array();
	private $index_rows;
	private $datum;
	
	public function __construct($basename,$username,$password){
		
		$this->base = $basename; 
		$this->username = hash("sha256", $username);
		$this->password = hash("sha256", $password);
		$this->datum = date("d.m.y H:i:s"); 
		
		if(!file_exists($this->base)){
		mkdir($this->base, 0777, true);
		
		$access='<Files *.*>
		Order deny,allow
		Deny from All 
		</Files>';
		$this->writeFile(".htaccess", $access);
		
			$config=array(
				'basename' => hash("sha256", $basename),
				'username' => $this->username,
				'password' => $this->password,
					'date' => $this->datum
			);
			
			$config = json_encode($config);
			$this->writeFile("config.json", $config);
		}else{
			$userdata = $this->readFile("config.json");
			$config = json_decode($userdata,true);
			if(hash("sha256", $basename) != $config['basename'] || $this->username != $config['username'] || $this->password != $config['password']){
				echo "Access denied!";
				exit;
			}
		}
		if(file_exists($this->base."/tableconfig.json")){
			$this->readTableconfig();
		}
		
	} 
	
	public function insert($table,$values){

		$table = str_replace(" ","",$table);
		$this->rows = array();
		
		if(!array_key_exists($table,$this->rows_config)){
			$this->index_rows = 0;
			$this->writeTableconfig($table);
			$this->table = $table."_".$this->index_rows;
		}else{
			$this->readTableconfig();
			$this->index_rows = $this->rows_config[$table];
			$this->table = $table."_". $this->index_rows ;
		}
				
			if(file_exists($this->base."/".$this->table.".json")){
			
				$this->rows = json_decode($this->readFile($this->table.".json"));
				$lastrow = end($this->rows);
				$nextid = $lastrow->id + 1;
				
				if($this->max_rows == count($this->rows)){			
					$this->index_rows++;
					$this->writeTableconfig($table);
					$nextrows11 = array();
					$nextrows1 = array('id' => $nextid);	

					foreach($values as $key=>$value){
						$nextrows1[$key] = $value;
					}
					$nextrows11[] = $nextrows1;
					
					$json =json_encode($nextrows11);
					if($this->writeFile($table."_".$this->index_rows.".json",$json)){
						return true;
					}else{
						return false;
					}
				}else{
					$nextrows = array('id' => $nextid);	
				
					foreach($values as $key=>$value){
						$nextrows[$key] = $value;
					}
					$this->rows[] = $nextrows;
					$json =json_encode($this->rows);
					if($this->writeFile($this->table.".json",$json)){
						return true;
					}else{
						return false;
					}
				}
				
			}else{
		
				$this->rows['id'] = 0;
				foreach($values as $key=>$value){
					$this->rows[$key] = $value;
				}
				$this->rows = array($this->rows);
				$json =json_encode($this->rows);
				if($this->writeFile($this->table.".json",$json)){
						return true;
					}else{
						return false;
					}
	
			}
	}
	
	public function select($table,$condition,$order,$start,$per_page){
		
		$table = str_replace(" ","",$table);
		$this->readTableconfig();
		if(array_key_exists($table,$this->rows_config)){
			
		$this->index_rows = $this->rows_config[$table];
		$this->result = array();
	
		for($i=0; $i<$this->index_rows+1; $i++){
			
		$this->rows = array();
		$this->table = $table."_".$i;
		$this->rows = json_decode($this->readFile($this->table.".json"));
		
			if($condition){
				foreach($this->rows as $key=>$row){
					if(eval("return $condition;")){
						$this->result[] = $row;
					}
				}
			}else{
				foreach($this->rows as $key=>$row){
					$this->result[] = $row;
				}
			}
			
		}	
		$this->rows_count = count($this->result);
		if($order == 'd'){
			$this->result = array_reverse($this->result);
		}
		if($per_page){
		$this->result = array_slice($this->result, $start, $per_page);
		}
	
		return $this->result;
		}else{
			echo "Table don't exists";
		}
	}
	
	public function search($basetable,$rows,$search,$order,$start,$per_page){
		
		$basetable = str_replace(" ","",$basetable);
		$this->readTableconfig();
		if(array_key_exists($basetable,$this->rows_config)){
			
		$this->index_rows = $this->rows_config[$basetable];
		$this->result = array();
		
		if($search){
			
			$search = explode(" ",$search);
			$rows = explode(",",$rows);
			
			$searchrows = '""';
				foreach($rows as $row){
					$searchrows .= '." ".strtolower($row->'.$row.') ';
				}
				$c = 0;
				$pageserch = '';
				foreach($search as $search_each){
					$search_each = strtolower($search_each);
					if($c == 0){
						$const = 'preg_match("/\b'.$search_each.'\b/", '.$searchrows.')';
						$pageserch .=  ' preg_match("/\b'.$search_each.'\b/", strtolower(serialize($this->rows)) )';
					}else{
						$const .= '&& preg_match("/\b'.$search_each.'\b/", '.$searchrows.')';
						$pageserch .=  ' && preg_match("/\b'.$search_each.'\b/", strtolower(serialize($this->rows)) )';
					}				
				$c=1;
				}
				for($i=0; $i<$this->index_rows+1; $i++){
					$this->rows = array();	
					$this->basetable = $basetable."_".$i;
					$this->rows = json_decode($this->readFile($this->basetable.".json"));

					if(eval("return $pageserch;")){

							foreach($this->rows as $key=>$row){
						
								if(eval("return $const;")){
									$this->result[] = $row;
								}
							}

					}
				}
		}
		$this->rows_count = count($this->result);
		if($order == 'd'){
			$this->result = array_reverse($this->result);
		}
		if($per_page){
			$this->result = array_slice($this->result, $start, $per_page);
		}
		return $this->result;
		
		}else{
			echo "Table don't exists";
		}
	}
	
	public function delete($table,$condition){
		$table = str_replace(" ","",$table);
		$this->readTableconfig();
		if(array_key_exists($table,$this->rows_config)){
			
		$this->index_rows = $this->rows_config[$table];
		$rowdeleted = array();
		$rowrenamed = array();
		$error = 0;
		if(!$condition){
			$condition='$row->id > -1';
		}
		for($i=0; $i<$this->index_rows+1; $i++){
		$this->result = array();	
		$this->rows = array();	
		$this->table = $table."_".$i;
		$this->rows = json_decode($this->readFile($this->table.".json"));
	
			foreach($this->rows as $row){
				if(eval("return $condition;")){
				 
				}else{
					$this->result[] = $row;
				}
			}
	
			if(empty($this->result)){
				$rowdeleted[] = $table."_".$i.".json";
			}else{
				$rowrenamed[] = $table."_".$i.".json";
			}
			
		$json =json_encode($this->result);
		if(!$this->writeFile($this->table.".json",$json)){
			$error++;
		}
		}
		
		if(!empty($rowdeleted)){
			foreach($rowdeleted as $row){
				unlink($this->base."/".$row);
			}
			$rownum = 0;
			foreach($rowrenamed as $row){
				$rowe = explode("_",$row);
				rename($this->base."/".$row, $this->base."/".$rowe[0]."_".$rownum.".json");
			$rownum++;
			}			
			$this->index_rows = $rownum - 1;
			if(!$this->writeTableconfig($table)){
			$error++;
			}			
		}
		
		if($error == 0){
			return true;
		}else{
			return false;
		}
			
		}else{
			echo "Table don't exists";
		}	
	}
	
	public function update($table,$condition,$updates){
		$table = str_replace(" ","",$table);
		$this->readTableconfig();
		if(array_key_exists($table,$this->rows_config)){
			
		$this->index_rows = $this->rows_config[$table];
		
		$error = 0;
		if(!$condition){
			$condition='$row->id > -1';
		}
		for($i=0; $i<$this->index_rows+1; $i++){
		$this->result = array();
		$this->rows = array();	
		$this->table = $table."_".$i;
		$this->rows = json_decode($this->readFile($this->table.".json"));

		foreach($this->rows as $row){
			 if(eval("return $condition;")){
				 foreach($updates as $key=>$value){
					 echo $key ." ". $value;
					 $row -> $key = $value;
				 }		
				 $this->result[] = $row;
			 }else{
				 $this->result[] = $row;
			 }
		}
		
		$json =json_encode($this->result);
		
		if(!$this->writeFile($this->table.".json",$json)){
			$error++;
		}
		
		}
		if($error == 0){
			return true;
		}else{
			return false;
		}
		}else{
			echo "Table don't exists";
		}
	}
	
	
	private function readFile($filename){
		return file_get_contents($this->base."/".$filename);
	}
	
	
	private function writeFile($filename,$data){
		try{
			file_put_contents($this->base."/".$filename, $data);
			return true;
		}catch(Exception $ex){
			return false;
		}
	}
	
	private function writeTableconfig($table){
		$this->rows_config[$table] = $this->index_rows;	
		$tableconfig = json_encode($this->rows_config);
		$this->writeFile("tableconfig.json", $tableconfig);
	}
	
	private function readTableconfig(){
        if(file_exists($this->base."/tableconfig.json")){
            $tableconfig = $this->readFile("tableconfig.json");
            $this->rows_config = json_decode($tableconfig,true);
        }else{
            echo "Table don't axists !";
        }
	}

	
	public function num_rows(){
		return $this->rows_count;
	}
	
	public function encode($data){
		return base64_encode($data);
	}
	public function decode($data){
		return base64_decode($data);
	}

}
