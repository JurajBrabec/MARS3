<?php

/*
 * MARS 3.0 APPLICATION PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'MARS_APPLICATION', 'Monitoring and Reporting Script (MARS) v3.0' );   
define( 'MARS_COPYRIGHT', '&copy;2015 Juraj Brabec, HP Inc.' );
define( 'MARS_CONFIG', 'config.ini' );
define( 'MARS_LOCKFILE', 'mars.lock' );
define( 'MARS_LOG_ACTIONS', 'log/actions.log' );
define( 'MARS_LOG_ERRORS', 'log/errors.log' );
define( 'MARS_LOG_DEBUG', 'log/debug.log' );
define( 'MARS_DURATION_PRECISION', 2 );
define( 'MARS_MAX_DURATION', 3500 );
define( 'MARS_OUTPUT', '(+%-2s) %s' );
define( 'MARS_SEVERITY_ACTION', 'Action' );
define( 'MARS_SEVERITY_OK', 'OK' );
define( 'MARS_SEVERITY_WARNING', 'Warning' );
define( 'MARS_SEVERITY_ERROR', 'Error' );
define( 'MARS_SEVERITY_DEBUG', 'Debug' );
define( 'MARS_LOG_MAX_SIZE', 10 * 1024 * 1024 );
define( 'MARS_LOG_MESSAGE_LIMIT', 256 );
define( 'MARS_LOG_OLD', '%s.bak' );
define( 'MARS_LOG_TIMESTAMP', 'Y-m-d_H-i-s' );
define( 'MARS_LOG', '%-19s %-5s (+%-5s) %s' );
define( 'MARS_ERROR_WRITING', 'Error writing message: %s' );
define( 'MARS_PERMANENT_STRING', 'Permanent' );
define( 'MARS_PERMANENT_DATE', '2038-01-19 03:14:07' );
define( 'MARS_ERROR_UNHANDLED_PARAMETER', 'Unhandled parameter "%s".' );
define( 'MARS_ERROR', 'Error: %s: %s' );
define( 'MARS_ERROR_CONFIG_MISSING', 'Config file "%s" is missing.' );
define( 'MARS_DP7_MAIN_SERVICE', 'RDS' );
define( 'MARS_DP8_MAIN_SERVICE', 'HPDP-IDB' );
define( 'MARS_SERVICE_ALL', 'All services' );
define( 'MARS_SERVICE_ACTIVE', 'Active' );
define( 'MARS_SERVICE_DOWN', 'Down' );
define( 'MARS_SERVICE_MAINTENANCE', 'Maintenance' );
define( 'MARS_MMD_LOCAL', 'CMMDB' );
define( 'MARS_ERROR_SERVICE_NOT_RUNNING', 'Service "%s" not running.' );
define( 'MARS_ERROR_EXEC', 'Cannot start command "%s".' );
define( 'MARS_ERROR_EXEC_TIMEOUT', 'Timeout %s seconds in command "%s".' );
define( 'MARS_ERROR_EXEC_ERRORLEVEL', 'Errorlevel %s in command "%s": "%s".' );
define( 'MARS_TIMEOUT_MULTIPLIER', 'TIMEOUT_MULTIPLIER' );
define( 'MARS_OMNIDBUTIL_TIMEOUT', 10 );
define( 'MARS_OMNIDBUTIL_SHOW_CELL_NAME', 'Catalog database owner: "(\S+)"' );
define( 'MARS_ERROR_CELLSERVER_NOT_SET_UP', 'Cell Server "%s" is not set up.' );
define( 'MARS_OMNISV_TIMEOUT', 10 );
define( 'MARS_OMNISV_VERSION', 'Data Protector (\S+): OMNISV, internal build (\d+)' );
define( 'MARS_ERROR_SERVICE_NOT_ACTIVE', 'DP Service %s is %s' );
define( 'MARS_ITO_DEFAULT_PRIORITY', 'high' );
define( 'MARS_ITO_MESSAGE', 'Creating "%s" ticket "%s".' );
define( 'MARS_ITO', '%s::%s;"%s";%s' );
define( 'MARS_ERROR_CANNOT_OPEN', 'Cannot open file %s.' );
define( 'MARS_ERROR_CANNOT_WRITE', 'Cannot write to file %s.' );
define( 'MARS_ERROR_CANNOT_CLOSE', 'Cannot close file %s.' );
define( 'MARS_PS_TIMEOUT', 10 );
define( 'MARS_OMNISTAT_TIMEOUT', 15 );
define( 'MARS_OMNIRPT_TIMEOUT', 15 );
define( 'MARS_TEST', 'Testing installation of PHP version %s on %s platform' );
define( 'MARS_TEST_MOD_NOT_SUPPORTED', '%-5s Mod "%s" not supported in PHP.' );
define( 'MARS_TEST_FILE_FOUND', '%-5s File "%s" found.' );
define( 'MARS_TEST_FILE_NOT_FOUND', '%-5s File "%s" not found.' );
define( 'MARS_TEST_CELLSERVER', '%-5s Cell Server is "%s".' );
define( 'MARS_TEST_OMNISTAT', '%-5s OMNISTAT.' );
define( 'MARS_TEST_SESSION', '%-5s Last completed backup SessionID is "%s".' );
define( 'MARS_TEST_OMNIRPT', '%-5s OMNIRPT.' );
define( 'MARS_TEST_SESSION_DETAILS', '      Session "%s" was "%s" and "%s".' );
define( 'MARS_TEST_NO_SESSION', '%-5s No session is available so far.' );
define( 'MARS_TEST_MYSQL', '%-5s Connected to MySQL on "%s".' );
define( 'MARS_TEST_NO_MYSQL', '%-5s No connection to MySQL on "%s": "%s".' );
define ('UCS_4BE', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define ('UCS_4LE', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define ('UCS_2BE', chr(0xFE) . chr(0xFF));
define ('UCS_2LE', chr(0xFF) . chr(0xFE));
define ('UTF_8B' , chr(0xEF) . chr(0xBB) . chr(0xBF));

define( 'MARS_DEBUG_EXECUTION_STARTED', 'Execution started ("%s").' );
define( 'MARS_DEBUG_EXECUTION_FINISHED', 'Execution finished (%s sec.).' );
define( 'MARS_DEBUG_EXECUTION_NO_RESOURCE', 'Execution failed: No resource.' );
define( 'MARS_DEBUG_EXECUTION_TIMED_OUT', 'Execution timed out (%s sec.).' );

define( 'MARS_WORKERS', 4 );
define( 'MARS_DEBUG_WORKER_START', 'Worker#%d/%d started cmd#%d/%d (%s).' );
define( 'MARS_DEBUG_WORKER_FINISH', 'Worker#%d/%d finished cmd#%d/%d (%ss).' );

if ( !function_exists( 'sys_get_temp_dir' ) ) {
	function sys_get_temp_dir( ) {
		if ( !empty( $_ENV[ 'TMP' ] ) ) { return realpath( $_ENV[ 'TMP' ] ); }
		if ( !empty( $_ENV[ 'TMPDIR' ] ) ) { return realpath( $_ENV[ 'TMPDIR' ] ); }
		if ( !empty( $_ENV[ 'TEMP' ] ) ) { return realpath( $_ENV[ 'TEMP' ] ); }
		$tempfile = tempnam( __FILE__, '' );
		if ( file_exists( $tempfile ) ) {
			unlink( $tempfile );
			return realpath( dirname( $tempfile ) );
		}
		return null;
	}
}

if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
	$foreign_file = dirname( realpath( $GLOBALS[ 'argv' ][ 0 ] ) ) . '\mars.sh';
	define( 'MARS_PROC_OPEN', '"%s"' );
	define( 'MARS_EXEC', 'start "CMD" /B /WAIT %s' );
	define( 'MARS_OMNIDBUTIL_SHOW_CELL_NAME_CMD', '"%s\omnidbutil.exe" -show_cell_name' );
	define( 'MARS_OMNISV_VERSION_CMD', '"%s\omnisv.exe" -version' );
	define( 'MARS_OMNISV_STATUS_CMD', '"%s\omnisv.exe" -status' );
	define( 'MARS_PS_CMD', 'wmic process get CommandLine | findstr SCHEDULER | findstr -v findstr' );
	define( 'MARS_OMNISTAT_PREVIOUS_CMD', '"%s\omnistat.exe" -previous -last 1' );
	define( 'MARS_OMNIRPT_SINGLE_SESSION_CMD', 
		'"%s\omnirpt.exe" -report single_session -level critical -session %s -tab' );
} else {
	$foreign_file = dirname( realpath( $GLOBALS[ 'argv' ][ 0 ] ) ) . '/mars.cmd';
	define( 'MARS_PROC_OPEN', '%s' );
	define( 'MARS_EXEC', 'exec %s' );
	define( 'MARS_OMNIDBUTIL_SHOW_CELL_NAME_CMD', '"%s/omnidbutil" -show_cell_name' );
	define( 'MARS_OMNISV_VERSION_CMD', '"%s/omnisv" -version' );
	define( 'MARS_OMNISV_STATUS_CMD', '"%s/omnisv" -status' );
	define( 'MARS_PS_CMD', 'ps -ef | grep SCHEDULER | grep -v grep' );
	define( 'MARS_OMNISTAT_PREVIOUS_CMD', '"%s/omnistat" -previous -last 1' );
	define( 'MARS_OMNIRPT_SINGLE_SESSION_CMD', 
		'"%s/omnirpt" -report single_session -level critical -session %s -tab' );
}
file_exists( $foreign_file ) && unlink( $foreign_file );

require_once 'events.php';
require_once 'database.php';
require_once 'actions.php';
require_once 'notifications.php';
require_once 'routines.php';
require_once 'specifications.php';
require_once 'libraries.php';
require_once 'devices.php';
require_once 'media.php';
require_once 'copylists.php';

class worker {
	var $id;
	var $name;
	var $cid;
	var $params;
	var $starttime;
	var $endtime;
	var $duration;
	var $output;
	var $error;
	var $stdout;
	var $stderr;
	var $process;
	var $status;

	public function __construct( $id, $cid, $params = array( ) ) {
		if ( empty( $params[ 'CMD' ] ) ) return false;
		$this->id = $id;
		$this->name = empty( $params[ 'NAME' ] ) ? base64_encode( $params[ 'CMD' ] ) : $params[ 'NAME' ];
		$this->cid = $cid;
		$this->params = $params;
		$this->starttime = microtime( true );
		$this->endtime = 0;
		$this->duration = 0;
		$this->output = array( );
		$this->error = array( );
		$this->stdout = tempnam( sys_get_temp_dir( ), '' );
		$this->stderr = tempnam( sys_get_temp_dir( ), '' );
		$descriptorspec = array(
				1 => array( 'file', $this->stdout, 'w' ),
				2 => array( 'file', $this->stderr, 'w' ),
		);
		$this->process = proc_open( sprintf( MARS_PROC_OPEN, $params[ 'CMD' ] ), $descriptorspec, $pipes );
		if ( !is_resource( $this->process ) ) return false;
		$this->status = proc_get_status( $this->process );
	}

	public function done( ) {
		usleep( 1 );
		$this->status = proc_get_status( $this->process );
		return !$this->status[ 'running' ];
	}

	public function finish( ) {
		$this->endtime = microtime( true );
		$this->duration = round( $this->endtime - $this->starttime, 2 );
		$this->status = proc_get_status( $this->process );
		proc_close( $this->process );
		$contents = file_get_contents( $this->stderr );
		!empty( $contents ) && $this->error = explode( PHP_EOL, $contents );
		$contents = file_get_contents( $this->stdout );
		!empty( $contents ) && $this->output = explode( PHP_EOL, $contents );
		$this->output = array_merge( $this->output, $this->error );
		unlink( $this->stdout );
		unlink( $this->stderr );
	}
}

class workers {
	var $workers;
	var $wid;
	var $queue;
	var $cid;

	public function __construct( $count = 1 ) {
		$this->workers = array_fill(0, $count, NULL );
		$this->wid = 0;
		$this->queue = array( );
		$this->cid = 0;
	}

	public function worker( $wid = NULL ) {
		$wid == NULL && $wid = $this->wid;
		if ( $wid < 0 or $wid >= count( $this->workers ) ) return false;
		return $this->workers[ $wid ];
	}

	public function working( ) {
		$i = 0;
		foreach( $this->workers as $worker ) {
			!empty( $worker ) && $i++;
		}
		return $i;
	}

	public function idle( ) {
		$i = 0;
		foreach( $this->workers as $worker ) {
			empty( $worker ) && $i++;
		}
		return $i;
	}

	public function waiting( ) {
		return ( $this->cid < count( $this->queue ) ) and ( $this->idle( ) > 0 );
	}
	
	
	public function next( ) {
		$i = $this->wid + 1;
		while( $i < count( $this->workers ) and empty( $this->workers[ $i ] ) ) {
			$i++;
		}
		if ( $i == count( $this->workers ) ) {
			$i = 0;
			while( $i <= $this->wid  and empty($this->workers[ $i ] ) ) {
				$i++;
			}
		}
		if ( $i < 0 or $i >= count( $this->workers ) ) return false;
		$this->wid = $i;
		return $i;
	}

	public function start( ) {
		if ( !$this->waiting( ) ) return false;
		$i = 0;
		while( $i < count( $this->workers ) and !empty( $this->workers[ $i ] ) ) {
			$i++;
		}
		$params = $this->queue[ $this->cid ];
		$worker = new worker( $i + 1, $this->cid + 1, $params );
		if ( $worker ) {
			$this->workers[ $i ] = $worker;
		}
		$this->cid++;
		return $worker;
	}

	public function encode( $worker = NULL ) {
		$worker === NULL && $worker = $this->worker( );
		return serialize( $worker );
	}

	public function decode( $worker ) {
		return unserialize( $worker );
	}

	public function done( ) {
		$worker = $this->worker( );
		return $worker ? $worker->done( ) : false;
	}
	
	public function finish( ) {
		$worker = $this->worker( );
		$this->workers[ $this->wid ] = NULL;
		$worker && $worker->finish( );
		return $worker;
	}

	public function add( $params = array( ), $priority = false ) {
		foreach ( $this->queue as $queue ) {
			if ( $queue[ 'CMD' ] == $params[ 'CMD' ] ) return false;
		}
		if ( $priority ) {
			array_splice( $this->queue, $this->cid, 0, array( $params ) );
		} else {
			$this->queue[ ] = $params;
		}
		return true;
	}
}

class mars_application {
	var $name = MARS_APPLICATION;
	var $root;
	var $duration_precision = MARS_DURATION_PRECISION;
	var $paramters;
	var $micro_time;
	var $start_time;
	var $os;
	var $dp;
	var $config = array();
	var $database;
	var $cellserver;
	var $service = array();
	var $mmd_local;
	var $down;
	var $workers;

	function mars_application( $parameters ) {
		$this->micro_time = microtime( true );
		$this->parameters = $parameters;
		$this->root = dirname( realpath( $this->parameters[ 0 ] ) );
		$this->hostname = php_uname( 'n' );
		$this->php = phpversion( );
		mod_mysqli( ) && $this->php .= 'i';
		$this->os = php_uname( 's' );
		$this->workers = NULL;
	}

	function output( $message ) {
		print ( sprintf( MARS_OUTPUT, $this->get_duration( ), $message ) ) . PHP_EOL;
	}

	function log( $severity, $message ) {
		switch ( $severity ) {
			case MARS_SEVERITY_DEBUG:
				$file = MARS_LOG_DEBUG;
				break;
			case MARS_SEVERITY_WARNING:
			case MARS_SEVERITY_ERROR:
				$file = MARS_LOG_ERRORS;
				break;
			case MARS_SEVERITY_ACTION:
			default:
				$file = MARS_LOG_ACTIONS;
				break;
		}
		$log = $this->root . DIRECTORY_SEPARATOR . $file;
		if ( file_exists( $log ) and ( filesize( $log ) > ( MARS_LOG_MAX_SIZE ) ) ) {
			rename( $log, sprintf( MARS_LOG_OLD, $log ) );
			clearstatcache( );
		}
		$this->append_file( $log, sprintf( MARS_LOG, date( MARS_LOG_TIMESTAMP ), 
			getmypid( ), $this->get_duration( ), $message ) );
		if ( isset( $this->database ) and $this->database->is_connected( ) ) {
			try {
				$sql = "insert into mars_log (cellserver,pid,duration,severity,message) " 
					. "values ('%cellserver',%pid,%duration,'%severity','%message');";
				$values = array( 
					'cellserver' => $this->cellserver, 
					'pid' => getmypid( ), 
					'duration' => $this->get_duration( ), 
					'severity' => $severity, 
					'message' => $this->database->escape_string( 
						substr( $message, 0, MARS_LOG_MESSAGE_LIMIT ) ) );
				$this->database->execute_query( $sql, $values );
			}
			catch ( exception $e ) {
				$this->output( sprintf( MARS_ERROR_WRITING, $e->getmessage( ) ) );
			}
		}
	}

	function log_action( $message ) {
		if ( trim( $message ) == '' ) return;
		$this->log( MARS_SEVERITY_ACTION, $message );
		$this->output( $message );
	}

	function log_warning( $message ) {
		if ( trim( $message ) == '' ) return;
		$this->log( MARS_SEVERITY_WARNING, $message );
		$this->output( $message );
	}

	function log_error( $message ) {
		if ( trim( $message ) == '' ) return;
		$this->log( MARS_SEVERITY_ERROR, $message );
		$this->output( $message );
	}

	function debug( $message ) {
		if ( $this->config[ 'DEBUG' ] != 1 ) return;
		if ( trim( $message ) == '' ) return;
		$this->log( MARS_SEVERITY_DEBUG, $message );
		$this->output( $message );
	}
	
	function parse_decimal( $decimal ) {
		return str_replace( ',', $this->config[ 'DECIMAL' ], $decimal );
	}

	function parse_date( $date ) {
		$date == MARS_PERMANENT_STRING && $date = MARS_PERMANENT_DATE;
		$date = str_replace( '. ', '.', $date );
		if ( !strtotime( $date ) ) {
			return '';
		} else {
			return date( $this->config[ 'TIME_FORMAT' ], strtotime( $date ) );
		}
	}

	function maintenance( ) {
		$result = false;
		if ( !empty( $this->config[ 'MAINTENANCE_FILE' ] ) ) {
			$file = $this->config[ 'MAINTENANCE_FILE' ];
			$result = ( file_exists( $file ) or file_exists( $this->root . DIRECTORY_SEPARATOR . $file ) );
		}
		$result && $this->debug( 'Maintenance file present.' );
		return $result;
	}
	
	function execute() {
		try {
			date_default_timezone_set( @date_default_timezone_get( ) );
			$this->read_config( );
			!empty( $this->config[ 'TIME_ZONE' ] ) && date_default_timezone_set( $this->config[ 'TIME_ZONE' ] );
			$this->start_time = date( $this->config[ 'TIME_FORMAT' ] );
			$this->workers = new workers( $this->config[ 'WORKERS' ] );
			switch ( strtoupper( $this->parameters[ 1 ] ) ) {
				case 'NOTIFICATION':
					$event = new mars_event( $this );
					$event->execute( );
					break;
				case 'ROUTINE':
					$this->connect( );
					$this->update_status( );
					$routine = new mars_routine( $this, $this->parameters[ 2 ] );
					$routine->execute( false );
					break;
				case 'SCHEDULER':
					$this->connect( );
					$this->update_status( );
					$this->scheduler( );
					break;
				case 'TEST':
					$this->connect( );
					$this->test_installation( );
					break;
				default:
					throw new exception( sprintf( MARS_ERROR_UNHANDLED_PARAMETER, 
						$this->paramters[ 1 ] ) );
					break;
			}
		}
		catch ( exception $e ) {
			$this->log_error( sprintf( MARS_ERROR, $this->name, $e->getmessage( ) ) );
		}
	}

	function connect() {
		$this->cellserver = $this->get_cellserver( );
		list( $host, $port ) = explode( ':', sprintf( '%s:%s', $this->config[ 'MYSQL_HOST' ], MYSQL_PORT ) );
		empty( $port ) && $port = 3306;
		$username = $this->config[ 'MYSQL_USER' ];
		$pwd = $this->config[ 'MYSQL_PASSWORD' ];
		if ( !mod_mysql( ) ) {throw new exception( MYSQL_NO_SUPPORT );}
		if ( !mod_mysqli( ) ) {
			$this->database = new mysql_database( sprintf( '%s:%s', $host, $port ), $username, $pwd );
			$this->database->select_database( $this->config[ 'MYSQL_DB' ] );
		} else {
			$this->database = new mysqli_database( $host, $username, $pwd, $this->config[ 'MYSQL_DB' ], $port );
		}
		$sql = sprintf( "select id,down from config_cellservers " . 
			"where name='%s' and valid_until is null", $this->cellserver );
		$this->database->execute_query( $sql );
		if ( $this->database->row_count == 0 ) {throw new exception( 
			sprintf( MARS_ERROR_CELLSERVER_NOT_SET_UP, $this->cellserver ) );}
		$this->down = $this->database->rows[ 0 ][ 'down' ];
		$this->dp = $this->get_version( );
		$this->check_omnisv_services( );
		$this->mmd_local = stristr( $this->service[ 'MMD' ], MARS_MMD_LOCAL ) ? 0 : 1;
		while ( execute_actions( $this, 'UPDATESCRIPT' ) );
	}
	
	function get_duration() {
		return round( microtime( true ) - $this->micro_time, $this->duration_precision );
	}

	function add_config_item( $key, $default ) {
		$this->config[ $key ] = isset( $this->config[ $key ] ) ? 
			$this->config[ $key ] : $default;
	}

	function check_path( $path ) {
		if ( realpath( $path ) ) return realpath( $path );
		if ( realpath( $this->root . DIRECTORY_SEPARATOR . $path ) ) return realpath( $this->root . DIRECTORY_SEPARATOR . $path );
		if ( mkdir( $path, 0777, true ) ) return $path;
		return false;
	}
	
	function read_config() {
		$config_file = $this->root . DIRECTORY_SEPARATOR . MARS_CONFIG;
		if ( !file_exists( $config_file ) ) {
			throw new exception( sprintf( MARS_ERROR_CONFIG_MISSING, $config_file ) );
		}
		$this->config = array_change_key_case( parse_ini_file( $config_file ), CASE_UPPER );
		$this->config[ 'OMNI2ESL' ] = $this->check_path( $this->config[ 'OMNI2ESL' ] );
		$ito_file = explode( DIRECTORY_SEPARATOR, $this->config[ 'ITO_FILE' ] );
		$filename = array_pop( $ito_file );
		$this->config[ 'ITO_FILE' ] = $this->check_path( implode( DIRECTORY_SEPARATOR, $ito_file ) ) . DIRECTORY_SEPARATOR . $filename;
		$this->add_config_item( 'OMNI_BIN', 
			$this->config[ 'OMNI_HOME' ] . DIRECTORY_SEPARATOR . 'bin' );
		if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
			$this->add_config_item( 'OMNI_SBIN', 
			$this->config[ 'OMNI_HOME' ] . DIRECTORY_SEPARATOR . 'bin' );
		} else {
			$this->add_config_item( 'OMNI_SBIN', 
			$this->config[ 'OMNI_HOME' ] . DIRECTORY_SEPARATOR . 'sbin' );
		}
		$this->add_config_item( MARS_TIMEOUT_MULTIPLIER, 1 );
		$this->add_config_item( 'WORKERS', MARS_WORKERS );
	}

	function cache_command( $cmdline, $timeout = 0 ) {
		$main_service = MARS_DP7_MAIN_SERVICE;
		!isset( $this->service[ $main_service ] ) && $main_service = MARS_DP8_MAIN_SERVICE;
		!isset( $this->service[ $main_service ] ) && $main_service = '';
		if ( isset( $this->service[ $main_service ] ) 
			and !stristr( $this->service[ $main_service ], MARS_SERVICE_ACTIVE ) ) {
			throw new exception( sprintf( MARS_ERROR_SERVICE_NOT_RUNNING, $main_service ) );
		}
		$this->debug( sprintf( MARS_DEBUG_EXECUTION_STARTED, $cmdline ) );
		$exec = sprintf( MARS_EXEC, $cmdline );
		$errorlevel = 0;
		if ( $timeout == 0 ) {
			$starttime = round( microtime( true ), $this->duration_precision );
			$output = array();
			exec( $exec, $output, $errorlevel );
			$this->debug( sprintf( MARS_DEBUG_EXECUTION_FINISHED, round( microtime( true ) - $starttime, $this->duration_precision ) ) );
		} else {
			$timeout = $timeout * $this->config[ MARS_TIMEOUT_MULTIPLIER ];
			$descriptorspec = array( 
				0 => array( 
					"pipe", 
					"r" ), 
				1 => array( 
					"pipe", 
					"w" ), 
				2 => array( 
					"pipe", 
					"w" ) );
			$pipes = array();
			$starttime = round( microtime( true ), $this->duration_precision );
			$process = proc_open( $exec, $descriptorspec, $pipes );
			if ( !is_resource( $process ) ) {
				$this->debug( MARS_DEBUG_EXECUTION_NO_RESOURCE );
				throw new exception( sprintf( MARS_ERROR_EXEC, $cmdline ) );
			}
			$status = proc_get_status( $process );
			$output = '';
			do {
				$timeleft = $starttime + $timeout - round( microtime( true ), $this->duration_precision );
				$timeleft < 0 && $timeleft = 0;
				$read = array( 
					$pipes[ 1 ] );
				$write = $exceptions = NULL;	
				stream_select( $read, $write, $exeptions, $timeleft, NULL );
				if ( !empty( $read ) ) {
#					$starttime = time( );
					$output .= fread( $pipes[ 1 ], 8192 );
				}
			} while ( !feof( $pipes[ 1 ] ) && $timeleft > 0 );
			$status = proc_get_status( $process );
			$errorlevel = $status[ 'exitcode' ];
			$output = explode( PHP_EOL, trim( $output ) );
			fclose( $pipes[ 0 ] );
			fclose( $pipes[ 1 ] );
			if ( $timeleft <= 0 ) {
				proc_terminate( $process );
				$this->debug( sprintf( MARS_DEBUG_EXECUTION_TIMED_OUT, round( microtime( true ) - $starttime, $this->duration_precision ) ) );
				throw new exception( sprintf( MARS_ERROR_EXEC_TIMEOUT, $timeout, $cmdline ) );
			}
			proc_close( $process );
			$this->debug( sprintf( MARS_DEBUG_EXECUTION_FINISHED, round( microtime( true ) - $starttime, $this->duration_precision ) ) );
		}
#		if ( ( $errorlevel != 0 ) and ( $errorlevel != 2 ) and ( $errorlevel != 3 ) ) {
#			throw new exception( sprintf( MARS_ERROR_EXEC_ERRORLEVEL, $errorlevel, $cmdline, 
#				implode( PHP_EOL, $output ) ) );
#		}
		return $output;
	}

	function get_cellserver() {
		if ( isset( $this->config[ 'CELLSERVER' ] ) ) {
			$name = $this->config[ 'CELLSERVER' ];
		} else {
			$lines = $this->cache_command( sprintf( MARS_OMNIDBUTIL_SHOW_CELL_NAME_CMD, 
				$this->config[ 'OMNI_SBIN' ] ), MARS_OMNIDBUTIL_TIMEOUT );
			foreach ( $lines as $line ) {
				preg_match( sprintf( '/%s/s', MARS_OMNIDBUTIL_SHOW_CELL_NAME ), $line, $match ) && 
					list( $line, $name ) = $match;
			}
		}
		return $name;
	}

	function get_version() {
		$lines = $this->cache_command( sprintf( MARS_OMNISV_VERSION_CMD, 
			$this->config[ 'OMNI_SBIN' ] ), MARS_OMNISV_TIMEOUT );
		foreach ( $lines as $line ) {
			preg_match( sprintf( '/%s/s', MARS_OMNISV_VERSION ), $line, $match ) && 
				list( $line, $version, $build ) = $match;
		}
		$version = sprintf( "%s b%s", $version, $build );
		return $version;
	}

	function check_omnisv_services() {
		$lines = $this->cache_command( sprintf( MARS_OMNISV_STATUS_CMD, 
			$this->config[ 'OMNI_SBIN' ] ), MARS_OMNISV_TIMEOUT );
		foreach ( $lines as $line ) {
			foreach ( explode( ',', $this->config[ 'SERVICE_LIST' ] ) as $service ) {
				if ( stristr( $line, $service ) ) {
					list ( $dummy, $status ) = explode( ':', $line );
					$this->service[ $service ] = trim( $status );
				}
			}
		}
		if ( isset( $this->config[ 'SERVICE_PRIORITY' ] ) ) {
			foreach ( $this->service as $service => $status ) {
				if ( !$this->maintenance( ) and ( !stristr( $status, MARS_SERVICE_ACTIVE ) ) 
					and ( !stristr( $this->down, $service ) ) ) {
					$message = sprintf( MARS_ERROR_SERVICE_NOT_ACTIVE, $service, $status );
					$this->log_error( $message );
					$this->create_ticket( $this->config[ 'SERVICE_PRIORITY' ], 'SV1', $message );
				}
			}
		}
	}

	function update_status() {
		$active = $down = array( );
		if ( $this->maintenance( ) ) {
			$active = array( MARS_SERVICE_MAINTENANCE );
		} else {
			foreach ( $this->service as $service => $status ) {
				stristr( $status, MARS_SERVICE_ACTIVE ) && $active[] = $service;
				stristr( $status, MARS_SERVICE_DOWN ) && $down[] = $service;
			}
			empty( $down ) && $active = array( MARS_SERVICE_ALL ); 
			empty( $active ) && $down = array( MARS_SERVICE_ALL );
		}
		$sql = "update config_cellservers set timezone='%timezone',os='%os',dp='%dp',php='%php'," . 
			"maintenance='%maintenance',active=nullif('%active',''),down=nullif('%down','')," . 
			"mmd_local='%mmd_local' where name='%name';";
		$values = array( 
			'name' => $this->cellserver, 
			'timezone' => date_default_timezone_get( ), 
			'os' => $this->os, 
			'dp' => $this->dp, 
			'php' => $this->php, 
			'maintenance' => $this->maintenance( ) ? 1 : 0, 
			'active' => implode( ',', $active ), 
			'down' => implode( ',', $down ), 
			'mmd_local' => $this->mmd_local 
		 );
		$this->database->execute_query( $sql, $values );
	}

	function create_ticket( $priority = MARS_ITO_DEFAULT_PRIORITY, $text1 = '', $text2 = '', $text3 = '' ) {
		if ( isset( $this->config[ 'ITO_FILE' ] ) ) {
			$this->log_action( sprintf( MARS_ITO_MESSAGE, $priority, $text1 ) );
			$this->append_file( $this->config[ 'ITO_FILE' ], sprintf( MARS_ITO, $priority, $text1, $text2, $text3 ) );
		}
	}
	
	function read_file( $filename ) {
		$contents = file_get_contents( $filename );
		if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
			$enc = mb_detect_encoding( $contents );
			if ( substr( $contents, 0, 4 ) == UCS_4BE ) $enc = 'UCS-4BE';
			if ( substr( $contents, 0, 4 ) == UCS_4LE ) $enc ='UCS-4LE';
			if ( substr( $contents, 0, 3 ) == UTF_8B ) $enc = 'UTF-8';
			if ( substr( $contents, 0, 2 ) == UCS_2BE ) $enc = 'UCS-2BE';
			if ( substr( $contents, 0, 2 ) == UCS_2LE ) $enc = 'UCS-2LE';
			$result = ( $enc == '' ) ? false : iconv( $enc ,'ASCII//IGNORE', $contents );
		} else {
			$result = $contents;
		}
		return $result;
	}
	
	function append_file( $filename, $line, $trimnl = true ) {
		if ( $trimnl ) $line = preg_replace( '/[\r\n]+/', ' ', $line );
		$file = @fopen( $filename, 'a' );
		if ( !$file ) {
			throw new exception( sprintf( MARS_ERROR_CANNOT_OPEN, $filename ) );
		}
		if ( !fwrite( $file, $line . PHP_EOL ) ) {
			throw new exception( sprintf( MARS_ERROR_CANNOT_WRITE, $filename ) );
		}
		if ( !fclose( $file ) ) {
			throw new exception( sprintf( MARS_ERROR_CANNOT_CLOSE, $filename ) );
		}
	}

	function scheduler() {
		while ( execute_routines( $this ) or execute_notifications( $this ) );
		$silentwindow = false;
		if ( !empty( $this->config[ 'SILENT_WINDOW' ] ) ) {
			list ( $start, $end ) = explode( ' ', $this->config[ 'SILENT_WINDOW' ] );
			$time = strtotime( date( 'H:i' ) );
			$start = strtotime( $start );
			$end = strtotime( $end );
			$end < $start && $end = $end + 24 * 60 * 60;
			$silentwindow = ( $time >= $start ) and ( $time < $end );
		}
		if ( !$this->maintenance( ) and !$silentwindow ) {
			$lockfile = @fopen( $this->root . DIRECTORY_SEPARATOR . MARS_LOCKFILE, 'w' );
			if ( flock( $lockfile, LOCK_EX | LOCK_NB ) ) {
				$this->debug( 'SCHEDULER start' );
				$sql = "update mars_queue set executed_on=null where cellserver='%cellserver' and  executed_on is not null;";
				$values = array(
						'cellserver' => $this->cellserver
				);
				$this->database->execute_query( $sql, $values );
				$sql = "update config_cellservers set host='%host',`localtime`='%localtime' where name='%name';";
				$values = array(
					'name' => $this->cellserver,
					'localtime' => date( $this->config[ 'TIME_FORMAT' ] ),
					'host' => sprintf( '%s::%s', $this->hostname, count( $this->workers->workers ) ) );
				$this->database->execute_query( $sql, $values );
				$permitted = true;
				while ( ( $permitted and ( execute_actions( $this ) or $this->workers->waiting( ) ) ) or $this->workers->working( ) ) {
					if ( $this->workers->waiting( ) ) {
						$worker = $this->workers->start( );
						$this->debug( sprintf( MARS_DEBUG_WORKER_START, 
							$worker->id, count( $this->workers->workers ), $worker->cid, count( $this->workers->queue ), $worker->params[ 'CMD' ] ) );
					} else {
						if ( $this->workers->working( ) and $this->workers->done( ) ) {
							$worker = $this->workers->finish( );
							$this->debug( sprintf( MARS_DEBUG_WORKER_FINISH, 
								$worker->id, count( $this->workers->workers ), $worker->cid, count( $this->workers->queue ), $worker->duration ) );
							if ( !empty( $worker->params[ 'ITEMS' ] ) ) {
								$file = $this->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $worker->name . '.tmp';
								file_put_contents( $file, $this->workers->encode( $worker ) );
								$items = $worker->params[ 'ITEMS' ];
								execute_actions( $this, $items[ 'NAME' ], $items[ 'DATA' ] );
							}
						}
						$this->workers->next( );
					}
					( $this->maintenance( ) or $this->get_duration( ) > 3600 ) && $permitted = false;
				}
				$sql = "update config_cellservers set host='%host',`localtime`='%localtime' where name='%name';";
				$values = array(
					'name' => $this->cellserver,
					'localtime' => date( $this->config[ 'TIME_FORMAT' ] ),
					'host' => $this->hostname );
				$this->database->execute_query( $sql, $values );
				flock( $lockfile, LOCK_UN );
				fclose( $lockfile );
				unlink( $this->root . DIRECTORY_SEPARATOR . MARS_LOCKFILE );
				$this->debug( 'SCHEDULER finish' );
			}
		}
	}

	function test_file( $severity, $file ) {
		if ( $file == '' ) return true;
		$msg = file_exists( $file ) ? sprintf( MARS_TEST_FILE_FOUND, MARS_SEVERITY_OK, $file ) : 
			sprintf( MARS_TEST_FILE_NOT_FOUND, $severity, $file );
		$this->output( $msg );
	}

	function test_installation() {
		$this->output( $this->name );
		$this->output( sprintf( MARS_TEST, PHP_VERSION, $this->os ) );
		!mod_mysql( ) && $this->output( sprintf( MARS_TEST_MOD_NOT_SUPPORTED, MARS_SEVERITY_ERROR, 'MySQL' ) );
		!mod_mysqli( ) && $this->output( sprintf( MARS_TEST_MOD_NOT_SUPPORTED, MARS_SEVERITY_WARNING, 'MySQLi' ) );
		$this->test_file( MARS_SEVERITY_ERROR, $this->config[ 'OMNI_BIN' ] );
		$this->test_file( MARS_SEVERITY_ERROR, $this->config[ 'OMNI_SBIN' ] );
		$this->test_file( MARS_SEVERITY_ERROR, $this->config[ 'OMNI_SERVER' ] );
		$this->test_file( MARS_SEVERITY_ERROR, $this->config[ 'OMNI2ESL' ] );
		$this->test_file( MARS_SEVERITY_ERROR, $this->config[ 'BACKUPMON' ] );
		$this->test_file( MARS_SEVERITY_WARNING, $this->config[ 'ITO_FILE' ] );
		$this->output( sprintf( MARS_TEST_CELLSERVER, MARS_SEVERITY_OK, $this->cellserver ) );
		$lines = $this->cache_command( sprintf( MARS_OMNISTAT_PREVIOUS_CMD, 
			$this->config[ 'OMNI_BIN' ] ), MARS_OMNISTAT_TIMEOUT );
		$this->output( sprintf( MARS_TEST_OMNISTAT, MARS_SEVERITY_OK ) );
		$sessionid = 'N/A';
		$specification = 'N/A';
		$status = 'N/A';
		foreach ( $lines as $line ) {
			if ( preg_match( "/backup.*completed/i", $line ) ) {
				list ( $sessionid ) = explode( ' ', $line );
			}
		}
		if ( $sessionid != 'N/A' ) {
			$this->output( sprintf( MARS_TEST_SESSION, MARS_SEVERITY_OK, $sessionid ) );
			$lines = $this->cache_command( sprintf( MARS_OMNIRPT_SINGLE_SESSION_CMD, 
				$this->config[ 'OMNI_BIN' ], $sessionid ), MARS_OMNIRPT_TIMEOUT );
			$this->output( sprintf( MARS_TEST_OMNIRPT, MARS_SEVERITY_OK ) );
			foreach ( $lines as $line ) {
				if ( $line[ 0 ] == '#' ) {
					continue;
				}
				list ( $specification, $sessionid, $type, $sessionowner, $status, $mode, 
					$starttime, $starttime_t, $endtime, $endtime_t,	$queuing, $duration, 
					$gbwritten, $media, $errors, $warnings, $pendingda, $runningda, $failedda, 
					$completedda, $objects, $success ) = explode( "\t", rtrim( $line ) );
				break;
			}
			$this->output( sprintf( MARS_TEST_SESSION_DETAILS, 
				$sessionid, $specification, $status ) );
		} else {
			$this->output( sprintf( MARS_TEST_NO_SESSION, MARS_SEVERITY_WARNING ) );
		}
		if ( isset( $this->database ) and $this->database->is_connected( ) ) {
			$this->output( sprintf( MARS_TEST_MYSQL, MARS_SEVERITY_OK, 
				$this->config[ 'MYSQL_HOST' ] ) );
		} else {
			$this->output( sprintf( MARS_TEST_NO_MYSQL, MARS_SEVERITY_ERROR, 
				$this->config[ 'MYSQL_HOST' ], $this->database->message ) );
		}
	}
}
