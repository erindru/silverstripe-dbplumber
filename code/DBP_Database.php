<?php

class DBP_Database extends ViewableData {
	
	function Name() {
		return DB::getConn()->currentDatabase();
	}

	function Type() {
		return DB::getConn()->getDatabaseServer();
	}

	function Version() {
		return DB::getConn()->getVersion();
	}

	function Adapter() {
		return get_class(DB::getConn());
	}

	function Tables() {

		$tables = new DataObjectSet();

		foreach(DB::tableList() as $table) $tables->push(new DBP_Table($table));
		
		$tables->sort('Name');

		return $tables;
	}
	
	function Link() {
		return Controller::curr()->Link() . 'database/show';
	}
	
	function forTemplate() {
		return $this->renderWith($this->class);
	}

	function DBPLink() {
		return Controller::curr()->Link();
	}

}

class DBP_Database_Controller extends DBP_Controller {

	function execute($request) {

		$vars = $this->getRequest()->requestVars();
		if(empty($vars['query'])) return false;
		$msg = '';
		$error = false;
		$query = $vars['query'];
		$fields = new DataObjectSet();
		$records = new DataObjectSet();
		set_error_handler('exception_error_handler');
		try {
			$results = DB::getConn()->query($query, E_USER_NOTICE);
		} catch(Exception $e) {
			$msg = new ArrayData(array('text' => $e->getMessage(), 'type' => 'error'));
		}
		restore_error_handler();

		if(isset($results) && $results) {
			if(0) {
				// @todo: add routine to determine the number of affected records on a write query
				// no hook for the result ;(
				// any ideas?
				$msg = new ArrayData(array('text' => '4711 records affected', 'type' => 'highlight'));
			} else {
				foreach($results as $result) {
					$record = new DBP_Record();
					$data = array();
					foreach($result as $field => $val) {
						if(!$fields->find('Label', $field)) $fields->push(new DBP_Field($field));
						$data[$field] = strlen($val) > 64 ? substr($val,0,63) . '<span class="truncated">&hellip;</span>' : $val;
					}
					$record->Data($data);
					$records->push($record);
				}
			}
		}

		$records = new ArrayData(
			array(
				'Query' => $query,
				'Fields' => $fields,
				'Records' => $records,
				'Message' => $msg,
				'DBPLink' => $this->instance->DBPLink()
			)                       
		);
		
		return $records->renderWith('DBP_Database_sql');
	}
	
	function export($request) {
		switch($request->postVar('exporttype')) {
			case 'backup':
				$this->backup($request->postVar('tables'));
				break;
		}
	}
	
	function backup($tables) {
		$commands = array();
		foreach($tables as $table) {
			$fields = array();
			$commands[] = 'DELETE FROM "' . $table . '";';
			foreach(DB::fieldList($table) as $name => $spec) $fields[] = $name;
			foreach(DB::query('SELECT * FROM "' . $table . '"') as $record) {
				$cells = array();
				foreach($record as $cell) $cells[] = str_replace("'", "\\'", $cell);
				$commands[] = 
					"INSERT INTO \"$table\" (\"" . 
					implode('", "', $fields) . 
					"\") VALUES ('" . 
					implode("', '", $cells) . 
					"');";
			}
		}
		header("Content-type: text/sql; charset=utf-8");
		header('Content-Disposition: attachment; filename="' . $this->instance->Name() . '_' . date('Ymd_His', time()) . '.sql"');
		foreach($commands as $command) echo $command . "\r\n";
	}
}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new Exception($errstr);
}