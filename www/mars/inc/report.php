<?php

/*
 * MARS 3.0 REPORT PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'ID', 'id' );
define( 'ADD', 'add' );
define( 'MODIFY', 'upd' );
define( 'REMOVE', 'del' );
define( 'SEND', 'snd' );
define( 'LOGIN', 'log' );
define( 'ACTION', 'act' );
define( 'REPORT', 'rpt' );
define( 'FIELD', 'fld' );
define( 'OPERATOR', 'op' );
define( 'VALUE', 'val' );
define( 'PRIMARY', 'P' );
define( 'READONLY', 'R' );
define( 'COMBOBOX', 'C' );

define( 'NAME', 'n' );
define( 'CONDITIONS', 'c' );
define( 'HIGHLIGHT', 'h' );
define( 'LINK', 'l' );
define( 'LINK_CONDITIONS', 'x' );
define( 'ORDERS', 'o' );
define( 'DATAPAGE', 'p' );
define( 'SHOW_PAGINATION', 'sp' );
define( 'SHOW_CONDITIONS', 'sc' );
define( 'SHOW_LEGEND', 'sl' );
define( 'REQUERY', 'q' );

define( 'SHOW_PAGINATION_DEFAULT', 1 );
define( 'SHOW_CONDITIONS_DEFAULT', 0 );
define( 'SHOW_LEGEND_DEFAULT', 0 );
define( 'REQUERY_DEFAULT', 1 );

define( 'OPERATORS', 
	serialize( 
		array( 
			array( 
				'operator' => 'REGEXP', 
				'description' => 'matches' ), 
			array( 
				'operator' => 'NOT_REGEXP', 
				'description' => 'not match' ), 
			array( 
				'operator' => '==', 
				'description' => 'is' ), 
			array( 
				'operator' => '!=', 
				'description' => 'is not' ), 
			array( 
				'operator' => '<', 
				'description' => 'is less than' ), 
			array( 
				'operator' => '>', 
				'description' => 'is more than' ), 
			array( 
				'operator' => '<=', 
				'description' => 'is equal or less than' ), 
			array( 
				'operator' => '>=', 
				'description' => 'is equal or more than' ) ) ) );
define( 'INPUT_SIZE', 64 );
define( 'PATTERN_SESSIONID' , '/\d{4}\/\d{2}\/\d{2}-\d+/i' );

class report {
	var $page;
	var $name;
	var $title;
	var $description;
	var $sql;
	var $timeperiod;
	var $customer;
	var $params = array();
	var $datapage_limit;
	var $fields = array();
	var $headers = array();
	var $rows = array();
	var $links = array();
	var $styles = array();
	var $pivot = array();

	function report( $page, $name ) {
		$this->page = $page;
		$this->name = $name;
		$this->simple = in_array( $this->name, unserialize( REPORTS_SIMPLE ) );
		$this->show_footer = 0;
		$this->show_open_in_new_window = 0;
		$this->params[ CONDITIONS ] = array();
		$this->params[ ORDERS ] = array();
		$this->params[ HIGHLIGHT ] = '';
		$this->params[ DATAPAGE ] = 1;
		$this->params[ REQUERY ] = REQUERY_DEFAULT;
		$this->params[ SHOW_CONDITIONS ] = SHOW_CONDITIONS_DEFAULT;
		$this->params[ SHOW_PAGINATION ] = SHOW_PAGINATION_DEFAULT;
		$this->params[ SHOW_LEGEND ] = SHOW_LEGEND_DEFAULT;
		$sql = sprintf( "select * from config_reports where name='%s';", $this->name );
		$this->page->application->database->execute_query( $sql );
		if ( $this->page->application->database->row_count == 0 ) return false;
		$rows = $this->page->application->database->rows[ 0 ];
		$this->title = $rows[ 'title' ];
		$this->description = $rows[ 'description' ];
		$this->sql = $rows[ 'sql' ];
		$this->timeperiod = $rows[ 'timeperiod' ];
		$this->customer = $rows[ 'customer' ];
		$this->datapage_limit = $rows[ 'datapage_limit' ];
		foreach ( explode( ',', $rows[ 'pivot' ] ) as $item ) {
			if ( empty( $item ) or $item[ 0 ] == '#' ) continue;
			$this->pivot[ ] = trim( $item );
			$this->params[ SHOW_PAGINATION ] = 0;
		}
		foreach ( explode( "\n", $rows[ 'fields' ] ) as $item ) {
			if ( empty( $item ) or $item[ 0 ] == '#' ) continue;
			$field = preg_split( '/\t+/', $item );
			$name = isset( $field[ 0 ] ) ? trim( $field[ 0 ] ) : '';
			$title = isset( $field[ 1 ] ) ? trim( $field[ 1 ] ) : '';
			$link = isset( $field[ 2 ] ) ? trim( $field[ 2 ] ) : '';
			$highlight = isset( $field[ 3 ] ) ? trim( $field[ 3 ] ) : '';
			$highlight == 'NONE' && $highlight = '';
			$link_conditions = isset( $field[ 4 ] ) ? trim( $field[ 4 ] ) : '';
			$description = isset( $field[ 5 ] ) ? trim( $field[ 5 ] ) : '';
			!empty( $name ) && $this->headers[ $name ] = $title;
			!empty( $link ) && !empty( $link_conditions ) && $this->links[ $name ] = array( 
				$link, 
				$highlight, 
				$link_conditions, 
				$description );
		}
		foreach ( explode( "\n", $rows[ 'styles' ] ) as $item ) {
			if ( empty( $item ) or $item[ 0 ] == '#' ) continue;
			$field = preg_split( '/\t+/', $item );
			$fields = isset( $field[ 0 ] ) ? trim( $field[ 0 ] ) : '';
			$condition = isset( $field[ 1 ] ) ? trim( $field[ 1 ] ) : '';
			$css = isset( $field[ 2 ] ) ? $field[ 2 ] : '';
			$description = isset( $field[ 3 ] ) ? $field[ 3 ] : $condition;
			$this->styles[ ] = array( 
				'fields' => $fields, 
				'conditions' => $condition, 
				'css' => $css,
				'description' => $description );
		}
	}

	function set_parameters( $params = array( ) ) {
		$this->params = array_merge( $this->params, $params );
		!empty( $params[ CONDITIONS ] ) && $this->params[ CONDITIONS ] = array_unique( explode( '||', $params[ CONDITIONS ] ) );
		!empty( $params[ ORDERS ] ) && $this->params[ ORDERS ] = array_unique( explode( '||', $params[ ORDERS ] ) );
		if ( in_array( $this->page->params[ MODE ], array( HTML, CSV ) ) and ( $this->params[ SHOW_PAGINATION ] == 1 ) ) {
			$this->params[ REQUERY ] = 1;
		}
	}

	function get_parameters( $new = array( ) ) {
		$url = array();
		if ( $this->simple and empty( $new[ LINK ] ) and empty( $new[ REQUERY ] ) 
				and ( empty( $new[ MODE ] ) or ( $new[ MODE ] == MODE_DEFAULT ) ) ) {
			return $url;
		}
		$url[ NAME ] = $this->name;
		$params = $this->params;
		
		!empty( $new[ CONDITIONS ] ) && $params[ CONDITIONS ][ ] = $new[ CONDITIONS ];
		!empty( $new[ LINK ] ) && $new[ LINK ] == $this->name && $params[ CONDITIONS ][ ] = $new[ LINK_CONDITIONS ];
		!empty( $params[ CONDITIONS ] ) && $url[ CONDITIONS ] = trim( implode( '||', array_unique( $params[ CONDITIONS ] ) ), '||' );
		
		if ( !empty( $new[ LINK ] ) and $new[ LINK ] != $this->name ) {
			$url[ LINK ] = $new[ LINK ];
			!empty( $new[ LINK_CONDITIONS ] ) && $url[ LINK_CONDITIONS ] = $new[ LINK_CONDITIONS ];
		}
		
		if ( !empty( $new[ ORDERS ] ) ) {
			switch ( $new[ ORDERS ][ 0 ] ) {
				case '-':
					unset( $params[ ORDERS ][ array_search( substr( $new[ ORDERS ], 1 ), $params[ ORDERS ] ) ] );
					break;
				case '*':
					unset( $params[ ORDERS ][ array_search( substr( $new[ ORDERS ], 1 ), $params[ ORDERS ] ) ] );
					$params[ ORDERS ][ ] = sprintf( '%s-', substr( $new[ ORDERS ], 1 ) );
					break;
				default:
					$params[ ORDERS ][ ] = $new[ ORDERS ];
					break;
			}
		}
		!empty( $params[ ORDERS ] ) && $url[ ORDERS ] = trim( implode( '||', array_unique( $params[ ORDERS ] ) ), '||' );
		
		$params = array_merge( $params, $new );
		$params[ DATAPAGE ] != 1 && $url[ DATAPAGE ] = $params[ DATAPAGE ];
		!empty( $params[ HIGHLIGHT ] ) && $url[ HIGHLIGHT ] = $params[ HIGHLIGHT ];
		$params[ SHOW_PAGINATION ] != SHOW_PAGINATION_DEFAULT && $url[ SHOW_PAGINATION ] = $params[ SHOW_PAGINATION ];
		$params[ SHOW_CONDITIONS ] != SHOW_CONDITIONS_DEFAULT && $url[ SHOW_CONDITIONS ] = $params[ SHOW_CONDITIONS ];
		$params[ SHOW_LEGEND ] != SHOW_LEGEND_DEFAULT && $url[ SHOW_LEGEND ] = $params[ SHOW_LEGEND ];
		!empty( $params[ ID ] ) && $url[ ID ] = $params[ ID ];
		
		if ( !empty( $new[ REQUERY ] ) or !empty( $new[ CONDITIONS ] ) or !empty( $new[ ORDERS ] ) or !empty( $new[ DATAPAGE ] ) or isset( 
			$new[ SHOW_PAGINATION ] ) or ( !empty( $new[ LINK ] ) and $new[ LINK ] == $this->name ) or !empty( $new[ ID ] ) ) {
			$params[ REQUERY ] = 1;
		} else {
			$params[ REQUERY ] = 0;
		}
		$url[ REQUERY ] = $params[ REQUERY ];
		return $url;
	}

	function get_field_title( $field = '' ) {
		$result = isset( $this->headers[ $field ] ) ? $this->headers[ $field ] : $field;
		return $result;
	}

	function get_value( $key, $operator, $value, $format = 'Y-m-d H:i:s' ) {
		$result = trim( str_replace( "'", '', str_replace( '"', "'", $value ) ) );
		foreach ( $this->fields as $field ) {
			if ( $field->name == $key and in_array( $field->type, array( 'datetime', 'time', 'timestamp' ) ) ) {
				$result = date( $format, strtotime( $result ) );
				if ( $operator == 'REGEXP' ) {
					$result = trim( str_replace( '00:00:00', '', $result ) );
					$result = trim( str_replace( '00:00', '', $result ) );
				}
			}
		}
		return $result;
	}
	
	function prepare() {
		$text = array();
		if ( $this->timeperiod ) {
			$datetime_from = $this->page->get_datetime( $this->page->params[ TIMEPERIOD ][ 0 ] );
			$datetime_to = $this->page->get_datetime( $this->page->params[ TIMEPERIOD ][ 1 ] );
			$this->description .= sprintf( ' for time period <b>%s</b> - <b>%s</b>', date( 'F j, Y, H:i', $datetime_from ), 
				date( 'F j, Y, H:i', $datetime_to ) );
			$sql = sprintf( "set @datetime_from='%s',@datetime_to='%s';", date( 'Y-m-d H:i:s', $datetime_from ), 
				date( 'Y-m-d H:i:s', $datetime_to ) );
		} else {
			$sql = "set @datetime_from='',@datetime_to='';";
		}
		$this->page->application->database->execute_query( $sql );
		if ( $this->customer and $this->page->params[ CUSTOMER ] != CUSTOMER_DEFAULT ) {
			$customer = $this->page->params[ CUSTOMER ];
			$this->description .= sprintf( ' for customer <b>%s</b>', $customer );
			$sql = sprintf( "set @customer='%s';", $customer );
		} else {
			$sql = "set @customer='';";
		}
		$this->page->application->database->execute_query( $sql );
		$fields_file = sprintf( '%s/mars_%s_%s_flds.tmp', sys_get_temp_dir( ), session_id( ), $this->name );
		$rows_file = sprintf( '%s/mars_%s_%s_rows.tmp', sys_get_temp_dir( ), session_id( ), $this->name );
		if ( $this->params[ REQUERY ] == 1 or !file_exists( $fields_file ) or !file_exists( $rows_file ) ) {
			$this->params[ REQUERY ] = 1;
			$sql = sprintf( '%s LIMIT 0;', str_replace( '%order','1', str_replace( '%where', ' ', $this->sql ) ) );
			$this->page->application->database->execute_query( $sql );
			$this->fields = $this->page->application->database->fields;	
			$where = array();
			foreach ( $this->params[ CONDITIONS ] as $condition ) {
				list ( $key, $operator, $value ) = sscanf( $condition, '%s %s %[^[]]' );
				$operator = str_replace( '==', '=', $operator );
				$operator = str_replace( '_', ' ', $operator );
				$value = $this->get_value( $key, $operator, $value );
				if ( $value == '' ) {
					if ( $operator == '=' ) {
						$operator = 'is';
						$value = 'null';
					}
					if ( $operator == '!=' ) {
						$operator = 'is not';
						$value = 'null';
					}
				} else {
					$value = sprintf( "'%s'", $value );
				}
				$where[ ] = sprintf( "%s %s %s", $key, $operator, $value );
			}
			$where = count( $where ) > 0 ? implode( ' and ', $where ) : '';
			$orderby = array();
			foreach ( $this->params[ ORDERS ] as $order ) {
				list ( $field, $suffix ) = sscanf( $order, '%[a-z_]%s' );
				$orderby[ ] = sprintf( '%s %s', $field, $suffix == '-' ? 'desc' : 'asc' );
			}
			$orderby = count( $orderby ) > 0 ? implode( ',', $orderby ) : '';
			if ( !in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) or $this->params[ SHOW_PAGINATION ] == 0 ) {
				$limit = '';
			} else {
				$limit = $this->params[ DATAPAGE ] == 1 ? sprintf( ' LIMIT %s', $this->datapage_limit + 1 ) : sprintf( ' LIMIT %s,%s', 
					( 

					$this->params[ DATAPAGE ] - 1 ) * $this->datapage_limit, $this->datapage_limit + 1 );
			}
			$sql = $this->sql;
			if ( $where != '' ) {
				if ( stristr( $sql, '%where' ) ) {
					$sql = str_replace( '%where', sprintf( 'WHERE %s', $where ), $sql );
				} elseif ( stristr( $sql, '%and' ) ) {
					$sql = str_replace( '%and', $where, $sql );
				} else {
					$sql = sprintf( '%s WHERE %s', $this->sql, $where );
				}
			} else {
				$sql = str_replace( '%where', '', $sql );
			}
			if ( $orderby != '' ) {
				if ( stristr( $sql, '%order' ) ) {
					$sql = str_replace( '%order', $orderby, $sql );
				} else {
					$sql = sprintf( '%s ORDER BY %s', $sql, $orderby );
				}
			} else {
				$sql = str_replace( ',%order', '', $sql );
			}
			$sql = sprintf( '%s%s;', $sql, $limit );
			$this->page->application->database->execute_query( $sql );
			file_put_contents( $fields_file, json_encode( $this->page->application->database->fields ) );
			file_put_contents( $rows_file, json_encode( $this->page->application->database->rows ) );
		}
		$this->fields = json_decode( file_get_contents( $fields_file, false ) );
		$this->rows = json_decode( file_get_contents( $rows_file ), true );

		foreach ( $this->params[ CONDITIONS ] as $condition ) {
			list ( $key, $operator, $value ) = sscanf( $condition, '%s %s %[^[]]' );
			$description = '';
			foreach ( unserialize( OPERATORS ) as $op ) {
				$op[ 'operator' ] == $operator && $description = $op[ 'description' ];
			}
			$value = $this->get_value( $key, $operator, $value, 'F j, Y, H:i' );
			$text[ ] = sprintf( "'%s' %s '<b>%s</b>'", $this->get_field_title( $key ), $description, $value );
		}
		$this->description .= count( $text ) > 0 ? sprintf( ', where %s', implode( ' and ', $text ) ) : '';
		if ( $this->params[ HIGHLIGHT ] != '' ) {
			$text = array();
			foreach ( explode( '||', $this->params[ HIGHLIGHT ] ) as $highlight ) {
				list ( $key, $operator, $value ) = sscanf( $highlight, '%s %s %[^[]]' );
				$description = '';
				foreach ( unserialize( OPERATORS ) as $op ) {
					$op[ 'operator' ] == $operator && $description = $op[ 'description' ];
				}
				$value = $this->get_value( $key, $operator, $value, 'F j, Y, H:i' );
				$text[ ] = sprintf( "'%s' %s '<b>%s</b>'", $this->get_field_title( $key ), $description, $value );
			}
			$this->description .= count( $text ) > 0 ? sprintf( ', selected %s', implode( ' and ', $text ) ) : '';
		}
	}

	function output_conditions() {
		if ( !in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) ) return '';
		$html = array();
		$html[ ] = '<div class="conditions">';
		if ( !$this->params[ SHOW_CONDITIONS ] ) {
			$params = array( 
				NAME => $this->name, 
				SHOW_CONDITIONS => 1 );
			$html[ ] = sprintf( '<a href="%s#%s" title="Show conditions"><img border="0" src="inc/close.png">Conditions</a> (<b>%s</b>)', 
				$this->page->get_url( $params ), $this->name, count( $this->params[ CONDITIONS ] ) );
			$html[ ] = '</br>';
			$html[ ] = '</div>';
			$html[ ] = '</br>';
			return implode( PHP_EOL, $html );
		}
		$params = array( 
			NAME => $this->name, 
			SHOW_CONDITIONS => 0 );
		$html[ ] = sprintf( '<a href="%s#%s" title="Hide conditions"><img border="0" src="inc/open.png">Conditions</a> (<b>%s</b>)', 
			$this->page->get_url( $params ), $this->name, count( $this->params[ CONDITIONS ] ) );
		$html[ ] = '<form method="post" action="">';
		$html[ ] = sprintf( '<input type="hidden" name="%s" value="%s">', REPORT, $this->name );
		$html[ ] = '<div class="container">';
		$html[ ] = '<table class="conditions">';
		$html[ ] = '<thead>';
		$html[ ] = '<tr>';
		$html[ ] = '<th class="conditions" scope="cols">Field</th>';
		$html[ ] = '<th class="conditions center" scope="cols">Operator</th>';
		$html[ ] = '<th class="conditions" scope="cols">Value</th>';
		$html[ ] = '<th class="conditions center" scope="cols">Action</th>';
		$html[ ] = '</tr>';
		$html[ ] = '</thead>';
		$html[ ] = '<tbody>';
		$i = 1;
		foreach ( $this->params[ CONDITIONS ] as $condition ) {
			$html[ ] = '<tr>';
			list ( $key, $operator, $value ) = sscanf( trim( $condition ), '%s %s %[^[]]' );
			$html[ ] = '<td class="conditions">';
			$html[ ] = sprintf( '<select name="%s_%s">', FIELD, $i );
			foreach ( $this->fields as $field ) {
				if ( $this->get_field_title( $field->name ) == '' ) continue;
				$selected = $key == $field->name ? ' selected' : '';
				$html[ ] = sprintf( '<option value="%s"%s>%s</option>', $field->name, $selected, $this->get_field_title( $field->name ) );
			}
			$html[ ] = '</select></td>';
			$html[ ] = '<td class="conditions">';
			$html[ ] = sprintf( '<select class="center" name="%s_%s">', OPERATOR, $i );
			foreach ( unserialize( OPERATORS ) as $op ) {
				$selected = $operator == $op[ 'operator' ] ? ' selected' : '';
				$html[ ] = sprintf( '<option value="%s"%s>%s</option>', $op[ 'operator' ], $selected, $op[ 'description' ] );
			}
			$html[ ] = '</select></td>';
			$html[ ] = '<td class="conditions">';
			$html[ ] = sprintf( '<input type="text" size="%s" name="%s_%s" value="%s">', INPUT_SIZE, VALUE, $i, trim( $value, '\'' ) );
			$html[ ] = '</td>';
			$html[ ] = '<td class="conditions">';
			$html[ ] = sprintf( '<button type="submit" class="modify" name="%s" value="%s_%s">Modify</button>', ACTION, ADD, $i );
			$html[ ] = sprintf( '<button type="submit" class="remove" name="%s" value="%s_%s">Remove</button>', ACTION, REMOVE, $i );
			$html[ ] = '</td>';
			$html[ ] = '</tr>';
			$i++;
		}
		$html[ ] = '<tr>';
		$html[ ] = '<td class="conditions" scope="cols">';
		$html[ ] = sprintf( '<select name="%s_0">', FIELD );
		foreach ( $this->fields as $field ) {
			if ( $this->get_field_title( $field->name ) == '' ) continue;
			$html[ ] = sprintf( '<option value="%s">%s</option>', $field->name, $this->get_field_title( $field->name ) );
		}
		$html[ ] = '</select></td>';
		$html[ ] = '<td class="conditions" scope="cols">';
		$html[ ] = sprintf( '<select class="center" name="%s_0">', OPERATOR );
		foreach ( unserialize( OPERATORS ) as $op ) {
			$html[ ] = sprintf( '<option value="%s">%s</option>', $op[ 'operator' ], $op[ 'description' ] );
		}
		$html[ ] = '</select></td>';
		$html[ ] = sprintf( '<td class="conditions" scope="cols"><input type="text" size="%s" name="%s_0">', INPUT_SIZE, VALUE );
		$html[ ] = '</td>';
		$html[ ] = '<td class="conditions center" scope="cols">';
		$html[ ] = sprintf( '<button type="submit" class="insert" name="%s" value="%s_0">Insert</button>', ACTION, ADD );
		$html[ ] = '</td>';
		$html[ ] = '</tr>';
		$html[ ] = '</tbody>';
		$html[ ] = '</table>';
		$html[ ] = '</div>';
		$html[ ] = '</form>';
		$html[ ] = '</div>';
		$html[ ] = '</br>';
		return implode( PHP_EOL, $html );
	}

	function output_pagination() {
		$html = array();
		$html[ ] = '<div class="pagination">';
		if ( !in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) or ( count( $this->pivot ) != 0 ) or
			 ( $this->params[ SHOW_PAGINATION ] and $this->params[ DATAPAGE ] == 1 and ( count( $this->rows ) < $this->datapage_limit ) ) ) {
			$html[ ] = sprintf( '&nbsp;Rows: <b>%s</b>', count( $this->rows ) );
		 	$html[ ] = '</div>';
			return implode( PHP_EOL, $html );
		}
		if ( $this->params[ SHOW_PAGINATION ] and ( $this->params[ DATAPAGE ] > 1 or count( $this->rows ) >= $this->datapage_limit ) ) {
			$firstrow = ( ( $this->params[ DATAPAGE ] - 1 ) * $this->datapage_limit ) + 1;
			$lastrow = $firstrow + count( $this->rows ) - 1 - ( count( $this->rows ) > $this->datapage_limit );
			$params = array( 
				NAME => $this->name, 
				SHOW_PAGINATION => 0 );
			$html[ ] = sprintf( 
				'<a href="%s#%s" title="Show all rows at once"><img border="0" src="inc/close.png">' .
					 'Rows</a>: <b>%s</b> - <b>%s</b>. Page(s) : <span class="pagenav">', $this->page->get_url( $params ), $this->name, 
					$firstrow, $lastrow );
			if ( $this->params[ DATAPAGE ] > 1 ) {
				$params = array( 
					NAME => $this->name, 
					DATAPAGE => $this->params[ DATAPAGE ] - 1 );
				$html[ ] = sprintf( '<span class="page"><a href="%s#%s" title="Show previous page">&lt</a></span>', 
					$this->page->get_url( 

					$params ), $this->name );
			} else {
				$html[ ] = '<span class="inactivepage">&lt;</span>';
			}
			
			$pages = $this->params[ DATAPAGE ];
			for ( $i = 1; $i <= $pages; $i++ ) {
				if ( ( $pages - $i >= 10 ) and ( $i % 10 != 0 ) ) continue;
				if ( $i == $this->params[ DATAPAGE ] ) {
					$html[ ] = sprintf( '<span class="activepage">%s</span>', $i );
				} else {
					$params = array( 
						NAME => $this->name, 
						DATAPAGE => $i );
					$html[ ] = sprintf( '<span class="page"><a href="%s#%s" title="Show %s. page">%s</a></span>', 
						$this->page->get_url( 

						$params ), $this->name, $i, $i );
				}
			}
			
			if ( count( $this->rows ) > $this->datapage_limit ) {
				$params = array( 
					NAME => $this->name, 
					DATAPAGE => $this->params[ DATAPAGE ] + 1 );
				$html[ ] = sprintf( '<span class="page"><a href="%s#%s" title="Show next page">&gt</a></span>', 
					$this->page->get_url( $params ), 

					$this->name );
				array_pop( $this->rows );
			} else {
				$html[ ] = '<span class="inactivepage">&gt;</span>';
			}
		} else {
			$params = array( 
				NAME => $this->name, 
				SHOW_PAGINATION => 1 );
			$html[ ] = sprintf( '<a href="%s#%s" title="Show rows in pages"><img border="0" src="inc/open.png">Rows</a>: <b>%s</b>', 
				$this->page->get_url( $params ), $this->name, count( $this->rows ) );
		}
		$html[ ] = '</span></div>';
		return implode( PHP_EOL, $html );
	}

	function output_legend() {
		$html = array();
		$html[ ] = '<div class="legend">';
		if ( in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) and ( $this->params[ SHOW_LEGEND ] == 0 ) ) {
			$params = array( 
				NAME => $this->name, 
				SHOW_LEGEND => 1 );
			$html[ ] = sprintf( '<a href="%s#%s" title="Show legend"><img border="0" src="inc/close.png">Legend</a>', 
				$this->page->get_url( 

				$params ), $this->name );
			$html[ ] = '</br>';
			$html[ ] = '</div>';
			$html[ ] = '</br>';
			return implode( PHP_EOL, $html );
		}
		if ( in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) ) {
			$params = array( 
				NAME => $this->name, 
				SHOW_LEGEND => 0 );
			$html[ ] = sprintf( '<a href="%s#%s" title="Hide legend"><img border="0" src="inc/open.png">Legend</a>', 
				$this->page->get_url( 

				$params ), $this->name );
		} else {
			$html[ ] = 'Legend:';
		}
		$legend = array();
		foreach ( $this->styles as $style ) {
			$fields = array();
			foreach ( explode( ',', $style[ 'fields' ] ) as $field ) {
				$fields[ ] = $this->get_field_title( $field );
			}
			$fields = implode( ',', $fields );
			if ( $style[ 'fields' ] == '*' ) $fields = '';
			foreach ( explode( '||', $style[ 'conditions' ] ) as $condition ) {
				list ( $key, $operator, $value ) = sscanf( trim( $condition ), '%s %s %[^[]]' );
				foreach ( unserialize( OPERATORS ) as $op ) {
					$op[ 'operator' ] == $operator && $operator = $op[ 'description' ];
				}
				$legend[ $fields ][ $key ][ ] = array( 
					sprintf( '%s %s', $operator, $value ), 
					$style[ 'css' ], $style[ 'description' ] );
			}
		}
		foreach ( $legend as $fields => $keys ) {
			$html[ ] = '<div class="container">';
			$html[ ] = '<table class="legend">';
			$html[ ] = '<tbody>';
			$th = array();
			$tr = array();
			$th[ ] = '<tr>';
			$th[ ] = '<th class="legend" scope="cols">Field</th>';
			$tr[ ] = '<tr>';
			$tr[ ] = sprintf( '<th class="legend">%s</th>', $fields );
			foreach ( $keys as $field => $conditions ) {
				$th[ ] = sprintf( '<th class="legend center" colspan="%s">%s</th>', count( $conditions ), $this->get_field_title( $field ) );
				foreach ( $conditions as $condition ) {
					list ( $data, $css, $description ) = $condition;
					$tr[ ] = sprintf( '<td class="legend" style="%s">%s</td>', $css, $description );
				}
			}
			$th[ ] = '</tr>';
			$tr[ ] = '</tr>';
			$html[ ] = implode( PHP_EOL, $th );
			$html[ ] = implode( PHP_EOL, $tr );
			$html[ ] = '</tbody>';
			$html[ ] = '</table>';
			$html[ ] = '</div>';
			$html[ ] = '</br>';
		}
		$html[ ] = '</div>';
		$html = implode( PHP_EOL, $html );
		if ( !in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) ) {
			$html = preg_replace( array( 
				'"<a href(.*?)>"', 
				'"</a>"' ), array( 
				'', 
				'' ), $html );
		}
		return $html;
	}

	function is_condition( $field ) {
		$result = false;
		foreach ( $this->params[ CONDITIONS ] as $condition ) {
			list ( $key, $operator, $value ) = sscanf( $condition, '%s %s %[^[]]' );
			if ( $key == $field ) {
				$result = true;
			}
		}
		return $result;
	}

	function match_condition( $row, $conditions = array() ) {
		$result = false;
		$conditions == array() && $conditions = $this->params[ CONDITIONS ];
		foreach ( $conditions as $condition ) {
			list ( $key, $operator, $value2 ) = sscanf( trim( $condition ), '%s %s %[^[]]' );
			$value2 = str_replace( "'", '', str_replace( '"', "'", $value2 ) );
			foreach( $row as $rowkey => $rowvalue ) {
				$key == $rowkey && $value1 = $rowvalue;
				$value2 == sprintf( '%%%s', $rowkey ) && $value2 = $rowvalue;
			}
			if ( strtotime( $value1 ) and strtotime( $value2 ) and !preg_match( PATTERN_SESSIONID, $value1 ) ) {
				if ( $operator == 'REGEXP' ) {
					$value1 = str_replace( ', 00:00', '', date( 'Y-m-d H:i', strtotime( $value1 ) ) );
					$value2 = str_replace( ', 00:00', '', date( 'Y-m-d H:i', strtotime( $value2 ) ) );
					$value2 = str_replace( ', :00', '', $value2 );
				} else {
					$value1 = strtotime( $value1 );
					$value2 = strtotime( $value2 );
				}
			}
			switch ( $operator ) {
				case '==':
					$value1 == $value2 && $result = true;
					break;
				case '!=':
					$value1 != $value2 && $result = true;
					break;
				case '>=':
					$value1 >= $value2 && $result = true;
					break;
				case '<=':
					$value1 <= $value2 && $result = true;
					break;
				case '>':
					$value1 > $value2 && $result = true;
					break;
				case '<':
					$value1 < $value2 && $result = true;
					break;
				case 'REGEXP':
					preg_match( sprintf( '/%s/i', $value2 ), $value1 ) && $result = true;
					break;
			}
		}
		return $result;
	}

	function is_highlighted( $row ) {
		if ( $this->params[ HIGHLIGHT ] == '' ) return false;
		$result = true;
		foreach ( explode( '||', $this->params[ HIGHLIGHT ] ) as $highlight ) {
			list ( $key, $operator, $value2 ) = sscanf( trim( $highlight ), '%s %s %[^[]]' );
			$value2 = str_replace( "'", '', str_replace( '"', "'", $value2 ) );
			foreach( $row as $rowkey => $rowvalue ) {
				$key == $rowkey && $value1 = $rowvalue;
				$value2 == sprintf( '%%%s', $rowkey ) && $value2 = $rowvalue;
			}
			$value1 != $value2 && $result = false;
		}
		return $result;
	}

	function get_style( $field, $row ) {
		$styles = array();
		foreach ( $this->styles as $style ) {
			if ( ( $style[ 'fields' ] == '*' ) or ( in_array( $field, explode( ',', $style[ 'fields' ] ) ) ) ) {
				if ( $this->match_condition( $row, explode( '||', $style[ 'conditions' ] ) ) ) {
					foreach ( explode( ';', $style[ 'css' ] ) as $css ) {
						if ( $css == '' ) continue;
						if ( !stristr( $css, ':' ) ) continue;
						list ( $key, $value ) = explode( ':', $css );
						$styles[ $key ] = trim( $value, ';' );
					}
				}
			}
		}
		$style = '';
		foreach ( $styles as $key => $value ) {
			$style .= sprintf( '%s:%s;', $key, $value );
		}
		return $style;
	}

	function format_value( $value, $type ) {
		if ( $value == '' ) return '';
		if ( in_array( $type, array( 
			'int' ) ) ) {
			$value = number_format( $value, 0, ',', ' ' );
		}
		if ( in_array( $type, array( 
			'real' ) ) ) {
			$value = number_format( $value, 1, ',', ' ' );
		}
		if ( in_array( $type, array( 
			'blob' ) ) ) {
			str_replace( "\n", '<br>', $value );
		}
		if ( in_array( $type, array( 
			'date', 
			'time', 
			'datetime', 
			'timestamp' ) ) ) {
			$value = str_replace( date( 'Y-m-d', time( ) - ( 24 * 60 * 60 ) ), 'Yesterday', $value );
			$value = str_replace( date( 'Y-m-d', time( ) ), 'Today', $value );
			$value = str_replace( date( 'Y-m-d', time( ) + ( 24 * 60 * 60 ) ), 'Tomorrow', $value );
		}
		return $value;
	}

	function get_link( $field, $row, $value ) {
		if ( !isset( $this->links[ $field->name ] ) or $value == '' or !in_array( $this->page->params[ MODE ], array( INTERACTIVE ) ) ) return $value;
		list ( $link, $highlight, $link_conditions, $title ) = $this->links[ $field->name ];
		foreach ( $row as $key => $val ) {
			if ( strpos( $link_conditions, sprintf( '%%%s', $key ) ) > 0 ) $link_conditions = str_replace( sprintf( '%%%s', $key ), $val, 
				trim( $link_conditions ) );
			if ( strpos( $title, sprintf( '%%%s', $key ) ) > 0 ) $title = str_replace( sprintf( '%%%s', $key ), $val, $title );
			if ( strpos( $highlight, sprintf( '%%%s', $key ) ) > 0 ) $highlight = str_replace( sprintf( '%%%s', $key ), $val, 
				trim( $highlight ) );
		}
		$link == $this->name && $highlight = '';
		$params = array( 
			NAME => $this->name, 
			HIGHLIGHT => $highlight, 
			LINK => $link, 
			LINK_CONDITIONS => $link_conditions );
		$html = sprintf( '<a href="%s#%s" title="%s">%s</a>', $this->page->get_url( $params ), $link, $title, $value );
		if ( $this->show_open_in_new_window and $link != $this->name and !$this->simple ) {
			$new = array( 
				NAME => $this->name, 
				HIGHLIGHT => '', 
				LINK => $link, 
				LINK_CONDITIONS => $link_conditions );
			$html .= sprintf( '<a href="%s" title="%s in new window"><img border="0" src="inc/new.png"></a>', $this->page->get_url( $new ), 
				
				$title );
		}
		return $html;
	}

	function is_ordered( $field ) {
		return in_array( $field, $this->params[ ORDERS ] ) or in_array( sprintf( '%s-', $field ), $this->params[ ORDERS ] );
	}

	function get_order( $field ) {
		$title = $this->get_field_title( $field );
		if ( !in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) or $this->simple ) return $title;
		$params = array( 
			NAME => $this->name, 
			ORDERS => $field );
		$html = '<a href="%s#%s" title="Order by \'%s\' ascending">%s</a>';
		if ( in_array( sprintf( '%s', $field ), $this->params[ ORDERS ] ) ) {
			$params[ ORDERS ] = sprintf( '*%s', $field );
			$html = '<a href="%s#%s" title="Order by \'%s\' descending">%s</a>&#x25B2;';
		}
		if ( in_array( sprintf( '%s-', $field ), $this->params[ ORDERS ] ) ) {
			$params[ ORDERS ] = sprintf( '-%s-', $field );
			$html = '<a href="%s#%s" title="Remove \'%s\' ordering">%s</a>&#x25BC;';
		}
		$html = sprintf( $html, $this->page->get_url( $params ), $this->name, $title, $title );
		return $html;
	}

	function output() {
		$html = array();
		if ( $this->page->params[ MODE ] == CSV ) {
			$line = array();
			foreach ( $this->fields as $field ) {
				if ( $this->get_field_title( $field->name ) == '' ) continue;
				$line[ ] = $field->name;
			}
			$html[ ] = implode( $this->page->application->config[ 'DELIMITER' ], $line );
			foreach ( $this->rows as $row ) {
				$line = array();
				foreach ( $this->fields as $field ) {
					if ( $this->get_field_title( $field->name ) == '' ) continue;
					$quote = $this->page->application->config[ 'QUOTE' ];
					if ( in_array( $field->type, array( 
						'int', 
						'real' ) ) ) $quote = '';
					$line[ ] = sprintf( '%s%s%s', $quote, $row[ $field->name ], $quote );
				}
				$html[ ] = implode( $this->page->application->config[ 'DELIMITER' ], $line );
			}
			return implode( PHP_EOL, $html );
		}
		$html[ ] = sprintf( '<a name="%s"></a>', $this->name );
		$html[ ] = '<div class="title">';
		if ( in_array( $this->page->params[ MODE ], array( INTERACTIVE, ADMIN ) ) and !$this->simple ) {
			$params = array( 
				NAME => $this->name, 
				REMOVE => 1 );
			$html[ ] = sprintf( '<a href="%s" title="Remove report \'%s\' from page">%s</a>', $this->page->get_url( $params ), 
				$this->title, 

				$this->title );
		} else {
			$html[ ] = $this->title;
		}
		$html[ ] = '</div>';
		$html[ ] = '<div class="description">&nbsp;';
		$html[ ] = $this->description;
		$html[ ] = $this->params[ REQUERY ] == 0 ? '<span class="small"> (cached) </span>' : '';
		$html[ ] = '</div>';
		$html[ ] = '</br>';
		if ( !$this->simple ) {
			if ( ( count( $this->rows ) > 0 ) or ( count( $this->params[ CONDITIONS ] ) > 0 ) ) $html[ ] = $this->output_conditions( );
			$html[ ] = $this->output_pagination( );
		}
		
		if ( $this->page->params[ MODE ] ==  ADMIN and $this->fields[ 0 ]->name == ID ) {
			$html[ ] = '<form method="post" action="">';
			$html[ ] ='<div>';
			$html[ ] = sprintf( '<input type="hidden" name="%s" value="%s">', REPORT, $this->name );
			$html[ ] = '</br>';
			if ( empty( $this->params[ ID ] ) ) {
				$html[ ] = sprintf( '&nbsp;New row: ' );
				$html[ ] = sprintf( '<button type="submit" class="insert" name="%s" value="%s">Add</button>', ACTION, ADD );
			} else {
				$html[ ] = sprintf( '&nbsp;Selected row (ID:<b>%s</b>): ', $this->params[ ID ] );
				$html[ ] = sprintf( '<button type="submit" class="modify" name="%s" value="%s">Update</button>', ACTION, MODIFY );
				$html[ ] = sprintf( '<button type="submit" class="remove" name="%s" value="%s">Obsolete</button>', ACTION, REMOVE );
			}
			if ( count( $this->rows ) == 0 ) {
				$html[ ] ='</form>';
			}
			$html[ ] ='</div>';
		}
		if ( count( $this->rows ) == 0 ) {
			$html[ ] = '</br>';
			return implode( PHP_EOL, $html );
		}
		
		$html[ ] = '<div class="container">';
		$html[ ] = '<table class="report">';
		
		if ( count( $this->pivot ) > 0 ) {
			list ( $pivotx, $pivoty ) = $this->pivot;
			$pivotx_values = array();
			$pivoty_values = array();
			foreach ( $this->rows as $row ) {
				$pivotx_values[ ] = $row[ $pivotx ];
				$pivoty_values[ ] = $row[ $pivoty ];
			}
			$pivotx_values = array_unique( $pivotx_values );
			$pivoty_values = array_unique( $pivoty_values );
			asort( $pivoty_values );
			$values = array();
			foreach ( $pivotx_values as $pivotx_value ) {
				foreach ( $pivoty_values as $pivoty_value ) {
					foreach ( $this->fields as $field ) {
						if ( $field->name == $pivotx ) continue;
						if ( $field->name == $pivoty ) continue;
						foreach ( $this->rows as $row ) {
							$row[ $pivotx ] == $pivotx_value and $row[ $pivoty ] == $pivoty_value && $values[ $pivotx_value ][ 

							$pivoty_value ][ $field->name ] = $row[ $field->name ];
						}
					}
				}
			}
		} else {
			$pivotx = $pivoty = '';
			$pivotx_values = array( 
				'' );
			$pivoty_values = array( 
				'' );
		}
		$html[ ] = '<thead>';
		
		if ( $pivoty != '' ) {
			$html[ ] = '<tr>';
			$class = array( 
				'report' );
			$this->is_condition( $pivoty ) && $class = array( 
				'conditions' );
			$this->is_ordered( $pivoty ) && $class[ ] = 'order';
			$order = $this->get_order( $pivoty );
			$html[ ] = sprintf( '<th scope="col" class="%s">%s</th>', implode( ' ', $class ), $order );
			foreach ( $pivoty_values as $header ) {
				$html[ ] = sprintf( '<th scope="col" class="%s" colspan="%s">%s</th>', implode( ' ', $class ), count( $this->fields ) - 2, 
					
					$header );
			}
			$html[ ] = '</tr>';
		}

		$html[ ] = '<tr>';
		if ( $pivotx != '' ) {
			$class = array( 
				'report' );
			$this->is_condition( $pivotx ) && $class = array( 
				'conditions' );
			$this->is_ordered( $pivotx ) && $class[ ] = 'order';
			$order = $this->get_order( $pivotx );
			$html[ ] = sprintf( '<th scope="col" class="%s">%s</th>', implode( ' ', $class ), $order );
		}
		foreach ( $pivoty_values as $title ) {
			foreach ( $this->fields as $field ) {
				if ( in_array( $field->name, array( 
					$pivotx, 
					$pivoty ) ) ) continue;
				if ( $this->get_field_title( $field->name ) == '' ) continue;
				$class = array( 
					'report' );
				$this->is_condition( $field->name ) && $class = array( 
					'conditions' );
				$this->is_ordered( $field->name ) && $class[ ] = 'order';
				$order = $this->get_order( $field->name );
				$html[ ] = sprintf( '<th scope="col" class="%s">%s</th>', implode( ' ', $class ), $order );
			}
		}
		$html[ ] = '</tr>';
		$html[ ] = '</thead>';
		
		if ( $this->show_footer ) {
			$html[ ] = '<tfoot>';
			$html[ ] = '<tr>';
			if ( $pivotx != '' ) {
				$class = sprintf( 'report %s small', $this->fields[ 0 ]->type );
				$title = $this->fields[ 0 ]->type;
				$html[ ] = sprintf( '<th class="%s">%s</th>', $class, $title );
			}
			foreach ( $pivoty_values as $pivoty_value ) {
				foreach ( $this->fields as $field ) {
					if ( in_array( $field->name, array( 
						$pivotx, 
						$pivoty ) ) ) continue;
					if ( $this->get_field_title( $field->name ) == '' ) continue;
					$class = sprintf( 'report %s small', $field->type );
					$title = $field->type;
					$html[ ] = sprintf( '<th class="%s">%s</th>', $class, $title );
				}
			}
			$html[ ] = '</tr>';
			$html[ ] = '</tfoot>';
		}
		
		$html[ ] = '<tbody>';
		foreach ( $pivotx_values as $pivotx_value ) {
			$row_html = array();
			if ( $pivotx != '' ) {
				$type = $this->fields[ 0 ]->type;
				$value = $pivotx_value;
				$value = $this->format_value( $value, $type );
				$row_html[ ] = '<tr>';
				$class = array( 
					'report', 
					$type );
				$row_html[ ] = sprintf( '<th class="%s">%s</th>', implode( ' ', $class ), $value );
			}
			foreach ( $pivoty_values as $pivoty_value ) {
				$rows = array();
				if ( $pivoty != '' ) {
					if ( isset( $values[ $pivotx_value ][ $pivoty_value ] ) ) {
						$rows[ ] = $values[ $pivotx_value ][ $pivoty_value ];
						$rows[ 0 ][ $pivotx ] = $pivotx_value;
						$rows[ 0 ][ $pivoty ] = $pivoty_value;
					} else {
						$rows[ ] = array();
						foreach ( $this->fields as $field ) {
							$rows[ 0 ][ $field->name ] = '';
						}
					}
				} else {
					$rows = $this->rows;
				}
				foreach ( $rows as $row ) {
					$pivoty == '' && $row_html[ ] = '<tr>';
					foreach ( $this->fields as $field ) {
						if ( in_array( $field->name, array( 
							$pivotx, 
							$pivoty ) ) ) continue;
						if ( $this->get_field_title( $field->name ) == '' ) continue;
						$type = $field->type;
						$value = $row[ $field->name ];
						$class = array( );
						$class[ ] = $type;
						if ( $this->is_highlighted( $row ) ) {
							$class[ ] = 'highlight';
							$style = '';
						} else {
							$style = $this->get_style( $field->name, $row );
						}
						if ( !in_array( $style, $this->page->styles ) ) $this->page->styles[ ] = $style;
						$stylekey = sprintf( 's%s', array_search( $style, $this->page->styles ) );
						if ( $this->page->params[ MODE ] ==  ADMIN and !empty( $row[ ID ] ) and !empty( $this->params[ ID ] ) and $row[ ID ] == $this->params[ ID ] and ( empty( $this->links[ $field->name ] ) or ( $this->links[ $field->name ][ 0 ] != READONLY ) ) ) {
							$row_html[ ] = sprintf( '<td class="%s">', $stylekey );
							!empty( $this->links[ $field->name ] ) and $this->links[ $field->name ][ 0 ] == PRIMARY && $row_html[ ] = sprintf( '<input type="hidden" name="%s" value="%s">', ID, $row[ ID ] );
							if ( !empty( $this->links[ $field->name ] ) and $this->links[ $field->name ][ 0 ] == COMBOBOX ) {
								$items = array( );
								switch( $this->links[ $field->name ][ 1 ] ) {
									case 'CELLSERVERS': $items = $this->page->cellservers;break;
									case 'CUSTOMERS': $items = $this->page->customers;break;
									case 'TIMEPERIODS': $items = $this->page->timeperiods;break;
									default: foreach ( explode( '|', $this->links[ $field->name ][ 1 ] ) as $item ) {
										$items[] = array( 'name' => $item, 'value' => $item );
									} break;
								}
								array_unshift( $items, array( 'name' => '', 'value' => '' ) );
								$row_html[ ] = sprintf( '<select class="admin" name="%s">', $field->name );
								foreach ( $items as $item ) {
									$selected = $value == $item[ 'value' ] ? ' selected' : '';
									$row_html[ ] = sprintf( '<option class="admin" value="%s"%s>%s</option>', $item[ 'value' ], $selected, $item[ 'name' ] );
								}
								$row_html[ ] = '</select>';
							} else { 
								$row_html[ ] = sprintf( '<input class="admin" type="text" name="%s" value="%s">', $field->name, trim( $value, '\'' ) );
							}
							$row_html[ ] = '</td>';
						} else {
							$value = $this->format_value( $value, $type );
							if ( $this->page->params[ MODE ] ==  ADMIN and !empty( $row[ ID ] ) and !empty( $this->links[ $field->name ] ) and $this->links[ $field->name ][ 0 ] == PRIMARY ) {
								$params = array( NAME => $this->name, ID => $row[ ID ] );
								$value = sprintf( '<a href="%s" title="Modify this record">%s</a>', $this->page->get_url( $params ), $value );						
							} else {
								$value = $this->get_link( $field, $row, $value );
							}
							$row_html[ ] = sprintf( '<td class="%s %s">%s</td>', $stylekey, implode( ' ', $class ), $value );
						}
					}
					$pivoty == '' && $row_html[ ] = '</tr>';
				}
			}
			$pivotx != '' && $row_html[ ] = '</tr>';
			$html[ ] = implode( PHP_EOL, $row_html );
		}
		$html[ ] = '</tbody>';
		$html[ ] = '</table>';
		if ( $this->page->params[ MODE ] ==  ADMIN and $this->fields[ 0 ]->name == ID ) {
			$html[ ] = '</form>';
		}
		$html[ ] = '</div>';
		$html[ ] = '</br>';
		!$this->simple and count( $this->rows ) > 0 &&  $html[ ] = $this->output_legend( );
		return implode( PHP_EOL, $html );
	}
}
