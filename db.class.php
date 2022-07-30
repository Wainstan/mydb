<?
class db {
	public $host = 'localhost';
	public $user = '';
	public $pass = '';
	public $base = '';
	public $error = '';
	public $res;
	public $last_query = '';
	
  public function connectdb($base, $user, $pass, $host = "localhost"){
      try {
          $this->res = new \pdo("mysql:host=$host;dbname=$base", $user, $pass);
      } catch (\pdoexception $e) {
          echo "database error: " . $e->getmessage();
          die();
      }
      $this->res->query('set names utf8');

      return $this;
  }
    
	function __construct($host='', $user='', $pass='', $base=''){
    if($host)
      $this->host=$host;
    if($user)
      $this->user=$user;
    if($pass)
      $this->pass=$pass;
    if($base)
      $this->base=$base;
    
    $this->connectdb($this->base, $this->user, $this->pass, $this->host = "localhost");
	}
	public function select($table_name, $sub_query='', $fields=""){
		$where = $this->where($sub_query);
		$fields_str = $this->fields($fields);

      $query = "SELECT $fields_str FROM `$table_name`". ' ' . $where;
  
    $this->last_query = $query;
   //print $query;
    $sth = $this->res->query($query);
    //var_dump(debug_backtrace());
      if($sth){
		return $sth->fetchAll(PDO::FETCH_ASSOC);
      }else{
          return false;
      }
	}
	public function pre_select($table_name, $sub_query='', $fields=""){
		$where = $this->where($sub_query);
        if(is_array($fields)){
            $fields_str = '';
            foreach($fields as $field){
                if($fields_str){
                    $fields_str = $fields_str. ', '.$field.' ';}else{
                $fields_str = ' '.$field.' ';}
            }
        }else{
            $fields_str = '*';
        }
      return "SELECT $fields_str FROM `$table_name`". ' ' . $where;
	}
	public function insert($table_name, $field){
	    $query = '';
		if(is_array($field) && count($field) > 0){
			foreach ($field as $index => $value) {
		
				$value = addslashes($value);
		
				if (!$query) {
					$query = "$index = '$value'"; 
				}
				else {
					$query .= ", $index = '$value'";
				}
		
			}
		} else {
			$this->error = "no data to insert!";
			return false;
		}
	    $query = "INSERT INTO $table_name SET " . $query;
	   // print "startsql-".$query."-endsql";
	  $this->last_query = $query;
      $sth = $this->res->query($query);
      return $this->res->lastInsertId();
	}
	public function update($table_name, $field, $sub_query){
	    $query = '';
	
	    foreach ($field as $index => $value) { 
	
	        $value = addslashes($value);
	
	        if (!$query) {
	            $query = "$index = '$value'"; 
	        }
	        else {
	            $query .= ", $index = '$value'";
	        }
	    }
	
	    $where = $this->where($sub_query);
	
	    $query = "UPDATE $table_name SET " . $query . ' ' . $where;
      
      $this->last_query = $query;
      return $this->res->query($query);
	}
	public function delete($table_name, $sub_query=''){
    $where = $this->where($sub_query);
		$query = "DELETE FROM `$table_name`". ' ' . $where;
		$this->last_query = $query;
		return $this->res->query($query);
	}
	public function exist($table, $q){
		$data = $this->select($table, $q, "limit 1");
		return is_array($data);
	}
	public function max($table, $field, $sub_query=0){
    $where = $this->where($sub_query);
		$query = "SELECT max(`$field`) FROM `$table` $where";
		$this->last_query = $query;
		$sth = $this->res->query($query);
		return $sth->fetchColumn();
	}
	public function count($table, $sub_query=0){
    $where = $this->where($sub_query);

		$query = "SELECT count(*) FROM `$table` $where";
        $this->last_query = $query;
		$sth = $this->res->query($query);
		return $sth->fetchColumn();
	}
  
	public function query($query){
    $this->last_query = $query;
    $sth = $this->res->query($query);
        if(is_object($sth)){
    		return $sth->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return $sth;
        }
	}
  public function where($sub_query){
    if (is_array($sub_query)){
		foreach ($sub_query as $index => $value) {
		    if(is_int($index)){
		       // $value = addslashes($value);
    			if (!isset($where)) {
    				$where = "WHERE $value "; 
    			} else {
    				$where .= " AND $value ";
    			}
		    }else{
		        $value = addslashes($value);
    			if (!isset($where)) {
    				$where = "WHERE `$index` = '$value'"; 
    			} else {
    				$where .= " AND `$index` = '$value'";
    			}
		    }
			
		}
	}
    return $where;
  }
  public function fields($fields){
    if(is_array($fields)){
        $fields_str = '';
        foreach($fields as $field){
            if($fields_str){
                $fields_str = $fields_str. ',`'.$field.'`';}else{
            $fields_str = '`'.$field.'`';}
        }
    }else{
        $fields_str = ' * ';
    }
    return $fields_str;
  }
}
?>