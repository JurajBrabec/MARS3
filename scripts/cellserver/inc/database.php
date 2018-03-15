<?php

/*
 * MARS 3.0 MYSQL DATABASE MANAGEMENT PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'DATABASE_NAME', 'Generic SQL' );
define( 'DATABASE_ERROR', '%s. %s error %s: "%s"' );
define( 'DATABASE_NOT_CONNECTED', 'Database not connected' );
define( 'DATABASE_INFO', '%s database' );
define( 'DATABASE_CONNECTED', '%s database "%s@%s" connected.' );
define( 'DATABASE_DURATION_PRECISION', 3 );
define( 'DATABASE_SELECTED', 'Database "%s" selected.' );
define( 'DATABASE_CHARSET_SET', 'Charset "%s" set.' );
define( 'DATABASE_STRING_ESCAPED', 'String "%s" escaped.' );
define( 'DATABASE_QUERY_EXECUTED', 'Query #%s executed.' );
define( 'DATABASE_QUERY_FREE', 'Query #%s results released.' );

define( 'MYSQL_NO_SUPPORT', 'MySQL support not installed in PHP.' );
define( 'MYSQL_NAME', 'MySQL' );
define( 'MYSQL_HOST', '127.0.0.1' );
define( 'MYSQL_PORT', '3306' );
define( 'MYSQL_USER', 'root' );
define( 'MYSQL_PASSWORD', '' );
define( 'MYSQL_DATABASE', 'mysql' );
define( 'MYSQL_ERROR', '%s. %s error %s: "%s" (%s. try) in "%s".' );
define( 'MYSQL_DATABASE_INFO', '%s database %s on %s' );
define( 'MYSQL_DATABASE_NOT_SELECTED', 'Database not selected' );
define( 'MYSQL_CHARSET_NOT_SET', 'Charset not set' );
define( 'MYSQL_STRING_NOT_ESCAPED', 'String not escaped' );
define( 'MYSQL_QUERY_NOT_EXECUTED', 'Query not executed' );
define( 'MYSQL_QUERY_NOT_FREE', 'Query results not freed' );
define( 'MYSQL_ROWS_SELECTED', 'Rows selected: %s' );
define( 'MYSQL_RETRY_ERRORS', serialize( array( 
	1205, 
	1213 ) ) );
define( 'MYSQL_RETRY_DELAY', 100000 );
define( 'MYSQL_RETRY_MAX', 3 );
define( 'MYSQL_START_TRANSACTION', 'START TRANSACTION;');
define( 'MYSQL_COMMIT', 'COMMIT;');

define( 'MYSQLI_NO_SUPPORT', 'MySQLi support not installed in PHP.' );
define( 'MYSQLI_NAME', 'MySQLi' );
define( 'MYSQLI_STRING', 'string' );
define( 'MYSQLI_INT', 'int' );
define( 'MYSQLI_REAL', 'real' );
define( 'MYSQLI_TIMESTAMP', 'timestamp' );
define( 'MYSQLI_DATETIME', 'datetime' );
define( 'MYSQLI_BLOB', 'blob' );
define( 'MYSQLI_OTHER', 'other' );

class sql_database {
	var $class_name = DATABASE_NAME;
	var $host;
	var $user;
	var $password;
	var $database;
	var $handler;
	var $db_info;
	var $error;
	var $message;
	var $charset;
	var $queries;
	var $durations;
	var $sql;
	var $query;
	var $field_count;
	var $fields;
	var $row_count;
	var $rows;
	var $insert_id;
	var $duration;
	var $query_info;

	function sql_database( $host = '', $user = '', $password = '' ) {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = '';
		$this->handler = null;
		$this->db_info = sprintf( DATABASE_INFO, $this->class_name );
		$this->error = 0;
		$this->message = sprintf( DATABASE_CONNECTED, $this->class_name, $this->user, $this->host );
		$this->charset = '';
		$this->queries = 0;
		$this->durations = 0;
		$this->sql = '';
		$this->query = null;
		$this->field_count = 0;
		$this->fields = array();
		$this->row_count = 0;
		$this->rows = array();
		$this->insert_id = 0;
		$this->duration = 0;
		$this->query_info = '';
	}

	function is_connected() {
		return is_resource( $this->handler );
	}

	function select_database( $database = '' ) {
		if ( !$this->is_connected( ) ) {throw new exception( 
				sprintf( DATABASE_ERROR, DATABASE_NOT_CONNECTED, $this->class_name, $this->error, $this->message ) );}
		$this->database = $database;
		$this->error = 0;
		$this->message = sprintf( DATABASE_SELECTED, $this->database );
		$this->charset = '';
		$this->sql = '';
		$this->query = null;
		$this->field_count = 0;
		$this->fields = array();
		$this->row_count = 0;
		$this->rows = array();
		$this->insert_id = 0;
		$this->duration = 0;
		$this->query_info = '';
	}

	function set_charset( $charset = '' ) {
		if ( !$this->is_connected( ) ) {throw new exception( 
				sprintf( DATABASE_ERROR, DATABASE_NOT_CONNECTED, $this->class_name, $this->error, $this->message ) );}
		$this->charset = $charset;
		$this->error = 0;
		$this->message = sprintf( DATABASE_CHARSET_SET, $this->charset );
		$this->sql = '';
		$this->query = null;
		$this->field_count = 0;
		$this->fields = array();
		$this->row_count = 0;
		$this->rows = array();
		$this->insert_id = 0;
		$this->duration = 0;
		$this->query_info = '';
	}

	function escape_string( $string = '' ) {
		if ( !$this->is_connected( ) ) {throw new exception( 
				sprintf( DATABASE_ERROR, DATABASE_NOT_CONNECTED, $this->class_name, $this->error, $this->message ) );}
		$this->error = 0;
		$this->message = sprintf( DATABASE_STRING_ESCAPED, $string );
		return $string;
	}

	function execute_query( $sql = '' ) {
		if ( !$this->is_connected( ) ) {throw new exception( 
				sprintf( DATABASE_ERROR, DATABASE_NOT_CONNECTED, $this->class_name, $this->error, $this->message ) );}
		$this->queries++;
		$this->sql = $sql;
		$this->query = null;
		$this->error = 0;
		$this->message = sprintf( DATABASE_QUERY_EXECUTED, $this->queries );
		$this->field_count = 0;
		$this->fields = array();
		$this->row_count = 0;
		$this->rows = array();
		$this->insert_id = 0;
		$this->duration = microtime( true );
		$this->query_info = '';
	}

	function free_query() {
		if ( !$this->is_connected( ) ) {throw new exception( 
				sprintf( DATABASE_ERROR, DATABASE_NOT_CONNECTED, $this->class_name, $this->error, $this->message ) );}
		$this->sql = '';
		$this->query = null;
		$this->error = 0;
		$this->message = sprintf( DATABASE_QUERY_FREE, $this->queries );
		$this->field_count = 0;
		$this->fields = array();
		$this->row_count = 0;
		$this->rows = array();
		$this->insert_id = 0;
		$this->duration = 0;
		$this->query_info = '';
	}
}

class mysql_database extends sql_database {
	var $class_name = MYSQL_NAME;

	function mysql_database( $host = MYSQL_HOST, $user = MYSQL_USER, $password = MYSQL_PASSWORD ) {
		if ( !mod_mysql( ) ) throw new exception( MYSQL_NO_SUPPORT );
		parent::sql_database( $host, $user, $password );
		$this->handler = mysql_connect( $this->host, $this->user, $this->password );
		if ( !$this->is_connected( ) ) {
			$this->handler = null;
			$this->db_info = $this->message;
			$this->error = mysql_errno( );
			$this->message = trim( mysql_error( ) );
			throw new exception( sprintf( DATABASE_ERROR, DATABASE_NOT_CONNECTED, $this->class_name, $this->error, $this->message ) );
			return false;
		}
		$this->db_info = sprintf( MYSQL_DATABASE_INFO, $this->class_name, mysql_get_server_info( $this->handler ), 
			mysql_get_host_info( $this->handler ) );
		return true;
	}

	function select_database( $database = '' ) {
		parent::select_database( $database );
		if ( !mysql_select_db( $database, $this->handler ) ) {
			$this->database = '';
			$this->error = mysql_errno( $this->handler );
			$this->message = trim( mysql_error( $this->handler ) );
			throw new exception( sprintf( DATABASE_ERROR, MYSQL_DATABASE_NOT_SELECTED, $this->class_name, $this->error, $this->message ) );
			return false;
		}
		return true;
	}

	function set_charset( $charset = '' ) {
		parent::set_charset( $charset );
		if ( !mysql_set_charset( $charset, $this->handler ) ) {
			$this->charset = '';
			$this->error = mysql_errno( $this->handler );
			$this->message = trim( mysql_error( $this->handler ) );
			throw new exception( sprintf( DATABASE_ERROR, MYSQL_CHARSET_NOT_SET, $this->class_name, $this->error, $this->message ) );
			return false;
		}
		return true;
	}

	function escape_string( $string = '' ) {
		$result = mysql_real_escape_string( parent::escape_string( $string ), $this->handler );
		if ( $result === false ) {
			$this->error = mysql_errno( $this->handler );
			$this->message = trim( mysql_error( $this->handler ) );
			throw new exception( sprintf( DATABASE_ERROR, MYSQL_STRING_NOT_ESCAPED, $this->class_name, $this->error, $this->message ) );
		}
		return $result;
	}

	function execute_query( $sql = '', $values = array( ) ) {
		krsort( $values );
		foreach ( $values as $key => $value ) {
			$sql = str_replace( '%' . $key, $value, $sql );
		}
		parent::execute_query( $sql );
		$try = 0;
		while ( $this->query === null ) {
			usleep( $try * MYSQL_RETRY_DELAY );
			$try++;
			$this->query = mysql_query( $this->sql, $this->handler );
			!$this->query and ( $try < MYSQL_RETRY_MAX ) and
				 in_array( mysql_errno( $this->handler ), unserialize( MYSQL_RETRY_ERRORS ) ) && $this->query = null;
		}
		if ( !$this->query ) {
			$this->duration = 0;
			$this->error = mysql_errno( $this->handler );
			$this->message = trim( mysql_error( $this->handler ) );
			throw new exception( 
				sprintf( MYSQL_ERROR, MYSQL_QUERY_NOT_EXECUTED, $this->class_name, $this->error, $this->message, $try, $this->sql ) );
			return false;
		}
		if ( is_resource( $this->query ) ) {
			$this->field_count = mysql_num_fields( $this->query );
			while ( $field = mysql_fetch_field( $this->query ) ) {
				$this->fields[ ] = $field;
			}
			$this->row_count = mysql_num_rows( $this->query );
			while ( $row = mysql_fetch_array( $this->query ) ) {
				$this->rows[ ] = $row;
			}
			$this->query_info = sprintf( MYSQL_ROWS_SELECTED, $this->row_count );
		} else {
			$this->query_info = mysql_info( $this->handler );
			$this->row_count = mysql_affected_rows( $this->handler );
			$this->insert_id = mysql_insert_id( $this->handler );
		}
		$this->duration = round( microtime( true ) - $this->duration, DATABASE_DURATION_PRECISION );
		$this->durations = $this->durations + $this->duration;
		return $this->row_count;
	}

	function start_transaction() {
		$this->execute_query( MYSQL_START_TRANSACTION );
	}
	
	function commit() {
		$this->execute_query( MYSQL_COMMIT );
	}
	
	function free_query() {
		$query = $this->query;
		parent::free_query( );
		if ( is_resource( $query ) and !mysql_free_result( $query ) ) {
			$this->error = mysql_errno( $this->handler );
			$this->message = trim( mysql_error( $this->handler ) );
			throw new exception( sprintf( DATABASE_ERROR, MYSQL_QUERY_NOT_FREE, $this->class_name, $this->error, $this->message ) );
			return false;
		}
		return true;
	}
}

class mysqli_database extends sql_database {
	var $class_name = MYSQLI_NAME;
	var $port;

	function mysqli_database( $host = MYSQL_HOST, $user = MYSQL_USER, $password = MYSQL_PASSWORD, $database = MYSQL_DATABASE, $port = MYSQL_PORT ) {
		if ( !mod_mysqli( ) ) throw new exception( MYSQLI_NO_SUPPORT );
		parent::sql_database( $host, $user, $password );
		$this->database = $database;
		$this->port = $port;
		$this->handler = @new mysqli( $this->host, $this->user, $this->password, $database, $port );
		if ( !$this->is_connected( ) ) {
			$this->db_info = $this->message;
			if ( version_compare( phpversion( ), '5.3.0', '>=') ) {
				$this->error = $this->handler->errno;
				$this->message = trim( $this->handler->error );
			} else {
				$this->error = mysqli_connect_errno( );
				$this->message = trim( mysqli_connect_error( ) );
			}
			throw new exception( sprintf( DATABASE_ERROR, DATABASE_NOT_CONNECTED, $this->class_name, $this->error, $this->message ) );
			$this->handler = null;
			return false;
		}
		$this->db_info = sprintf( MYSQL_DATABASE_INFO, $this->class_name, $this->handler->client_info,
				$this->handler->host_info );
		return true;
	}

	function is_connected() {
		if ( version_compare( phpversion( ), '5.3.0', '>=') ) {
			return !$this->handler->connect_error;
		} else {
			return !mysqli_connect_error( );
		}
	}
	
	function select_database( $database = '' ) {
		parent::select_database( $database );
		if ( !$this->handler->select_db( $database ) ) {
			$this->database = '';
			$this->error = $this->handler->errno;
			$this->message = trim( $this->handler->error );
			throw new exception( sprintf( DATABASE_ERROR, MYSQL_DATABASE_NOT_SELECTED, $this->class_name, $this->error, $this->message ) );
			return false;
		}
		return true;
	}

	function set_charset( $charset = '' ) {
		parent::set_charset( $charset );
		if ( !$this->handler->set_charset( $charset ) ) {
			$this->charset = '';
			$this->error = $this->handler->errno;
			$this->message = trim( $this->handler->error );
			throw new exception( sprintf( DATABASE_ERROR, MYSQL_CHARSET_NOT_SET, $this->class_name, $this->error, $this->message ) );
			return false;
		}
		return true;
	}

	function escape_string( $string = '' ) {
		$result = $this->handler->escape_string( parent::escape_string( $string ) );
		if ( $result === false ) {
			$this->error = $this->handler->errno;
			$this->message = trim( $this->handler->error );
			throw new exception( sprintf( DATABASE_ERROR, MYSQL_STRING_NOT_ESCAPED, $this->class_name, $this->error, $this->message ) );
		}
		return $result;
	}

	function execute_query( $sql = '', $values = array( ) ) {
		krsort( $values );
		foreach ( $values as $key => $value ) {
			$sql = str_replace( '%' . $key, $value, $sql );
		}
		parent::execute_query( $sql );
		$try = 0;
		while ( $this->query === null ) {
			usleep( $try * MYSQL_RETRY_DELAY );
			$try++;
			$this->query = $this->handler->query( $this->sql );
			!$this->query and ( $try < MYSQL_RETRY_MAX ) and
			in_array( $this->handler->errno, unserialize( MYSQL_RETRY_ERRORS ) ) && $this->query = null;
		}
		if ( !$this->query ) {
			$this->duration = 0;
			$this->error = $this->handler->errno;
			$this->message = trim( $this->handler->error );
			throw new exception(
					sprintf( MYSQL_ERROR, MYSQL_QUERY_NOT_EXECUTED, $this->class_name, $this->error, $this->message, $try, $this->sql ) );
			return false;
		}
		if ( is_object( $this->query ) ) {
			$this->field_count = $this->query->field_count;
			while ( $field = $this->query->fetch_field( ) ) {
				$field->typeid = $field->type;
				switch ( $field->typeid ) {
					case MYSQLI_TYPE_CHAR:
					case MYSQLI_TYPE_STRING: 
					case MYSQLI_TYPE_VAR_STRING:
						$field->type = MYSQLI_STRING; break; 
					case MYSQLI_TYPE_SHORT:
					case MYSQLI_TYPE_LONG:
					case MYSQLI_TYPE_LONGLONG:
					case MYSQLI_TYPE_INT24:
						$field->type = MYSQLI_INT; break;
					case MYSQLI_TYPE_FLOAT:
					case MYSQLI_TYPE_DOUBLE:
					case MYSQLI_TYPE_DECIMAL:
					case MYSQLI_TYPE_NEWDECIMAL:
						$field->type = MYSQLI_REAL;break;
					case MYSQLI_TYPE_TIMESTAMP:
						$field->type = MYSQLI_TIMESTAMP; break;
					case MYSQLI_TYPE_YEAR:
					case MYSQLI_TYPE_DATE:
					case MYSQLI_TYPE_TIME:
					case MYSQLI_TYPE_DATETIME:
					case MYSQLI_TYPE_NEWDATE:
						$field->type = MYSQLI_DATETIME; break;
					case MYSQLI_TYPE_TINY_BLOB:
					case MYSQLI_TYPE_MEDIUM_BLOB:
					case MYSQLI_TYPE_LONG_BLOB:
					case MYSQLI_TYPE_BLOB:
						$field->type = MYSQLI_BLOB; break;
					case MYSQLI_TYPE_NULL:
					case MYSQLI_TYPE_INTERVAL:
					case MYSQLI_TYPE_SET:
					case MYSQLI_TYPE_GEOMETRY:
					case MYSQLI_TYPE_BIT:
					default:
						$field->type = MYSQLI_OTHER;
				}
				$this->fields[ ] = $field;
			}
			$this->row_count = $this->query->num_rows;
			while ( $row = $this->query->fetch_array( ) ) {
				$this->rows[ ] = $row;
			}
			$this->query_info = sprintf( MYSQL_ROWS_SELECTED, $this->row_count );
			$this->query->free();
		} else {
			$this->query_info = $this->handler->info;
			$this->row_count = $this->handler->affected_rows;
			$this->insert_id = $this->handler->insert_id;
		}
		$this->duration = round( microtime( true ) - $this->duration, DATABASE_DURATION_PRECISION );
		$this->durations = $this->durations + $this->duration;
		return $this->row_count;
	}

	function start_transaction() {
		$this->handler->autocommit( FALSE );
	}

	function commit() {
		$this->handler->commit( );
		$this->handler->autocommit( TRUE );
	}

	function rollback() {
		$this->handler->rollback( );
		$this->handler->autocommit( TRUE );
	}
	
}

function mod_mysql() {
	return function_exists( 'mysql_connect' );
}

function mod_mysqli() {
	return class_exists( 'mysqli' );
}
