<?php

/*
 * MARS 3.0 PAGE PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'SOURCE', 's' );
define( 'SCHEDULER', 'scheduler' );
define( 'ROUTINE', 'routine' );
define( 'DAILY_ROUTINE', 'Daily routine' );
define( 'NORMAL_ROUTINE', 'Normal routine' );
define( 'MAINTENANCE', 'Maintenance' );
define( 'CELLSERVER', 'cell' );
define( 'SID', 'sid' );
define( 'DELETE_SID', 'del_sid' );
define( 'SQL', 'sql' );
define( 'EXECUTE', 'exec' );
define( 'FILE', 'file' );
define( 'UPLOAD', 'up' );
define( 'PAGE', 'p' );
define( 'REPORTS', 'r' );
define( 'REPORTS_DEFAULT', serialize( array( 
		'ods', 
		'ocs', 
		'oc' ) ) );
define( 'REPORTS_ADMIN', 'ods' );
define( 'REPORTS_SIMPLE', serialize( array( 
		'ods', 
		'ocs', 
		'oc' ) ) );
define( 'MODE', 'm' );
define( 'MODE_DEFAULT', 'i' );
define( 'INTERACTIVE', 'i' );
define( 'HTML', 'e' );
define( 'CSV', 'x' );
define( 'ADMIN', 'admin' );
define( 'TIMEPERIOD', 't' );
define( 'TIMEPERIOD_DEFAULT', 'W::W+1' );
define( 'CUSTOMER', 'cn' );
define( 'CUSTOMER_DEFAULT', '*All customers' );
define( 'MAIL_TO', 'to' );
define( 'MAIL_CC', 'cc' );
define( 'MAIL_TO_DEFAULT', '' );
define( 'MAIL_CC_DEFAULT', '' );
define( 'MAX_HTML_INLINE_SIZE_MB', 2 );
define( 'MAX_HTML_ATTACHMENT_SIZE_MB', 2 );
define( 'SCHEDULE', 's' );
define( 'SCHEDULE_NAME', 'sn' );
define( 'SCHEDULE_DATE', 'sd' );
define( 'SCHEDULE_TIME', 'st' );

require_once 'report.php';

class page {
	var $application;
	var $id;
	var $name;
	var $title;
	var $timeperiod;
	var $customer;
	var $message;
	var $html = array();
	var $params = array();
	var $reports = array();
	var $cellservers = array();
	var $customers = array();
	var $timeperiods = array();
	var $styles = array();
	
	function page( $application, $name, $title, $mode = MODE_DEFAULT ) {
		$this->application = $application;
		$this->id = '';
		$this->name = $name;
		$this->title = $title;
		$this->timeperiod = 0;
		$this->customer = 0;
		$this->message = '';
		$this->params[ MODE ] = $mode;
		$this->params[ TIMEPERIOD ] = explode( '::', empty( $_COOKIE[ TIMEPERIOD ] ) ? TIMEPERIOD_DEFAULT : $_COOKIE[ TIMEPERIOD ] );
		$this->params[ CUSTOMER ] = empty( $_COOKIE[ CUSTOMER ] ) ? CUSTOMER_DEFAULT : $_COOKIE[ CUSTOMER ];
		$this->params[ MAIL_TO ] = MAIL_TO_DEFAULT;
		$this->params[ MAIL_CC ] = MAIL_CC_DEFAULT;
		$files = glob( sprintf( '%s/mars*', sys_get_temp_dir( ) ) );
		$time = time( ) - 5 * 60;
		foreach ( $files as $file )
			is_file( $file ) && ( filemtime( $file ) < $time ) && unlink( $file );
	}

	function add_report( $params = array( ) ) {
		if ( empty( $params[ NAME ] ) ) return false;
		$report = $this->get_report( $params[ NAME ] );
		if ( !$report ) {
			$report = new report( $this, $params[ NAME ] );
			$this->reports[ ] = $report;
		}
		$report->set_parameters( $params );
		$report->timeperiod && $this->timeperiod = 1;
		$report->customer && $this->customer = 1;
		return $report;
	}

	function get_report( $name ) {
		$result = false;
		foreach ( $this->reports as $report ) {
			if ( $report->name == $name ) $result = $report;
		}
		return $result;
	}

	function encode( $value ) {
		$result = '';
		count( $value[ REPORTS ] ) > 0 && $result = urlencode( base64_encode( json_encode( $value ) ) );
		return $result;
	}

	function decode( $value ) {
		$result = array();
		!empty( $value ) && $result = json_decode( base64_decode( $value ), true );
		return $result;
	}

	function parse_url() {
		!empty( $_REQUEST[ PAGE ] ) && $this->id = $_REQUEST[ PAGE ];
		$page = $this->decode( $this->id );
		$reports = array();
		!empty( $page[ REPORTS ] ) && $reports = $page[ REPORTS ];
		foreach ( $reports as $report ) {
			$params = $report;
			!empty( $page[ REQUERY ] ) && $params[ REQUERY ] == 1;
			if ( empty( $params[ LINK ] ) ) {
				$this->add_report( $params );
			} else {
				!empty( $params[ HIGHLIGHT ] ) && $this->add_report( $params );
				$linked = array();
				$linked[ NAME ] = $params[ LINK ];
				$params[ LINK_CONDITIONS ] != 'NONE' && $linked[ CONDITIONS ] = $params[ LINK_CONDITIONS ];
				$linked[ REQUERY ] = 1;
				$this->add_report( $linked );
				break;
			}
		}
		!empty( $page[ MODE ] ) && $this->params[ MODE ] = $page[ MODE ];
		!empty( $page[ TIMEPERIOD ] ) && $this->params[ TIMEPERIOD ] = explode( '::', $page[ TIMEPERIOD ] );
		!empty( $page[ CUSTOMER ] ) && $this->params[ CUSTOMER ] = $page[ CUSTOMER ];
		!empty( $page[ MAIL_TO ] ) && $this->params[ MAIL_TO ] = $page[ MAIL_TO ];
		!empty( $page[ MAIL_CC ] ) && $this->params[ MAIL_CC ] = $page[ MAIL_CC ];
		$this->params[ MODE ] == ADMIN && $this->title = sprintf( ADMIN_TITLE, $this->application->region );
		if ( isset( $_REQUEST[ ACTION ] ) ) {
			$params = array( 
					REQUERY => 0 );
			if ( isset( $_REQUEST[ REPORT ] ) ) {
				if ( $this->params[ MODE ] == ADMIN ) {
					$action = $_POST[ ACTION ];
					$name = $_POST[ REPORT ];
					$fields = $_POST;
					unset( $fields[ ACTION ] );
					unset( $fields[ REPORT ] );
					$sql = '';
					switch( $action ) {
						case ADD: 
							$sql=sprintf( "insert into config_%s () values ();", $name ); 
							break;
						case MODIFY:
							$values=array( );
							foreach( $fields as $key=>$value ) $key != ID && $values[]=sprintf("%s=nullif('%s','')", $key, $value );
							$sql=sprintf( "update config_%s set %s,valid_until=null where id=%s;", $name, implode(',',$values), $fields[ ID ] ); 
							unset( $this->get_report( $name )->params[ ID ] );
							break;
						case REMOVE: 
							$sql=sprintf( "update config_%s set valid_until=now() where id=%s;", $name, $fields[ ID ] ); 
							unset( $this->get_report( $name )->params[ ID ] );
							break;
						default: $sql='';
					}
					if ( $sql != '' ) try {
						$this->application->database->execute_query( $sql );
						$this->get_report( $name )->params[ ID ] = $this->application->database->insert_id;
					} catch ( Exception $e ) {
						$this->message = $e->getmessage( );
						return;
					}
					
				} else {
					list ( $action, $index ) = explode( '_', $_REQUEST[ ACTION ] );
					$name = $_POST[ REPORT ];
					$field = $_POST[ sprintf( '%s_%s', FIELD, $index ) ];
					$operator = urldecode( $_POST[ sprintf( '%s_%s', OPERATOR, $index ) ] );
					$value = $_POST[ sprintf( '%s_%s', VALUE, $index ) ];
					$params[ NAME ] = $this->get_report( $name )->name;
					$params[ REQUERY ] = 1;
					if ( $action == ADD ) {
						if ( $index > 0 ) {
							unset( $this->get_report( $name )->params[ CONDITIONS ][ $index - 1 ] );
						}
						$condition = sprintf( '%s %s \'%s\'', $field, $operator, $value );
						$params[ CONDITIONS ] = $condition;
					}
					if ( $action == REMOVE ) {
						unset( $this->get_report( $name )->params[ CONDITIONS ][ $index - 1 ] );
					}
				}
			}
			if ( $_REQUEST[ ACTION ] == TIMEPERIOD and !empty( $_REQUEST[ TIMEPERIOD ] ) and implode( '::', $this->params[ TIMEPERIOD ] ) != $_REQUEST[ TIMEPERIOD ] ) {
				$params[ TIMEPERIOD ] = explode( '::', $_REQUEST[ TIMEPERIOD ] );
				$params[ REQUERY ] = 1;
				$result = setcookie( TIMEPERIOD, $_REQUEST[ TIMEPERIOD ], time( ) + COOKIE_TIME, COOKIE_PATH, COOKIE_DOMAIN );
			}
			if ( $_REQUEST[ ACTION ] == TIMEPERIOD and !empty( $_REQUEST[ CUSTOMER ] ) and $this->params[ CUSTOMER ] != $_REQUEST[ CUSTOMER ] ) {
				$params[ CUSTOMER ] = $_REQUEST[ CUSTOMER ];
				$params[ REQUERY ] = 1;
				$result = setcookie( CUSTOMER, $_REQUEST[ CUSTOMER ], time( ) + COOKIE_TIME, COOKIE_PATH, COOKIE_DOMAIN );
			}
			if ( $_REQUEST[ ACTION ] == SEND ) {
				$result = setcookie( MAIL_TO, $_REQUEST[ MAIL_TO ], time( ) + COOKIE_TIME, COOKIE_PATH, COOKIE_DOMAIN );
				$result = setcookie( MAIL_CC, $_REQUEST[ MAIL_CC ], time( ) + COOKIE_TIME, COOKIE_PATH, COOKIE_DOMAIN );
				if ( empty( $_REQUEST[ MAIL_TO ] ) ) { 
					$this->message = 'Please specify at least one recipient\'s address.';
					return;
				}
				$params[ REQUERY ] = 1;
				!empty( $_REQUEST[ TIMEPERIOD ] ) && $this->params[ TIMEPERIOD ] = explode( '::', $_REQUEST[ TIMEPERIOD ] );
				!empty( $_REQUEST[ CUSTOMER ] ) && $this->params[ CUSTOMER ] = $_REQUEST[ CUSTOMER ];
				!empty( $_REQUEST[ MODE ] ) && $this->params[ MODE ] = $_REQUEST[ MODE ];
				!empty( $_REQUEST[ MAIL_TO ] ) && $this->params[ MAIL_TO ] = $_REQUEST[ MAIL_TO ];
				!empty( $_REQUEST[ MAIL_CC ] ) && $this->params[ MAIL_CC ] = $_REQUEST[ MAIL_CC ];
			}
			if ( $_REQUEST[ ACTION ] == LOGIN ) {
				if ( !setcookie( USERNAME, $_REQUEST[ USERNAME ], 0, COOKIE_PATH, COOKIE_DOMAIN ) or !setcookie( PWD, $_REQUEST[ PWD ], 0, COOKIE_PATH, COOKIE_DOMAIN ) ) {
					$this->message = sprintf( 'Cookie: %s %s %s not set.', $_REQUEST[ USERNAME ], COOKIE_PATH, COOKIE_DOMAIN );
					return;
				}
			}
			try {
				if ( $_REQUEST[ ACTION ] == SCHEDULE ) {
					$result = setcookie( MAIL_TO, $_REQUEST[ 'S'.MAIL_TO ], time( ) + COOKIE_TIME, COOKIE_PATH, COOKIE_DOMAIN );
					$result = setcookie( MAIL_CC, $_REQUEST[ 'S'.MAIL_CC ], time( ) + COOKIE_TIME, COOKIE_PATH, COOKIE_DOMAIN );
					if ( empty( $_REQUEST[ SCHEDULE_NAME ] ) ) { 
						$this->message = 'Please enter a schedule name, it cannot be blank.';
						return;
					}
					if ( empty( $_REQUEST[ 'S'.MAIL_TO ] ) ) { 
						$this->message = 'Please specify at least one recipient\'s address.';
						return;
					}
					$name = $_REQUEST[ SCHEDULE_NAME ];
					strlen( $name ) > 64 && $name = substr( $name, 0, 64 );
					$timeperiod = implode( '::', $this->params[ TIMEPERIOD ] );
					$timeperiod = ( $timeperiod == TIMEPERIOD_DEFAULT ? '' : $timeperiod );
					$customer = $this->params[ CUSTOMER ];
					$customer = ( $customer == CUSTOMER_DEFAULT ? '' : $customer );
					$mode = $this->params[ MODE ] == HTML ? 'HTML' : 'CSV';
					$sql = 'INSERT INTO config_scheduler (date,time,name,type,param1,param2,param3,param4,param5,param6) VALUES ';
					$sql .= sprintf( "(nullif('%s',''),'%s','%s','PAGE','%s',nullif('%s',''),nullif('%s',''),'%s',nullif('%s',''),'%s')", 
						$_REQUEST[ SCHEDULE_DATE ], $_REQUEST[ SCHEDULE_TIME ], $name, $this->id, 
						$timeperiod, $customer, $_REQUEST[ 'S'.MAIL_TO ], $_REQUEST[ 'S'.MAIL_CC ], $mode );
					$sql .= ' ON DUPLICATE KEY UPDATE ';
					$sql .= sprintf( "date=nullif('%s',''),time='%s',type='PAGE',param1='%s',param2=nullif('%s',''),param3=nullif('%s',''),param4='%s',param5=nullif('%s',''),param6='%s';", 
						$_REQUEST[ SCHEDULE_DATE ], $_REQUEST[ SCHEDULE_TIME ], $this->id, $timeperiod, $customer, $_REQUEST[ 'S'.MAIL_TO ], $_REQUEST[ 'S'.MAIL_CC ], $mode );
					$this->application->database->execute_query( $sql );
					$this->message = 'Report was scheduled.';
					$this->params[ MODE ] = MODE_DEFAULT;
					return;
				}
				if ( $_REQUEST[ ACTION ] == ROUTINE ) {
					switch( $_REQUEST[ ROUTINE ] ) {
						case NORMAL_ROUTINE: $action = 'ROUTINE'; $data = ''; break;					
						case DAILY_ROUTINE: $action = 'ROUTINE'; $data = 'DAILY'; break;					
						case MAINTENANCE: $action = 'MAINTENANCE'; $data = ''; break;
						default: $action = ''; $data = ''; break;
					}
					if ( $action != '' ) {
						$sql = 'ALTER EVENT event_routine ENABLE;';
						$this->application->database->execute_query( $sql );
						$sql = sprintf( "call procedure_queue('localhost','%s','%s');", $action, $data );
						$this->application->database->execute_query( $sql );
						$this->message = sprintf( '%s was queued.', $_REQUEST[ ROUTINE ] );
					}
					return;
				}
				if ( $_REQUEST[ ACTION ] == DELETE_SID ) {
					$sessions = array_map('trim', explode( ',', $_REQUEST [ SID ] ) );
					$cellserver = $_REQUEST[ CELLSERVER ];
					$sql = sprintf( "delete from dataprotector_sessions where cellserver='%s' and sessionid in('%s');", trim( $cellserver ), implode("','", $sessions ) );
					$this->application->database->execute_query( $sql );
					$this->message = sprintf( '%s sessions deleted.', $this->application->database->row_count );
					return;
				}
				if ( $_REQUEST[ ACTION ] == EXECUTE ) {
					$sql = $_REQUEST[ SQL ];
					$this->application->database->execute_query( $sql );
					$this->message = sprintf( '%s records affected.', $this->application->database->row_count );
					return;
				}
				if ( $_REQUEST[ ACTION ] == UPLOAD ) {
					$sql = $_REQUEST[ SQL ];
					$file = basename( $_FILES[ FILE ][ 'name' ] );
					if ( empty( $file ) ) 
						throw new exception( 'No file selected.' );
					$ext = pathinfo( $file, PATHINFO_EXTENSION );
					if ( $ext != 'zip' ) 
						throw new exception( sprintf( 'File "%s" is not a valid MARS upgrade package.', $file ) ); 
					if ( !move_uploaded_file( $_FILES[  FILE ][ 'tmp_name' ], sprintf( '..\\..\\%s', $file ) ) ) 
						throw new exception( sprintf( 'Error uploading package "%s".', $file ) );
					$this->message = sprintf( 'Package "%s" has been uploaded.', $file );
					return;
				}
			} catch ( Exception $e ) {
				$this->message = $e->getmessage( );
				return;
			}
			header( sprintf( 'Location: %s', $this->get_url( $params ) ) );
			die( );
		}
		if ( !empty( $_REQUEST[ SOURCE ] ) ) {
			header( sprintf( 'Location: %s', $this->get_url( ) ) );
			die( );
		}
	}

	function get_url( $new = array( ) ) {
		$params = $this->params;
		$params = array_merge( $params, $new );
		
		$url = array();
		$reports = array();
		foreach ( $this->reports as $report ) {
			if ( $report->simple or ( !empty( $params[ NAME ] ) and $report->name == $params[ NAME ] ) ) {
				empty( $params[ REMOVE ] ) && $reports[ ] = $report->get_parameters( $params );
			} else {
				$blank = array( );
				if ( ( $params[ MODE ] == ADMIN ) or !empty( $params[ REQUERY ] ) ) {
					$blank[ REQUERY ] = 1;
				}
				$reports[ ] = $report->get_parameters( $blank );
			}
		}
		if ( !empty( $new[ NAME ] ) and !$this->get_report( $new[ NAME ] ) ) {
			$reports[ ] = array( 
					NAME => $new[ NAME ] );
		}
		$url[ REPORTS ] = $reports;
		
		$params[ MODE ] != MODE_DEFAULT && $url[ MODE ] = $params[ MODE ];
		$this->timeperiod && implode( '::', $params[ TIMEPERIOD ] ) != TIMEPERIOD_DEFAULT && $url[ TIMEPERIOD ] = implode( '::', $params[ TIMEPERIOD ] );
		$this->customer && $params[ CUSTOMER ] != CUSTOMER_DEFAULT && $url[ CUSTOMER ] = $params[ CUSTOMER ];
		$params[ MAIL_TO ] != MAIL_TO_DEFAULT && $url[ MAIL_TO ] = $params[ MAIL_TO ];
		$params[ MAIL_CC ] != MAIL_CC_DEFAULT && $url[ MAIL_CC ] = $params[ MAIL_CC ];
		$url = $this->encode( $url );
		!empty( $url ) && $url = sprintf( '?%s=%s', PAGE, $url );
		return sprintf( '%s%s', $_SERVER[ 'PHP_SELF' ], $url );
	}

	function get_datetime( $timeperiod ) {
		preg_match( '/(?P<key>[HDWMNY])(?P<sign>[+-]*)(?P<value>\d*)/', $timeperiod, $result );
		$result[ 'sign' ] == '' && $result[ 'sign' ] = '+';
		$result[ 'value' ] == '' && $result[ 'value' ] = '0';
		switch ( $result[ 'key' ] ) {
			case 'H':
				$key = 'hour';
				$start = sprintf( 'Today %s hours', date( 'H' ) );
				break;
			case 'D':
				$key = 'day';
				$start = 'Today midnight';
				break;
			case 'W':
				$key = 'week';
#	PHP BUG FOR SUNDAYS	RETURNS NEXT MONDAY	$start = 'Monday this week midnight';
				$start = date( 'w' ) == 1 ? 'Today midnight' : 'Last Monday midnight';
				break;
			case 'N':
				$key = 'month';
				$start = sprintf( '+14 day %s', date( 'Y-m-d H:i:s', strtotime( 'first day of this month midnight' ) ) );
				break;
			case 'Y':
				$key = 'year';
				$start = 'First day of january midnight';
				break;
			case 'M':
			default:
				$key = 'month';
				$start = 'First day of this month midnight';
				break;
		}
		return strtotime( sprintf( '%s%s %s', $result[ 'sign' ], $result[ 'value' ], $key ), strtotime( $start ) );
	}

	function output_toolbar() {
		$html = array();
		$menu = array();
		if ( $this->params[ MODE ] == ADMIN ) {
			$html[ ] = '<div class="toolbaradmin">';
			$menu[ 'Cell Servers' ] = sprintf( '%s?%s=%s|%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN, 'cellservers' );
			$menu[ 'Media Servers' ] = sprintf( '%s?%s=%s|%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN, 'mediaservers' );
			$menu[ 'Customers' ] = sprintf( '%s?%s=%s|%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN, 'customers' );
			$menu[ 'Retentions' ] = sprintf( '%s?%s=%s|%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN, 'retentions' );
			$menu[ 'Scheduler' ] = sprintf( '%s?%s=%s|%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN, 'scheduler' );
			$menu[ 'Time periods' ] = sprintf( '%s?%s=%s|%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN, 'timeperiods' );
			$menu[ 'Settings' ] = sprintf( '%s?%s=%s|%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN, 'settings' );
		} else {
			$html[ ] = '<div class="toolbar">';
			$menu[ 'Reload' ] = $this->get_url( array( 
				REQUERY => 1 ) );
			$sql = 'select submenu,title,name from config_reports where submenu is not null order by sort;';
			$this->application->database->execute_query( $sql );
			foreach ( $this->application->database->rows as $row ) {
				$menu[ $row[ 'submenu' ] ][ ] = array( 
						$row[ 'title' ], 
						sprintf( '%s?%s=%s', $_SERVER[ 'PHP_SELF' ], SOURCE, $row[ 'name' ] ) );
			}
			$menu[ 'Send' ][ ] = array( 
					'HTML', 
					$this->get_url( array( 
							MODE => HTML ) ) );
			$menu[ 'Send' ][ ] = array( 
					'CSV', 
					$this->get_url( array( 
							MODE => CSV ) ) );
		}
		$menu[ 'Admin' ] = sprintf( '%s?%s=%s', $_SERVER[ 'PHP_SELF' ], SOURCE, ADMIN );
#		$menu[ 'Back' ] = 'javascript:history.go(-1);';
#		$menu[ 'Help' ] = '#';
		$html[ ] = '<form method="post" action="">';
		$html[ ] = sprintf( '<input type="hidden" name="%s" value="%s"">', ACTION, TIMEPERIOD );
		$html[ ] = '<table class="toolbar"><tr>';
		$html[ ] = '<td class="toolbar">';
		$html[ ] = sprintf( '<a href="%s"><img alt="MARS logo" height="28" align="center" border="0" src="inc/mars.png"></a>', $_SERVER[ 'PHP_SELF' ] );
		$html[ ] = '</td>';
		if ( $this->timeperiod ) {
			$html[ ] = '<td class="toolbar">';
			$html[ ] = sprintf( '<select name="%s" onchange="this.form.submit()">', TIMEPERIOD );
			$sql = 'select name,value from config_timeperiods where valid_until is null order by ord;';
			$this->application->database->execute_query( $sql );
			foreach ( $this->application->database->rows as $row ) {
				$selected = implode( '::', $this->params[ TIMEPERIOD ] ) == $row[ 'value' ] ? ' selected' : '';
				$html[ ] = sprintf( '<option value="%s"%s>%s</option>', $row[ 'value' ], $selected, $row[ 'name' ] );
			}
			$html[ ] = '</select>';
			$html[ ] = '</td>';
		}
		if ( $this->customer ) {
			$html[ ] = '<td class="toolbar">';
			$html[ ] = sprintf( '<select name="%s" onchange="this.form.submit()">', CUSTOMER );
			$sql = 'select name from config_customers where valid_until is null order by name;';
			$this->application->database->execute_query( $sql );
			$selected = $this->params[ CUSTOMER ] == CUSTOMER_DEFAULT ? ' selected' : '';
			$html[ ] = sprintf( '<option value="%s"%s>%s</option>', CUSTOMER_DEFAULT, $selected, CUSTOMER_DEFAULT );
			foreach ( $this->application->database->rows as $row ) {
				$customer = $row[ 'name' ];
				$selected = $this->params[ CUSTOMER ] == $customer ? ' selected' : '';
				$html[ ] = sprintf( '<option value="%s"%s>%s</option>', $customer, $selected, $customer );
			}
			$html[ ] = '</select>';
			$html[ ] = '</td>';
		}
		$html[ ] = '<td class="toolbar">';
		$html[ ] = '<ul class="toolbar">';
		foreach ( $menu as $key => $value ) {
			$li = file_exists( sprintf( 'inc/%s.png', $key ) ) ? sprintf( '<img alt="%s" height="26" width="26" border="0" src="inc/%s.png">', $key, $key ) : $key;
			if ( is_array( $value ) ) {
				$html[ ] = sprintf( '<li><a href="#">%s</a>', $li );
				$html[ ] = '<ul>';
				foreach ( $value as $item ) {
					list ( $subitem, $link ) = $item;
					$html[ ] = '<li>';
					list( $dummy, $source ) = explode( '?', $link, 2 );
					!empty( $_REQUEST[ PAGE ] )  && $html[ ] = sprintf( '<a class="item1" href="%s&%s">+</a>', $_SERVER['REQUEST_URI'], $source  );
					$html[ ] = sprintf( '<a class="item2" href="%s">%s</a>', $link, $subitem );
					$html[ ] = '</li>';
				}
				$html[ ] = '</ul>';
				$html[ ] = '</li>';
			} else {
				$html[ ] = sprintf( '<li><a href="%s">%s</a></li>', $value, $li );
			}
		}
		$html[ ] = '</ul>';
		$html[ ] = '</td>';
		$html[ ] = '</tr></table>';
		$html[ ] = '</form>';
		$html[ ] = '</div>';
		$html[ ] = '</br></br></br>';
		return $html;
	}

	function output() {
		$title = array();
		foreach ( $this->reports as $report ) {
			$report->simple || $title[ ] = $report->title;
		}
		count( $title ) == 0 && $title[ ] = 'Overview';
		$title = implode( ', ', $title );
		strrpos( $title, ', ' ) && $title = substr_replace( $title, ' and ', strrpos( $title, ', ' ), 2 );
		$this->title = str_replace( '%TITLE', $title, $this->title );
		while ( @ob_end_flush( ) );
		@ob_implicit_flush( true );
		@header( 'Content-type: text/html; charset=utf-8' );
		@ob_start( );
		@session_start( );
		@session_cache_limiter( 'private, must-revalidate' );
		@session_cache_expire( 5 * 60 );
		$html = array();
		$html[ ] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
		$html[ ] = '<html>';
		$html[ ] = '<head>';
		$html[ ] = '<title>';
		$html[ ] = sprintf( APPLICATION, $this->application->region );
		$html[ ] = '</title>';
		$html[ ] = '<meta content="text/html; charset=UTF-8" http-equiv=Content-Type>';
		$html[ ] = '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
		$html[ ] = '<link rel="icon" type="image/x-icon" href="/favicon.ico" />';
		$html[ ] = '<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />';
		$html[ ] = '<link rel="stylesheet" type="text/css" href="inc/mars.css">';
		if ( $this->params[ MODE ] == INTERACTIVE ) {
			$html[ ] = '<script type=text/javascript>';
			$html[ ] = 'window.onload = function(){ document.getElementById("loading").style.display = "none" }';
			$html[ ] = '</script>';
		}
		if ( in_array( $this->params[ MODE ], array( INTERACTIVE, ADMIN ) ) ) {
			$html[ ] = '<link rel="stylesheet" type="text/css" href="inc/mars.report.css">';
		} else {
			$html[ ] = '<style type="text/css">';
			$html[ ] = file_get_contents( __DIR__ . '/mars.report.css' );
			$html[ ] = '</style>';
		}
		$html[ ] = '</head>';
		$html[ ] = '<body>';
		$this->html = $html;
		if ( PHP_SAPI !== 'cli' ) echo implode( PHP_EOL, $this->html );
		if ( in_array( $this->params[ MODE ], array( INTERACTIVE, ADMIN ) ) ) {
			echo implode( PHP_EOL, $this->output_toolbar( ) );
		}
		$logo_file = sprintf( '%s/../logo.png', __DIR__ );
		$src = sprintf( 'data:image/png;base64,%s', base64_encode( file_get_contents( $logo_file ) ) );
		$html = array();
		$html[ ] = '<div class="header">';
		$html[ ] = '<table class="header">';
		$html[ ] = '<tr><td class="header">';
//		$html[ ] = sprintf( '<img alt="Company logo" src="data:image/png;base64,%s">',base64_encode( file_get_contents( __DIR__ . '/../logo.png' ) ) );		
		$html[ ] = '<img alt="Company logo" src="%company-logo%">';		
		$html[ ] = '</td>';
		$html[ ] = '<td class="header">';
		$html[ ] = $this->title;
		$html[ ] = '</td>';
		$html[ ] = '</tr></table>';
		$html[ ] = '</div>';
		$html[ ] = '</br>';
		if ( $this->message != '' ) {
			$html[ ] = '<div class="title">';
			$html[ ] = 'Message';
			$html[ ] = '</div>';
			$html[ ] = '<div class="info">';
			$html[ ] = '<table class="info"><tr><td class="info">';
			$html[ ] = $this->message;
			$html[ ] = '</td></tr></table>';
			$html[ ] = '</div></br>';
			$this->message = '';
		}
		$this->html = array_merge( $this->html, $html );
		if ( PHP_SAPI !== 'cli' ) echo str_replace( '%company-logo%', $src, implode( PHP_EOL, $this->html ) );
		if ( $this->params[ MODE ] == INTERACTIVE ) {
			$html = array();
			$html[ ] = '<div id="loading">';
			$html[ ] = 'Loading ... ';
			$html[ ] = '<img align="center" alt="Loading" title="Loading..." src="inc/progress_bar.gif">';
			$html[ ] = '... please wait!';
			$html[ ] = '</div>';
			echo implode( PHP_EOL, $html );
		}
		ob_flush( );
		if ( $this->params[ MODE ] == ADMIN  and empty( $_COOKIE[ USERNAME ] ) ) {
			$html = array();
			$html[ ] = '<div id="login">';
			$html[ ] = 'Administrative login required';
			$html[ ] = '</br>';
			$html[ ] = '</br>';
			$html[ ] = '<form method="post" action="">';
			$html[ ] = sprintf( '<input type="hidden" name="%s" value="%s">', PAGE, $this->id );
			$html[ ] = '<table class="login"><tr>';
			$html[ ] = sprintf( '<td class="login">User:</td><td class="login"><input type="text" size="20" name="%s" value="%s"></td>', USERNAME, '' );
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '<td class="login">Password:</td><td class="login"><input type="password" size="20" name="%s" value="%s"></td>', PWD, '' );
			$html[ ] = '</tr></table>';
			$html[ ] = '<hr>';
			$html[ ] = sprintf( '<button type="submit" class="insert login" name="%s" value="%s">Log In</button>', ACTION, LOGIN );
			$html[ ] = '</form>';
			$html[ ] = '</div>';
			echo implode( PHP_EOL, $html );
		} else if ( in_array( $this->params[ MODE ], array( INTERACTIVE, HTML, ADMIN ) ) ) {
			$html = array();
			if ( $this->params[ MODE ] == ADMIN ) {
				$sql = 'select name from config_cellservers where valid_until is null order by name;';
				$this->application->database->execute_query( $sql );
				foreach ( $this->application->database->rows as $row ) {
					$this->cellservers[] = array( 'name' => $row[ 'name' ], 'value' => $row[ 'name' ] );
				}
				$sql = 'select name from config_customers where valid_until is null order by name;';
				$this->application->database->execute_query( $sql );
				foreach ( $this->application->database->rows as $row ) {
					$this->customers[] = array( 'name' => $row[ 'name' ], 'value' => $row[ 'name' ] );
				}
				$sql = 'select name,value from config_timeperiods where valid_until is null order by ord;';
				$this->application->database->execute_query( $sql );
				foreach ( $this->application->database->rows as $row ) {
					$this->timeperiods[] = array( 'name' => $row[ 'name' ], 'value' => $row[ 'value' ] );
				}
				if ( $this->reports[ 0 ]->name == REPORTS_ADMIN  ) {
					$html[ ] = '<div class="title">';
					$html[ ] = 'Administrative tasks';
					$html[ ] = '</div>';
					$html[ ] = '<div class="execute">';
					$html[ ] = '<form method="post" action="" enctype="multipart/form-data">';
					$html[ ] = '<table class="execute">';
					$html[ ] = '<tr>';
					$html[ ] = '<td class="execute">';
					$html[ ] = '&nbsp;Queue DB task';
					$html[ ] = '</td><td class="execute" colspan="2">';
					$html[ ] = sprintf( '<select title="Select a task that will be queued for execution" name="%s">', ROUTINE );
					foreach ( array( '', NORMAL_ROUTINE, DAILY_ROUTINE, MAINTENANCE ) as $row ) {
						$html[ ] = sprintf( '<option class="toolbar" value="%s">%s</option>', $row, $row );
					}
					$html[ ] = '</select>';
					$html[ ] = '</td><td class="execute">';
					$html[ ] = sprintf( '<button type="submit" class="insert" name="%s" value="%s">&nbsp;Queue&nbsp;</button>', ACTION, ROUTINE );
					$html[ ] = '</td class="execute">';
					$html[ ] = '</tr><tr>';
					$html[ ] = '<td class="execute">';
					$html[ ] = '&nbsp;Delete sessions';
					$html[ ] = '</td><td class="execute">';
					$html[ ] = sprintf( '<input title="Enter one or more comma-separated session ID`s for deletion" type="text" size="60" name="%s" value="">', SID );
					$html[ ] = '</td><td class="execute">';
					$html[ ] = sprintf( 'on <select title="Select target Cell Server" name="%s">', CELLSERVER );
					$html[ ] = '<option class="toolbar" value=""></option>';
					foreach ( $this->cellservers as $row ) {
						$html[ ] = sprintf( '<option class="toolbar" value="%s">%s</option>', $row[ 'name' ], $row[ 'name' ] );
					}
					$html[ ] = '</select>';
					$html[ ] = '</td><td class="execute">';
					$html[ ] = sprintf( '<button type="submit" class="remove" name="%s" value="%s">&nbsp;Delete&nbsp;</button>', ACTION, DELETE_SID );
					$html[ ] = '</td>';
					$html[ ] = '</tr><tr>';
					$html[ ] = '<td class="execute">';
					$html[ ] = '&nbsp;Execute SQL';
					$html[ ] = '</td><td class="execute" colspan="2">';
					$html[ ] = sprintf( '<input title="Enter a valid SQL command" type="text" size="100" name="%s" value="">', SQL );
					$html[ ] = '</td><td class="execute">';
					$html[ ] = sprintf( '<button type="submit" class="modify" name="%s" value="%s">Execute</button>', ACTION, EXECUTE );
					$html[ ] = '</td>';
					$html[ ] = '</tr><tr>';
					$html[ ] = '<td class="execute">';
					$html[ ] = '&nbsp;Upload package:';
					$html[ ] = '</td><td class="execute" colspan="2">';
					$html[ ] = sprintf( '<input title="Select a valid MARS update package" type="file" accept=".zip" name="%s">', FILE );
					$html[ ] = '</td><td class="execute">';
					$html[ ] = sprintf( '<button type="submit" class="upload" name="%s" value="%s">&nbsp;&nbsp;&nbsp;Upload&nbsp;&nbsp;&nbsp;</button>', ACTION, UPLOAD );
					$html[ ] = '</td>';
					$html[ ] = '</tr>';
					$html[ ] = '</table>';
					$html[ ] = '</form>';
					$html[ ] = '</div>';
					$html[ ] = '</br>';
					$this->reports = array( );
				}
			}
			foreach ( $this->reports as $report ) {
				$report->prepare( );
				$html[ ] = $report->output( );
			}
			$stylehtml = array( '' );
			$stylehtml[ ] = '<style>';
			foreach ( $this->styles as $key=>$value ) {
				$stylehtml[ ] =sprintf( '.s%s{border:#CCCCCC 1px solid;padding:5px;vertical-align:top;%s}', $key, $value );
			}
			$stylehtml[ ] = '</style>';
			$html = array_merge( $stylehtml, $html );
			if ( in_array( $this->params[ MODE ], array( INTERACTIVE, ADMIN ) ) ) echo implode( PHP_EOL, $html );
			$this->html = array_merge( $this->html, $html );
			ob_flush( );
		}
		$name = $this->application->name;
		mod_mysqli( ) && $name .= 'i';
		$duration = $this->application->get_duration( );
		$db_duration = round( $this->application->database->durations, 2 );
		$footer = array();
		$footer[ ] = '<div class="footer">';
		$footer[ ] = sprintf( '<b>%s</b> | %s <b>%s</b> report(s) prepared in <b>%s</b> second(s) on <b>%s (%s)</b>.', 
			$name, COPYRIGHT, count( $this->reports ), $duration, date( 'F j, Y, H:i' ), date_default_timezone_get( ) );
		$footer[ ] = '</div>';
		$footer[ ] = '</body>';
		$footer[ ] = '</html>';
		$this->html = array_merge( $this->html, $footer );
		$result = true;
		if ( in_array( $this->params[ MODE ], array( HTML, CSV ) ) and empty( $this->params[ MAIL_TO ] ) ) {
			$mail_to = empty( $_COOKIE[ MAIL_TO ] ) ? '' : $_COOKIE[ MAIL_TO ];
			$mail_cc = empty( $_COOKIE[ MAIL_CC ] ) ? '' : $_COOKIE[ MAIL_CC ];
			$schedule_name = empty( $_COOKIE[ SCHEDULE_NAME ] ) ? $this->title: $_COOKIE[ SCHEDULE_NAME ];
			$schedule_date = empty( $_COOKIE[ SCHEDULE_DATE ] ) ? '' : $_COOKIE[ SCHEDULE_DATE ];
			$schedule_time = empty( $_COOKIE[ SCHEDULE_TIME ] ) ? date( 'H:00', strtotime( '+1 hour' ) ) : $_COOKIE[ SCHEDULE_TIME ];
			$html = array();
			$html[ ] = '<div class="mail">';
			$html[ ] = '<form method="post" action="">';
			$html[ ] = sprintf( '<input type="hidden" name="%s" value="%s">', PAGE, $this->id );
			$html[ ] = '<table class="mail"><tr>';
			$html[ ] = '<td colspan="5"><b>If you want to send it now, specify recipient\'s e-mail address.</b></td>';
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '<td><b>TO</b>:</td><td colspan="4"><input type="text" size="50" name="%s" value="%s"></td>', MAIL_TO, $mail_to );
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '<td><b>CC</b> (optional):</td><td colspan="3"><input type="text" size="50" name="%s" value="%s"></td>', MAIL_CC, $mail_cc );
			$html[ ] = sprintf( '<td><button type="submit" class="insert" name="%s" value="%s">Send</button></td>', ACTION, SEND );
			$html[ ] = '</tr><tr><td colspan="5">&nbsp;</td></tr><tr>';
			$html[ ] = '</tr><tr><td colspan="5">&nbsp;</td></tr><tr>';
			$html[ ] = '</tr><tr>';
			$html[ ] = '<td colspan="5"><b>If you want to schedule it, specify schedule\'s name, date/time and recipient\'s e-mail address.</b></td>';
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '<td><b>Name</b>:</td><td colspan="4"><input type="text" size="50" name="%s" value="%s"></td>', SCHEDULE_NAME, $schedule_name );
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '<td><b>Date</b> (blank for daily):</td><td><input type="text" size="10" name="%s" value="%s"></td>', SCHEDULE_DATE, $schedule_date );
			$html[ ] = sprintf( '<td><b>Time</b>:</td><td><input type="text" size="10" name="%s" value="%s"></td>', SCHEDULE_TIME, $schedule_time );
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '<td><b>TO</b>:</td><td colspan="4"><input type="text" size="50" name="%s" value="%s"></td>', 'S'.MAIL_TO, $mail_to );
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '<td><b>CC</b> (optional):</td><td colspan="3"><input type="text" size="50" name="%s" value="%s"></td>', 'S'.MAIL_CC, $mail_cc );
			$html[ ] = sprintf( '<td><button type="submit" class="modify" name="%s" value="%s">Schedule</button></td>', ACTION, SCHEDULE );
			$html[ ] = '</tr><tr>';
			$html[ ] = sprintf( '</tr></table>' );
			$html[ ] = '</form>';
			$html[ ] = '</div>';
			echo implode( PHP_EOL, $html );
		}
		if ( in_array( $this->params[ MODE ], array( HTML, CSV ) ) and !empty( $this->params[ MAIL_TO ] ) ) {
			if ( PHP_SAPI !== 'cli' ) {
				echo '<div class="mail">';
				echo sprintf( 'Sending "%s" report to %s ... ', $this->title, $this->params[ MAIL_TO ] );
			}
			ob_flush( );
			$delims = array( ';', ' ', '|' );
			$name = preg_replace( "([^\w\s\d\-_~,;\[\]\(\).])", '', $this->name );
			$subject = $this->title;
			$to = str_replace( $delims, ',', $this->params[ MAIL_TO ] );
			$cc = !empty( $this->params[ MAIL_CC ] ) ? str_replace( $delims, ',' , $this->params[ MAIL_CC ] ) : '';
			$from = $this->application->config[ 'MAIL_FROM' ];
			
			$uid = md5( uniqid( time( ) ) );
			$headers = array();
			$headers[ ] = 'MIME-Version: 1.0';
			$headers[ ] = sprintf( 'Content-Type: multipart/mixed; boundary="%s"', $uid );
			$headers[ ] = sprintf( 'From: %s', $from );
			!empty( $cc ) && $headers[ ] = sprintf( 'Cc: %s', $cc );
			$headers[ ] = sprintf( 'Reply-To: %s', $from );
			$headers[ ] = sprintf( 'Subject: %s', $subject );
			$headers[ ] = sprintf( 'X-Mailer: PHP/%s', phpversion( ) );
			
			$body = array();
			$body[ ] = 'This is a multi-part message in MIME format.';
			$body[ ] = '';
			$body[ ] = sprintf( '--%s', $uid );
			if ( $this->params[ MODE ] == HTML ) {
				$report_name = sprintf( '%s.html', $name );
				if ( strlen( implode( PHP_EOL, $this->html ) ) < MAX_HTML_INLINE_SIZE_MB * 1024 * 1024 ) {
					$body[ ] = 'Content-type:text/html; charset=utf8';
					$body[ ] = 'Content-Transfer-Encoding: 7bit';
					$body[ ] = '';
//					$body[ ] = implode( PHP_EOL, $this->html );
					$body[ ] = str_replace( '%company-logo%', 'cid:company-logo.png', implode( PHP_EOL, $this->html ) );
					$body[ ] = '';
					$body[ ] = sprintf( '--%s', $uid );
//					$body[ ] = 'Content-Type: application/octet-stream; name="logo.png"';
//					$body[ ] = 'Content-Transfer-Encoding: base64';
//					$body[ ] = 'Content-Disposition: attachment; filename="logo.png"';
					$body[ ] = 'Content-ID: <company-logo.png>';
					$body[ ] = 'Content-Type: image/gif';
					$body[ ] = 'Content-Transfer-Encoding: base64';
					$body[ ] = '';
					$body[ ] = chunk_split( base64_encode( file_get_contents( $logo_file ) ) );
				} else {
					$body[ ] = 'Content-type:text/plain; charset=utf8';
					$body[ ] = 'Content-Transfer-Encoding: 7bit';
					$body[ ] = '';
					$body[ ] = 'Attached is the generated report in HTML format.';
					$body[ ] = sprintf( '(Exceeded allowed size of inline HTML report %s MB)', MAX_HTML_INLINE_SIZE_MB );
					$body[ ] = '';
					$body[ ] = sprintf( '%s %s. %s report(s) prepared in %s second(s) on %s (%s).', 
						$this->application->name, str_replace( '&copy;', '(c)', COPYRIGHT ), count( $this->reports ), $duration, date( 'F j, Y, H:i' ), date_default_timezone_get( ) );
											
				}
				$html = str_replace( '%company-logo%', $src, implode( PHP_EOL, $this->html ) );
				$body[ ] = '';
				$body[ ] = sprintf( '--%s', $uid );
				if ( strlen( $html ) < MAX_HTML_ATTACHMENT_SIZE_MB * 1024 * 1024 ) {
					$body[ ] = sprintf( 'Content-Type: application/octet-stream; name="%s"', $report_name );
					$body[ ] = 'Content-Transfer-Encoding: base64';
					$body[ ] = sprintf( 'Content-Disposition: attachment; filename="%s"', $report_name );
					$body[ ] = '';
					$body[ ] = chunk_split( base64_encode( $html ) );
					$body[ ] = '';
				} else {
					$zip_file = sprintf( '%s/mars_%s_%s.zip', sys_get_temp_dir( ), session_id( ), $name );
					$zip = new ZipArchive;
					$zip->open( $zip_file, ZipArchive::CREATE );
					$zip->addFromString( $report_name, $html );
					$zip->close( );
					$body[ ] = sprintf( 'Content-Type: application/zip; name="%s.zip"', $name );
					$body[ ] = 'Content-Transfer-Encoding: base64';
					$body[ ] = sprintf( 'Content-Disposition: attachment; filename="%s.zip"', $name );
					$body[ ] = '';
					$body[ ] = chunk_split( base64_encode( file_get_contents( $zip_file ) ) );
					$body[ ] = '';
					unlink( $zip_file );
				}
			} else {
				$zip_file = sprintf( '%s/mars_%s_%s.zip', sys_get_temp_dir( ), session_id( ), $name );
				$zip = new ZipArchive;
				$zip->open( $zip_file, ZipArchive::CREATE );
				$body[ ] = 'Content-type:text/plain; charset=utf8';
				$body[ ] = 'Content-Transfer-Encoding: 7bit';
				$body[ ] = '';
				$body[ ] = 'Attached is the ZIP containing the generated reports in CSV format.';
				$body[ ] = '';
				$body[ ] = sprintf( '%s %s. %s report(s) prepared in %s second(s) on %s.', $this->application->name, str_replace( '&copy;', '(c)', COPYRIGHT ), count( $this->reports ), $duration, date( 'F j, Y, H:i' ) );
				$body[ ] = '';
				foreach ( $this->reports as $report ) {
					$report->prepare( );
					$output = $report->output( );
					$report_name = sprintf( '%s.csv', strtolower( str_replace( ' ', '_', $report->title ) ) );
					$zip->addFromString( $report_name, $output );
#					$body[ ] = sprintf( '--%s', $uid );
#					$body[ ] = sprintf( 'Content-Type: application/octet-stream; name="%s"', $report_name );
#					$body[ ] = 'Content-Transfer-Encoding: base64';
#					$body[ ] = sprintf( 'Content-Disposition: attachment; filename="%s"', $report_name );
#					$body[ ] = '';
#					$body[ ] = chunk_split( base64_encode( $output ) );
#					$body[ ] = '';
				}
				$zip->close( );
				$body[ ] = sprintf( '--%s', $uid );
				$body[ ] = sprintf( 'Content-Type: application/zip; name="%s.zip"', $name );
				$body[ ] = 'Content-Transfer-Encoding: base64';
				$body[ ] = sprintf( 'Content-Disposition: attachment; filename="%s.zip"', $name );
				$body[ ] = '';
				$body[ ] = chunk_split( base64_encode( file_get_contents( $zip_file ) ) );
				$body[ ] = '';
				unlink( $zip_file );
			}
			$body[ ] = sprintf( '--%s--', $uid );
			$result = mail( $to, $subject, implode( PHP_EOL, $body ), implode( PHP_EOL, $headers ) );
			if ( PHP_SAPI !== 'cli' ) {
				echo $result ? '<span style="color:green;">successful</span>' : '<span class="color:red;">failed</span>';
				echo sprintf( '. You may now <a href="%s">show</a> the page, or <a href="javascript:history.go(-1)">send it</a> to someone else, or <a href="%s">start over</a>.', $this->get_url( array( 
						MODE => INTERACTIVE, 
						MAIL_TO => '', 
						MAIL_CC => '', 
						REQUERY => 1 ) ), $_SERVER[ 'PHP_SELF' ] );
				echo '</div>';
				echo '</br>';
			}
		}
		if ( PHP_SAPI !== 'cli' ) echo implode( PHP_EOL, $footer );
		ob_flush( );
		ob_end_flush( );
		session_write_close( );
		return $result;
	}
}