<?php

/*
 * MARS 3.0 INDEX PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
date_default_timezone_set( @date_default_timezone_get( ) );
define( 'APPLICATION', 'MARS %s' );
define( 'COPYRIGHT', '&copy; 2015-' . date( 'Y' ) . ' Juraj Brabec, DXC Technology Company.' );
define( 'OLD_IE', '/(?i)msie [4-6]/' );
define( 'ERROR_OLD_IE', 'Unsupported IE version "%s" detected. Please install IE 8 or above.' );
define( 'INI_FILE', 'config.ini' );
define( 'BUILD_FILE', 'build' );
define( 'USERNAME', 'usr' );
define( 'PWD', 'pwd' );
define( 'USERNAME_DEFAULT', 'operator' );
define( 'PWD_DEFAULT', '' );
define( 'COOKIE_TIME', 24 * 60 * 60 );
define( 'COOKIE_PATH', '/' );
define( 'COOKIE_DOMAIN', (!empty($_SERVER['HTTP_HOST']) and $_SERVER['HTTP_HOST'] != 'localhost') ? '' : false );
define( 'REPORT_NAME', 'report_page' );
define( 'REPORT_TITLE', '%s - %s Report' );
define( 'ADMIN_TITLE', '%s - Administration Interface' );
define( 'SCHEDULER_PAGE', 'PAGE' );
define( 'SCHEDULER_SOURCE', 'SOURCE' );
define( 'SCHEDULER_CMD', 'CMD' );
define( 'ERROR_UNHANDLED_TYPE', "Unhandled type '%s'." );
define( 'UPGRADE_FILE', '..\\upgrade*.zip' );
define( 'UPGRADE_EXTRACT', 'Extracting file "%s".');
define( 'UPGRADE_EXECUTE', 'Executing file "%s".' );
define( 'UPGRADE_EXTRACT_ERROR', 'Error: Cannot extract file "%s".' );
define( 'UPGRADE_EXECUTE_ERROR', 'Error: File "%s" does not exist.' );
define( 'UPGRADE_CMD', '%s\\%s\\upgrade.cmd' );
define( 'UPGRADE_EXTRACT_CLEANUP', 'rd /s /q "%s\\%s"' );
define( 'EXEC', '%s 2>&1' );
define( 'ERROR_EXEC', 'Cannot start command "%s".' );
define( 'ERROR_EXEC_TIMEOUT', 'Timeout %s seconds in command "%s".' );
define( 'CMD_EXECUTING', 'Executing command "%s" (%s).' );
define( 'CMD_EXECUTED', 'Command "%s" (%s) was executed successful.' );
define( 'REPORT_SENT', 'Report "%s" was sent to %s successful.' );
define( 'ERROR_SENDING', 'Error sending report "%s" to %s.' );
define( 'EXCEPTION_ERROR' ,'Error' );
define( 'EXCEPTION_SEVERITY' ,'Severity' );
define( 'EXCEPTION_CODE' ,'Code' );
define( 'EXCEPTION_FILE' ,'File' );
define( 'EXCEPTION_LINE' ,'Line' );
define( 'EXCEPTION_TRACE' ,'Trace' );
define( 'FOOTER', '%s %s Prepared in <b>%s</b> second(s) on <b>%s (%s)</b>.' );

require_once 'inc\database.php';
require_once 'inc\page.php';

class application {
	var $name;
	var $region;
	var $database;
	var $config = array();
	var $microtime;
	var $start_time;
	var $params = array();

	function output( $message ) {
		print ( $message . PHP_EOL ) ;
	}

	function parse_decimal( $decimal ) {
		return str_replace( ',', $this->config[ 'DECIMAL' ], $decimal );
	}

	function parse_date( $date ) {
		$date = str_replace( '. ', '.', $date );
		if ( !strtotime( $date ) ) {
			return '';
		} else {
			return date( $this->config[ 'TIME_FORMAT' ], strtotime( $date ) );
		}
	}

	function application( $params = array( ) ) {
		$this->micro_time = microtime( true );
		$this->config = array_change_key_case( parse_ini_file( INI_FILE ), CASE_UPPER );
		!empty( $this->config[ 'TIME_ZONE' ] ) && date_default_timezone_set( $this->config[ 'TIME_ZONE' ] );
		$this->start_time = date( $this->config[ 'TIME_FORMAT' ] );
		if ( !empty( $this->config[ 'SMTP_SERVER' ] ) ) ini_set( 'SMTP', $this->config[ 'SMTP_SERVER' ] ); 
		if ( !empty( $this->config[ 'SMTP_PORT' ] ) ) ini_set( 'smtp_port', $this->config[ 'SMTP_PORT' ] ); 
		$build = file_get_contents( sprintf( '%s\%s', dirname( dirname( __DIR__ ) ), BUILD_FILE ) );
		$this->name = sprintf( APPLICATION, trim( $build ) );
		$this->database_connect( );
		$this->region = $this->get_config( 'region', APPLICATION );
		$this->params = $params;
	}

	function database_connect( ) {
		list( $host, $port ) = explode( ':', sprintf( '%s:%s', $this->config[ 'MYSQL_HOST' ], MYSQL_PORT ) );
		$username = empty( $_COOKIE[ USERNAME ] ) ? USERNAME_DEFAULT : $_COOKIE[ USERNAME ] ;
		$pwd = empty( $_COOKIE[ PWD ] ) ? PWD_DEFAULT : $_COOKIE[ PWD ] ;
		if ( !mod_mysql( ) ) {throw new exception( MYSQL_NO_SUPPORT );}
		if ( !mod_mysqli( ) ) {
			$this->database = new mysql_database( sprintf( '%s:%s', $host, $port ),	$username, $pwd );
			$this->database->select_database( $this->config[ 'MYSQL_DB' ] );
		} else {
			$this->database = new mysqli_database( $host, $username, $pwd, $this->config[ 'MYSQL_DB' ], $port );
		}
	}
	
	function get_duration() {
		return round( microtime( true ) - $this->micro_time, 2 );
	}
	
	function get_config( $name = '', $default = '' ) {
		$result = $default;
		$sql = sprintf( "select value from config_settings where name='%s';", $name );
		$this->database->execute_query( $sql ) && $result = $this->database->rows[ 0 ][ 'value' ];
		return $result;
	}
	
	function show_page( ) {
		$agent = empty( $_SERVER[ 'HTTP_USER_AGENT' ] ) ? '' : $_SERVER[ 'HTTP_USER_AGENT' ];
		if( preg_match( OLD_IE, $agent ) )
		{
			throw new exception( sprintf( ERROR_OLD_IE, $agent ) );
		}
		$source = empty( $this->params[ SOURCE ] ) ? array( ) : explode( '|', $this->params[ SOURCE ] );
		$mode = ( count( $source ) > 0 and $source[ 0 ] == ADMIN ) ? ADMIN : INTERACTIVE; 
		$title = $this->region;
		if ( $mode == ADMIN ) {
			$title = sprintf( ADMIN_TITLE, $this->region );
			array_shift( $source );
			if ( count( $source ) == 0 ) 
				$source = array( REPORTS_ADMIN );
			$this->params[ SOURCE ] = implode( '|', $source );
		} else {
			$title = sprintf( REPORT_TITLE, $this->region, '%TITLE' );
			if ( empty( $this->params[ SOURCE ] ) and empty( $this->params[ PAGE ] ) ) {
				setcookie( USERNAME, '', 0, COOKIE_PATH, COOKIE_DOMAIN );
				setcookie( PWD, '', 0, COOKIE_PATH, COOKIE_DOMAIN );
				$this->params[ SOURCE ] = implode( '|', unserialize( REPORTS_DEFAULT ) );
			}
		}
		$page = new page( $this, REPORT_NAME, $title, $mode );
		if ( !empty( $this->params[ SOURCE ] ) ) {
			$this->params[ PAGE ] = '';
			foreach ( explode( '|', $this->params[ SOURCE ] ) as $name ) {
				$page->add_report( array( NAME => $name ) );
			}
		}
		$page->parse_url( );
		$page->output( );
	}
	
	function execute_upgrade( ) {
		$files = glob( UPGRADE_FILE );
		foreach( $files as $file ) {
			$path = pathinfo( realpath( $file ), PATHINFO_DIRNAME );
			$name = pathinfo( realpath( $file ), PATHINFO_FILENAME );
			$this->output( sprintf( UPGRADE_EXTRACT, $name ) );
			$zip = new ZipArchive;
			$res = $zip->open( $file );
			if ( $res === TRUE ) {
				$zip->extractTo( $path );
				$zip->close( );
				$cmd = sprintf( UPGRADE_CMD, $path, $name );
				if( file_exists( $cmd ) ) {
					$this->output( sprintf( UPGRADE_EXECUTE, $cmd ) );
					$result = exec( $cmd, $output, $errorlevel );
					$this->output( 'R: ' . ( $result ? '1' : '0' ) . ' E: ' . $errorlevel );
					$this->output( "\t" . implode( PHP_EOL . "\t", $output ) );
					$errorlevel == 0 && unlink( $file );
				} else {
					$this->output( sprintf( UPGRADE_EXECUTE_ERROR, $cmd ) );
				}
				exec( sprintf( UPGRADE_EXTRACT_CLEANUP, $path, $name ) );
			} else {
				$this->output( sprintf( UPGRADE_EXTRACT_ERROR, $file ) );
			}			
		}
	}
	
	function execute_scheduler( ) {
		$this->execute_upgrade( );
		$rows = array();
		$sql = sprintf( "select * from _scheduler where type in ('%s','%s','%s');", SCHEDULER_SOURCE, SCHEDULER_PAGE, SCHEDULER_CMD);
		$this->database->execute_query( $sql ) && $rows = $this->database->rows;
		foreach ( $rows as $row ) {
			try {
				if ( $row[ 'type' ] == SCHEDULER_CMD ) {
					$this->output( sprintf( CMD_EXECUTING, $row[ 'name' ], $row[ 'param1' ] ) );
					$errorlevel = 0;
					$output = array();
					$result = exec( sprintf( EXEC, $row[ 'param1' ] ), $output, $errorlevel );
					$this->output( 'R: ' . ( $result ? '1' : '0' ) . ' E: ' . $errorlevel );
					$this->output( "\t" . implode( PHP_EOL . "\t", $output ) );
					$this->output( sprintf( CMD_EXECUTED, $row[ 'name' ], $row[ 'param1' ] ) );
				} else {
					$name = strtolower( str_replace( ' ', '_', $row[ 'name' ] ) );
					$title = sprintf( REPORT_TITLE, $this->region, $row[ 'name' ] );
					switch ( $row[ 'param6' ] ) {
						case 'CSV': $mode =  CSV; break;
						case 'HTML': $mode = HTML; break;
						default: $mode =  HTML; break;
					}
					$page = new page( $this, $name, $title, $mode );
					switch ( $row[ 'type' ] ) {
						case SCHEDULER_SOURCE:
							foreach ( explode( '|', $row[ 'param1' ] ) as $name ) {
								$page->add_report( array(
									NAME => $name ) );
							}
							break;
						case SCHEDULER_PAGE:
							$_REQUEST[ PAGE ] = $row[ 'param1' ];
							break;
						default:
							throw new exception( sprintf( ERROR_UNHANDLED_TYPE, $row[ 'type' ] ) );
							break;
					}
					$page->parse_url( );
					!empty( $row[ 'param2' ] ) && $row[ 'param2' ] != TIMEPERIOD_DEFAULT && $page->params[ TIMEPERIOD ] = explode( '::', $row[ 'param2' ] );
					!empty( $row[ 'param3' ] ) && $row[ 'param3' ] != CUSTOMER_DEFAULT && $page->params[ CUSTOMER ] = $row[ 'param3' ];
					!empty( $row[ 'param4' ] ) && $page->params[ MAIL_TO ] = $row[ 'param4' ];
					!empty( $row[ 'param5' ] ) && $page->params[ MAIL_CC ] = $row[ 'param5' ];
					$page->params[ MODE ] = $mode;
					if ( $page->output( ) ) {
						$result = true;
						$this->output( sprintf( REPORT_SENT, $row[ 'name' ], $row[ 'param4' ] ) );
					} else {
						$result = false;
						$this->output( sprintf( ERROR_SENDING, $row[ 'name' ], $row[ 'param4' ] ) );
					}
				}
				if ( $result ) {
					$sql = sprintf( "update config_scheduler set updated_on='%s' where id='%s';", $this->start_time, $row[ 'id' ] );
					$this->database->execute_query( $sql );
				}
			}  catch ( ErrorException $e ) {
				$line = '%s: %s' . PHP_EOL;
				echo sprintf( $line, EXCEPTION_ERROR, $e->getmessage( ) );
				echo sprintf( $line, EXCEPTION_SEVERITY, $e->getseverity( ) );
				echo sprintf( $line, EXCEPTION_CODE, $e->getcode( ) );
				echo sprintf( $line, EXCEPTION_FILE, $e->getfile( ) );
				echo sprintf( $line, EXCEPTION_LINE, $e->getline( ) );
				$line = '%s: ' . PHP_EOL . '%s' . PHP_EOL;
				echo sprintf( $line, EXCEPTION_TRACE, $e->gettraceasstring( ) );
			} catch ( Exception $e ) {
				echo $e->getmessage( );
			}
		}
	}
	
	function close() {
		if ( PHP_SAPI === 'cli' ) return;
		$duration = $application->get_duration( );
		$this->output( '</br>' );
		$this->output( '<div class="footer">' );
		$this->output( sprintf( FOOTER, $application->name, COPYRIGHT, $duration, date( 'F j, Y, H:i' ), date_default_timezone_get( ) ) );
		$this->output( '</div>' );
		$this->output( '</br>' );
		$this->output( '</body>' );
		$this->output( '</html>' );
	}
}

function error_handler( $errno, $errstr, $errfile, $errline, array $errcontext ) {
	if ( ( 0 === error_reporting( ) ) or ( $errno == 8192 ) ) {return false;}
	throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
}

set_error_handler( 'error_handler' );
try {
	$params =  $_REQUEST;
	PHP_SAPI === 'cli' && parse_str( implode( '&', array_slice( $argv, 1 ) ), $params );
	$application = new application( $params );
	$source = empty( $application->params[ SOURCE ] ) ? '' : $application->params[ SOURCE ];
	switch ( $source ) {
		case SCHEDULER: $application->execute_scheduler();break;
		case ADMIN: 
		default: $application->show_page();break;  
	} 
} catch ( ErrorException $e ) {
	if ( PHP_SAPI === 'cli' ) {
		$line = '%s: %s' . PHP_EOL;
	} else {
		$line = '<b>%s</b>: %s</br>' . PHP_EOL;
		echo '<div class="error">' . PHP_EOL;
	}
	echo sprintf( $line, EXCEPTION_ERROR, $e->getmessage( ) );
	echo sprintf( $line, EXCEPTION_SEVERITY, $e->getseverity( ) );
	echo sprintf( $line, EXCEPTION_CODE, $e->getcode( ) );
	echo sprintf( $line, EXCEPTION_FILE, $e->getfile( ) );
	echo sprintf( $line, EXCEPTION_LINE, $e->getline( ) );
	if ( PHP_SAPI === 'cli' ) {
		$line = '%s: ' . PHP_EOL . '%s' . PHP_EOL;
		echo sprintf( $line, EXCEPTION_TRACE, $e->gettraceasstring( ) );
	} else {
		$line = '<b>%s</b>: </br><code>%s</code></br>' . PHP_EOL;
		echo sprintf( $line, EXCEPTION_TRACE, $e->gettraceasstring( ) );
		echo '</div>' . PHP_EOL;
	}
}
catch ( Exception $e ) {
	if ( PHP_SAPI === 'cli' ) {
		echo $e->getmessage( ) . PHP_EOL;
	} else {
		setcookie( USERNAME, '', 0, COOKIE_PATH, COOKIE_DOMAIN );
		setcookie( PWD, '', 0, COOKIE_PATH, COOKIE_DOMAIN );
		echo '<div class="error">' . PHP_EOL;
		echo $e->getmessage( ) . PHP_EOL;
		echo '</br>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
	}
}
$application->close();
