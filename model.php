<?php

class model
{
	private   $model;
	protected $dbname;
	public 	  $insert_id = '-1';//上次插入的ID
	protected $result;
	protected $field = '*';
	protected $where = '';
	protected $in    = '';
	protected $orderby = '';
	public 	  $page;
	function __construct()
	{
		$this->model = new mysqli('127.0.0.1','root','root','test');
		$this->model->set_charset('utf8');
	}

	function __destruct()
	{	
		$this->model->close();
	}
	/*
	 *$type 执行类型
	 *$data $updata 数据 / 条件
	 *return 影响行
	 */
	public function query($type,$data,$updata = '')
	{
		$key = '';
		$val = '';
		empty($this->dbname)?die('选择表！'):true;
		switch ($type) {
			case 'insert':
					if (empty($data)) {
						die("插入数据不能为空！");
					}
					foreach ($data as $k => $v) {
						$key .= '`'.$k.'`'.' ,';
						$val .= is_string($v)? '\''.$v.'\''. ',': $v.' ,';	
					}
					$key = rtrim($key, ',');
					$val = rtrim($val, ',');
					$sql = "INSERT INTO ".$this->dbname.'('.$key.')VALUES('.$val.');';
				break;

			case 'update':
					if (empty($updata)) {
						die("条件不能为空！");
					}
					foreach ($data as $k1 => $v1) {
						if (is_string($v1)) {
							$key .= ',`'.$k1.'`= '.'\''.$v1.'\'';
						}else{
							$key .= ',`'.$k1.'`= '.$v1;
						}
						 
					}
					foreach ($updata as $k_1 => $v_1) {
						$val .= " AND $k_1 = $v_1";
					}
					$key = ltrim($key, ',');
					$sql = "UPDATE ".$this->dbname." SET ".$key." WHERE 1 ".$val;
				break;

			case 'delete':
					if (empty($data)) {
						die("条件不能为空！");
					}
					foreach ($data as $k => $value) {
						$key .= " AND $k = $value";
					}
					
					$sql = "DELETE FROM ".$this->dbname." WHERE 1".$key;
					
				break;
			default:
					die("error Unknown type");
				break;
		}
		
		return  $this->execu($sql);
		 	
	}
	//执行原生语句
	public function execu($sql)
	{
		$this->model->query($sql);
		$this->i_id =  $this->model->insert_id;
		return $this->model->affected_rows;
	}
	/*
	 *$sql为空,有条件查询否则执行原生查询语句;
     *return 返回结果集
	 */
	public function select($sql = '')
	{
		$res = array();
		if (empty($sql)) {
			$sql = "SELECT ".$this->field." FROM ".$this->dbname." WHERE 1".$this->where.' '.$this->in.$this->orderby;	
			
		}
		$result = $this->model->query($sql);
		while ($row = $result->fetch_assoc()) {
			array_push($res, $row);
	    }
		$result->free();	
	   
    	return $res;
	}
	//字符串或一维关联数组
	public function where($where = '')
	{
		empty($where)?die('参数不能为空！'):true;
		$wher = '';
		$this->where .= empty($wher)?$where:$wher;
		return $this;
	}
	//选择数据库
	public function db($dbname)
	{
		$this->dbname = $dbname;
		return $this;
	}

	//字符串|一维数组
	public function field($str)
	{
		$st = '';
		if (is_array($str)) {
			foreach ($str as  $val) {
				$st .= ','.$val;
			}
			$str = ltrim($st, ',');
		}
		$this->field = $str;
		return $this;
	}

	//一维数组
	public function in($filed, $array)
	{
		empty($filed)?die("字段不能为空！"):true;
		$str = '';
		if (is_array($array)) {
			
			foreach ($array as $value) {
				if (is_string($value)) {
					$str .= ' ,'.'\''.$value.'\'';
				}else{
					$str .= ','.$value; 
				}
			}
		
			$st = ltrim($str,',');
			$this->in = ' AND '.$filed." IN ( ".$st.")";
		}else{
			die("参数不是数组！");
		}
		return $this;
	}

	public function orderby($field, $where = 'ASC')
	{
		empty($field)?die('error field!'):true;
		$this->orderby = " ORDER BY ".$field." $where";
		return $this;
	}
	
	//分页
	public function pegeone($dbname,$number)
	{
		$db = '';
		if(is_array($dbname)){

			foreach ($dbname as  $v) {
				$db .= ','.$v;
			}
			$db = ltrim($db, ',');
			
		}else{

		}
		$_GET['page'] = isset($_GET['page'])?$_GET['page']:0;
		$page = $_GET['page'] / $number;
		if (empty($this->where)) {
			$mysq = "";
		}else{
			$mysq = " WHERE ".$this->where;
			
		}
		$sql = "SELECT ".$this->field." FROM ".$db.$mysq;
		
		$this->model->query($sql);

		$total = $this->model->affected_rows - 1;
		$url = strstr('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'?',true);
		
		$jj = ceil($total / $number);//获取分页数
 		$li = '';
 		$start = ($page - 5) < 0?0: $page - 5;
 		$stop =  ($page + 5) > $jj ?$jj:$page + 5;
 		for ($start,$b=($start*$number); $start < $stop; $start++,$b+=$number) { 
 			
 			$li .= "<li style='text-decoration:none; background-color: #FFFFFF;border: 1px solid #DDDDDD;float: left;line-height: 1.42857;margin-left: -1px;padding: 4px 10px;position: relative;'><a href='$url?page=$b' style='text-decoration: none;'>{$start}</a><li>";
 		
 		}
 		$wei = ($jj - 1) * $number;
 		$jian = $_GET['page'] + $number > $total ?$_GET['page'] :$_GET['page'] + $number;//下一页
		$jia  = $_GET['page'] - $number < 0 ?0:$_GET['page'] - $number;//上一页
$str = <<<START
		<ul style='list-style-type:none;'>
			<li style='text-decoration:none; background-color: #FFFFFF;border: 1px solid #DDDDDD;float: left;line-height: 1.42857;margin-left: -1px;padding: 4px 10px;position: relative;text-decoration: none;'><a href='$url?page=0' style='text-decoration: none;'>首页</a><li>
			<li style='background-color: #FFFFFF;border: 1px solid #DDDDDD;float: left;line-height: 1.42857;margin-left: -1px;padding: 4px 10px;position: relative;'><a href='$url?page=$jia' style='text-decoration: none;'>上一页</a><li>
			$li
			<li style='background-color: #FFFFFF;border: 1px solid #DDDDDD;float: left;line-height: 1.42857;margin-left: -1px;padding: 4px 10px;position: relative;'><a href='$url?page=$jian' style='text-decoration: none;'>下一页</a><li>
			<li style='background-color: #FFFFFF;border: 1px solid #DDDDDD;float: left;line-height: 1.42857;margin-left: -1px;padding: 4px 10px;position: relative;'><a href='$url?page=$wei' style='text-decoration: none;'>尾页</a><li>
		</ul>
		<input type="text" value="" id="page_input" style='width:40px;background-color: #FFFFFF;border: 1px solid #DDDDDD;float: left;line-height:22px;;margin-left: 5px;padding: 4px 10px;'/>
		<button type="button" onclick="on_page(this)" value="$number" data='$jj' style='width:33px;background-color: #FFFFFF;border: 1px solid #DDDDDD;float: left;line-height:22px;;margin-left: 5px;padding: 4px 2px;color:blue;'>跳转</button>
		
		<script>
			function on_page(th)
			{
				var page  = document.getElementById("page_input").value;
				var total = th.getAttribute('data');
				var val = th.getAttribute('value');
				console.log(total);
				if (parseInt(page) >= 0 && parseInt(page) < parseInt(total)) {
					
					location.href = '$url?page='+page*val;
				}

			}
		</script>
START;
		$this->page = $total>0?$str:null;
		$sql.= " LIMIT {$_GET['page']} , ".$number;
		echo $sql;
		$res = array();
		$result = $this->model->query($sql);
		while ($row = $result->fetch_assoc()) {
			array_push($res, $row);
	    }
		$result->free();	
	   
    	return $res;
	}
}
