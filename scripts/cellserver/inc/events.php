<?php

/*
 * MARS 3.0 EVENTS PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
	define( 'EVENTS_PROCESSES_CMD', 'wmic process get Name,Caption,ProcessId,ParentProcessId,CommandLine /value' );
	define( 'EVENTS_PROCESSES_ERROR', 'Error executing WMIC.' );
	define( 'EVENTS_PROCESSES_PPID', 'ParentProcessId' );
	define( 'EVENTS_PROCESSES_CMDLINE', 'CommandLine' );
	define( 'EVENTS_PROCESSES_PATTERN', 'Caption=(?P<Caption>.+)\s+CommandLine=(?P<CommandLine>.*)\s+Name=(?P<Name>.+)' .
		'\s+ParentProcessId=(?P<ParentProcessId>.+)\s+ProcessId=(?P<ProcessId>%s)\s+' );
} else {
	define( 'EVENTS_PROCESSES_CMD', 'ps -ef' );
	define( 'EVENTS_PROCESSES_ERROR', 'Error executing PS -ef.' );
	define( 'EVENTS_PROCESSES_PPID', 'PPID' );
	define( 'EVENTS_PROCESSES_CMDLINE', 'CMD' );
	define( 'EVENTS_PROCESSES_PATTERN','(?P<UID>\S+)\s+(?P<PID>%s)\s+(?P<PPID>\d+)\s+(?P<C>\d+)\s+(?P<STIME>\S+)' . 
	'\s+(?P<TTY>\S+)\s+(?P<TIME>\S+)\s+(?P<CMD>.+)' );
}
define( 'EVENTS_OMNIRPT_PROCESS_PATTERN', '-oneliner\s(?P<value>\w+)\s' );
define( 'EVENTS_OMNITRIG_PROCESS_PATTERN', '-event\s(?P<value>\w+)\s' );
define( 'EVENTS_OMNITRIG_PROCESS_ERROR', 'Error retrieving parent OMNITRIG process.' );
define( 'EVENTS_VARIABLES', 'BACKUPGROUP,BACKUPTYPE,DATALIST,EVENTOBJECT,MODE,NODE,NUMBACKUPERRORS,' .
	'OWNER,PREVIEW,RESTARTED,RESTART_COUNT,SESSIONID,SESSIONKEY,SESSIONLIST,SESSIONSTATUS,SESSIONSTATUSNUM,' .
	'SESSIONSUBTYPE,SESSIONTYPE,SEVERITY,MEDIAPOOL,NUMFREEMEDIA,DEVICENAME,DEVICESLOT,MEDIUMID,' .
	'LICCATEGORY,LICQUANTITY,TBLSPACENAME,TBLSPACEOCCUPIED,OB2_DEVICE_ERROR_COUNT,ALARMMSG,' .
	'COMMANDERCODE,COMMANDERMSG,NUMEVENTS' );
define( 'EVENTS_EVENT_ERROR', 'Blank/invalid event name.' );
define( 'EVENTS_NOTIFICATION', 'NOTIFICATION' );
define( 'EVENTS_NOTIFICATION_QUEUED', 'Notification "%s" was queued.' );	
define( 'EVENTS_NOTIFICATION_ERROR', 'Error "%s" queuing notification "%s".' );
define( 'EVENTS_QUEUE', '%s/queue/%s.txt' );
define( 'EVENTS_TMP', '%s/tmp/%s.tmp' );

class mars_event {
	var $application;
	var $php_pid = 0;
	var $cmd_pid = 0;
	var $omnirpt_pid = 0;
	var $omnitrig_pid = 0;
	var $name = '';
	var $contents = '';
	var $processes = '';
	var $variables = '';
	var $timestamp;
	
	function mars_event( $application ) {
		$this->application = $application;
		$this->timestamp = sprintf( '%s-%s', date( 'Ymd-His' ), round( fmod( microtime( true ), 1 ) * pow( 10, 4 ) ) );
		foreach ( $_SERVER as $key=>$value ) {
			$this->variables .= sprintf( '%s=%s', $key, $value ) . PHP_EOL;
		}
		$this->contents = sprintf( 'TIMESTAMP=%s', $this->timestamp ) . PHP_EOL;
		$this->php_pid = getmypid();
	}

	function execute() {
		$result = false;
		try {
			$this->application->append_file( sprintf( EVENTS_TMP, $this->application->root, $this->timestamp ), $this->contents, false );
			$this->application->append_file( sprintf( EVENTS_TMP, $this->application->root, $this->timestamp ), $this->variables, false );
			$lines = $this->application->cache_command( EVENTS_PROCESSES_CMD );
			if ( !$lines ) throw new exception( sprintf( EVENTS_PROCESSES_ERROR ) );
			$this->processes = implode( PHP_EOL, $lines );
			$this->application->append_file( sprintf( EVENTS_TMP, $this->application->root, $this->timestamp ), $this->processes, false );
			$process = $this->get_process( $this->php_pid );
			$process && $this->cmd_pid = $process[ EVENTS_PROCESSES_PPID ]; 
			$process = $this->get_process( $this->cmd_pid );
			$process && $this->omnirpt_pid = $process[ EVENTS_PROCESSES_PPID ];
			$process = $this->get_process( $this->omnirpt_pid );
			preg_match( sprintf( '/%s/i', EVENTS_OMNIRPT_PROCESS_PATTERN ), $process[ EVENTS_PROCESSES_CMDLINE ], $match );
			!empty( $match[ 'value' ] ) && $this->name = $match[ 'value' ];
			$process && $this->omnitrig_pid = $process[ EVENTS_PROCESSES_PPID ];
			$process = $this->get_process( $this->omnitrig_pid );
			if ( !$process ) 
				throw new exception( EVENTS_OMNITRIG_PROCESS_ERROR );
			preg_match( sprintf( '/%s/i', EVENTS_OMNITRIG_PROCESS_PATTERN ), $process[ EVENTS_PROCESSES_CMDLINE ], $match );
			!empty( $match[ 'value' ] ) && $this->name = $match[ 'value' ];
			if ( empty( $this->name ) ) 
				throw new exception( EVENTS_EVENT_ERROR );
			$arr = array_intersect_key( $_SERVER, array_flip( explode( ',', EVENTS_VARIABLES ) ) );
			$this->contents .= sprintf( '%s=%s', EVENTS_NOTIFICATION, $this->name ) . PHP_EOL;
			foreach ( $arr as $key=>$value ) {
				$this->contents .= sprintf( '%s=%s', $key, $value ) . PHP_EOL;
			}
			$this->application->append_file( sprintf( EVENTS_QUEUE, $this->application->root, 
				sprintf( '%s-%s', $this->timestamp, $this->name ) ), $this->contents, false );
			unlink( sprintf( EVENTS_TMP, $this->application->root, $this->timestamp ) );
			$this->application->log_action( sprintf( EVENTS_NOTIFICATION_QUEUED, $this->name ) );
			$result = true;
		} catch ( exception $e ) {
			$this->application->log_error( sprintf( EVENTS_NOTIFICATION_ERROR, $e->getMessage(), $this->timestamp ) );
		}
		return $result;
	}
	
	function get_process( $pid ) {
		$match = false;
		preg_match( sprintf( '/%s/m', sprintf( EVENTS_PROCESSES_PATTERN, $pid ) ), $this->processes, $match );
		return $match;
	}
}
