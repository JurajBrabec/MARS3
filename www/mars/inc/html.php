<?php

/*
 * MARS 3.0 MYSQL DATABASE MANAGEMENT PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'ID', 'id' );
define( 'NAME', 'name' );
define( 'CAPTION', 'caption' );
define( 'RENDERED', 'rendered' );
define( 'ONCLICK', 'onclick' );
define( 'TYPE', 'type' );
define( 'VALUE', 'value' );
define( 'TEXT', 'text' );
define( 'SELECTED', 'selected' );
define( 'PASSWORD', 'password' );
define( 'BUTTON', 'button' );
define( 'SUBMIT', 'submit' );
define( 'RESET', 'reset' );
define( 'METHOD', 'method' );
define( 'POST', 'post' );
define( 'GET', 'get' );
define( 'CLASSNAME', 'class' );
define( 'STYLE', 'style' );
define( 'HREF', 'href' );
define( 'TARGET', 'target' );
define( 'ALT', 'alt' );
define( 'SRC', 'src' );
define( 'CONTENT', 'content' );
define( 'HTTP_EQUIV', 'http-equiv' );
define( 'CHARSET', 'charset' );
define( 'ICON', 'icon' );
define( 'STYLESHEET', 'stylesheet' );
define( 'REL', 'rel' );
define( 'IMAGEXICON', 'image/x-icon' );
define( 'TEXTCSS', 'text/css' );
define( 'TEXTJAVASCRIPT', 'text/javascript' );

define( 'BOLD', 'bold' );

define( 'PAGE_HEADER', 'Content-type: text/html; charset=utf-8' );
define( 'SESSION_CACHE_LIMITER', 'private, must-revalidate' );
define( 'SESSION_CACHE_EXPIRE', 5 * 60 );

define( 'SID', 'session' );
define( 'TITLE', 'title' );
define( 'PARAMETERS', 'parameters' );
define( 'ACTIONS', 'actions' );
define( 'OBJECTS', 'objects' );
define( 'MESSAGE', 'message' );

class itemlist {
	protected $items = array( );

	public function __construct( $source = NULL) {
		$this->items = array( );
		if ( is_string( $source ) ) {
			$this->items = $this->decode( $source );
		}
		if ( is_array( $source ) ) {
			$this->items = $source;
		}
	}

	public function set( $item = array ( ) ) {
		foreach ( $item as $key=>$value ) $this->items[ $key ] = $value;
	}

	public function get( $item = NULL ) {
		if ( $item === NULL ) return $this->items;
		$result = empty( $this->items[ $item ] ) ? NULL :$this->items[ $item ];
		return $result;
	}

	public function encode( ) {
		return serialize( $this->items );
	}

	public function decode( $string ) {
		return unserialize( $string );
	}

}

class indexeditemlist extends itemlist {

	public function set( $item = array( ) ) {
		$item = serialize( $item );
		if ( !in_array( $item, $this->items, true ) ) $this->items[] = $item;
		return array_search( $item, $this->items, true ) + 1;
	}

	public function get( $id = NULL ) {
		if ( $id === NULL ) return $this->items;
		return unserialize( $this->items[ $id - 1 ] );
	}

}

class html_object {
	protected $closed = TRUE;
	protected $tag_open = '';
	protected $tag_close = '';
	protected $parameters;
	protected $objects = array( );
	protected $attributes = array( );
	protected $implode = FALSE;

	public function __construct( $parameters = array( ) ) {
		$this->parameters = new itemlist( $parameters );
		$this->objects = array( );
		$this->set_attributes( array( ID, CLASSNAME, STYLE, ONCLICK ) );
	}

	public function get( $parameter ) {
		return $this->parameters->get( $parameter );
	}

	public function set( $parameter ) {
		$this->parameters->set( $parameter );
	}

	public function add( $object ) {
		if ( is_string( $object ) ) $object = new html_text( $object );
		$this->objects[ ] = $object;
		return $object;
	}

	public function close( ) {
		$this->closed = TRUE;
		foreach( $this->objects as $object ) {
			$object->close( );
		}
	}
	
	public function implode( $implode = TRUE ) {
		$this->implode = $implode;
	}
	
	public function set_attributes( $attributes = array( ) ) {
		foreach( $attributes as $attribute ) {
			in_array( $attribute, $this->attributes ) || $this->attributes[ ] = $attribute;
		}
	}
	
	public function get_attributes( ) {
		$result = '';
		foreach( $this->attributes as $attribute ) { 
			$value = $this->get( $attribute );
			if ( !is_null( $value ) ) {
				$pattern = in_array( $attribute, array( SELECTED ) ) ? '%s' : '%s="%s"';
				$result .= ' ';
				$result .= sprintf( $pattern, $attribute, $value );
			}
		}
		return $result;
	}

	public function get_object( $id = NULL ) {
		$result = NULL;
		if ( $id === NULL ) $result = $this->objects;
		if ( is_numeric( $id ) ) $result = $this->objects[ $id ];
		if ( is_string( $id ) ) foreach ( $this->objects as $object ) {
			if ( $object->get( ID ) == $id or $object->get( NAME ) == $id ) $result = $object;
		}
		return $result;
	}
	
	public function output( ) {
		$html = array( );
		foreach( $this->objects as $object ) {
			$output = $object->output( );
			$html = array_merge( $html, is_array( $output ) ? $output : array( $output ) );
		}
		$html = array_filter( array_merge( $this->output_open( ), $html, $this->output_close( ) ) );  
		$this->implode && $html = array( implode( '', $html ) );
		return $html;
	}

	public function output_open( ){
		$html = array( );
		$html[ ] = str_replace('>', sprintf( '%s>', $this->get_attributes( ) ), $this->tag_open );
		$html[ ] = $this->get( CAPTION );
		return $html;
	}
	
	public function output_close( ){
		$html = array( );
		if ( $this->closed ) {
			$html[ ] = $this->tag_close;
		}
		return $html;
	}
}

class html_document extends html_object {
	protected $closed = FALSE;
	protected $tag_open = '<!DOCTYPE html><html>';
	protected $tag_close = '</html>';
}

class html_body extends html_object {
	protected $closed = FALSE;
	protected $tag_open = '<body>';
	protected $tag_close = '</body>';
}

class html_head extends html_object {
	protected $tag_open = '<head>';
	protected $tag_close = '</head>';
}

class html_meta extends html_object {
	protected $implode = TRUE;
	protected $tag_open = '<meta>';
	protected $tag_close = '';
	
	public function __construct( $parameters = array( ) ) {
		parent::__construct( $parameters );
		$this->set_attributes( array ( HTTP_EQUIV, CONTENT, NAME, CHARSET ) );
	}
}

class html_link extends html_object {
	protected $implode = TRUE;
	protected $tag_open = '<link>';
	protected $tag_close = '';
	
	public function __construct( $rel, $type, $href, $parameters = array( ) ) {
		parent::__construct( $parameters );
		$this->set( array( REL => $rel, TYPE => $type, HREF => $href ) );
		$this->set_attributes( array ( REL, TYPE, HREF ) );
	}
}

class html_style extends html_object {
	protected $tag_open = '<style>';
	protected $tag_close = '</style>';
	
	public function __construct( $type = TEXTCSS, $parameters = array( ) ) {
		parent::__construct( $parameters );
		$this->set( array( TYPE => $type ) );
		$this->set_attributes( array ( TYPE ) );
	}
}

class html_script extends html_object {
	protected $tag_open = '<script>';
	protected $tag_close = '</script>';
	
	public function __construct( $type = TEXTJAVASCRIPT, $parameters = array( ) ) {
		parent::__construct( $parameters );
		$this->set( array( TYPE => $type ) );
		$this->set_attributes( array ( TYPE ) );
	}
}

class html_text extends html_object {
	protected $implode = TRUE;
	
	public function __construct( $caption = '', $parameters = array( ) ) {
		if ( is_array( $caption ) and $parameters == array( ) ) {
			$parameters = $caption;
			$caption = '';
		}
		parent::__construct( $parameters );
		if ( is_object( $caption ) and is_a( $caption, get_parent_class( ) ) ) {
			$this->add( $caption );
			$caption = '';
		}
		empty( $caption ) || $this->set( array( CAPTION => $caption ) );
	}
}

class html_title extends html_text {
	protected $tag_open = '<title>';
	protected $tag_close = '</title>';
}

class html_span extends html_text {
	protected $tag_open = '<span>';
	protected $tag_close = '</span>';
}

class html_div extends html_object {
	protected $closed = FALSE;
	protected $tag_open = '<div>';
	protected $tag_close = '</div>';
	
}

class html_input extends html_object {
	protected $implode = TRUE;
	protected $tag_open = '<input>';
	protected $tag_close = '';
	
	public function __construct( $name, $value, $type = TEXT, $parameters = array( ) ) {
		if ( is_array( $type ) and $parameters == array( ) ) {
			$parameters = $type;
			$type = TEXT;
		}
		parent::__construct( $parameters );
		$this->set( array( NAME => $name, VALUE => $value, TYPE => $type ) );
		$this->set_attributes( array ( NAME, VALUE, TYPE ) );
	}
}

class html_option extends html_text {
	protected $implode = TRUE;
	protected $tag_open = '<option>';
	protected $tag_close = '</option>';
	
	public function __construct( $caption, $selected = FALSE, $parameters = array( ) ) {
		if ( is_array( $selected ) and $parameters == array( ) ) {
			$parameters = $selected;
			$selected = FALSE;
		}
		parent::__construct( $caption, $parameters );
		$selected && $this->set( array( SELECTED => 1 ) );
		$this->set( array( VALUE => $caption ) );
		$this->set_attributes( array ( VALUE, SELECTED ) );
	}
}

class html_select extends html_object {
	protected $tag_open = '<select>';
	protected $tag_close = '</select>';
	
	public function __construct( $name, $options = array( ), $value = NULL, $parameters = array( ) ) {
		parent::__construct( $parameters );
		$this->set( array( NAME => $name, VALUE => $value ) );
		$this->set_attributes( array ( NAME ) );
		foreach( $options as $option ) {
			$this->add( new html_option( $option, $option == $value ) );
		}
	}
}

class html_button extends html_object {
	protected $implode = TRUE;
	protected $tag_open = '<button>';
	protected $tag_close = '</button>';
	
	public function __construct( $name, $caption, $value, $type = SUBMIT, $parameters = array( ) ) {
		if ( is_array( $type ) and $parameters == array( ) ) {
			$parameters = $type;
			$type = SUBMIT;
		}
		parent::__construct( $parameters );
		$this->set( array( NAME => $name, CAPTION => $caption, VALUE => $value, TYPE => $type ) );
		$this->set_attributes( array ( NAME, VALUE, TYPE ) );
	}
}

class html_form extends html_object {
	protected $tag_open = '<form>';
	protected $tag_close = '</form>';
	
	public function __construct( $name, $caption, $method = POST, $parameters = array( ) ) {
		if ( is_array( $method ) and $parameters == array( ) ) {
			$parameters = $method;
			$method = POST;
		}
		parent::__construct( $parameters );
		$this->set( array( NAME => $name, CAPTION => $caption, METHOD =>$method ) );
		$this->set_attributes( array ( NAME, METHOD ) );
	}
	
	public function output_open( ) {
		$html = parent::output_open( );
		$caption = $this->get( CAPTION );
		!empty( $caption ) && array_pop( $html );
		$html[ ] = '<fieldset>';
		!empty( $caption ) && $html[ ] = sprintf( '<legend>%s</legend>', $caption );
		return $html;
	}

	public function output_close( ) {
		$html = parent::output_close( );
		$this->closed && array_unshift( $html, '</fieldset>' );
		return $html;
	}
}

class html_listitem extends html_text {
	protected $implode = TRUE;
	protected $tag_open = '<li>';
	protected $tag_close = '</li>';
}

class html_orderedlist extends html_object {
	protected $tag_open = '<ol>';
	protected $tag_close = '</ol>';
}

class html_unorderedlist extends html_object {
	protected $tag_open = '<ul>';
	protected $tag_close = '</ul>';
}


class html_anchor extends html_text {
	protected $implode = TRUE;
	protected $tag_open = '<a>';
	protected $tag_close = '</a>';
	
	public function __construct( $caption, $href, $parameters = array( ) ) {
		parent::__construct( $caption, $parameters );
		$this->set( array( HREF => $href ) );
		$this->set_attributes( array ( HREF, TARGET ) );
	}
}

class html_img extends html_object {
	protected $implode = TRUE;
	protected $tag_open = '<img>';
	protected $tag_close = '';
	
	public function __construct( $src, $alt = '', $parameters = array( ) ) {
		if ( is_array( $alt ) and $parameters == array( ) ) {
			$parameters = $alt;
			$alt = '';
		}
		parent::__construct( $parameters );
		$this->set( array( ALT => $alt, SRC => $src ) );
		$this->set_attributes( array ( ALT, SRC ) );
	}
}

class html_tabledata extends html_text {
	protected $implode = TRUE;
	protected $tag_open = '<td>';
	protected $tag_close = '</td>';
}

class html_tableheader extends html_text {
	protected $implode = TRUE;
	protected $tag_open = '<th>';
	protected $tag_close = '</th>';
}

class html_tablefooter extends html_text {
	protected $implode = TRUE;
	protected $tag_open = '<td>';
	protected $tag_close = '</td>';
}

class html_tablerow extends html_object {
	protected $tag_open = '<tr>';
	protected $tag_close = '</tr>';
	
	public function add( $object, $parameters = array( ) ) {
		$result = parent::add( new html_tabledata( $object ) );
		$result->set( $parameters );
		return $result;
	}
}

class html_tablehead extends html_object {
	protected $tag_open = '<thead>';
	protected $tag_close = '</thead>';

	public function add( $object, $parameters = array( ) ) {
		$result = parent::add( new html_tableheader( $object ) );
		$result->set( $parameters );
		return $result;
	}
	
	public function output_open( ) {
		$html = parent::output_open( );
		$html[ ] = '<tr>';
		return $html;
	}

	public function output_close( ) {
		$html = array( );
		$html = parent::output_close( );
		$this->closed && array_unshift( $html, '</tr>' );
		return $html;
	}
}

class html_tablefoot extends html_object {
	protected $tag_open = '<tfoot>';
	protected $tag_close = '</tfoot>';
	
	public function add( $object, $parameters = array( ) ) {
		$result = parent::add( new html_tablefooter( $object ) );
		$result->set( $parameters );
		return $result;
	}

	public function output_open( ) {
		$html = parent::output_open( );
		$html[ ] = '<tr>';
		return $html;
	}

	public function output_close( ) {
		$html = parent::output_close( );
		$this->closed && array_unshift( $html, '</tr>' );
		return $html;
	}
}

class html_table extends html_object {
	protected $closed = FALSE;
	protected $tag_open = '<table>';
	protected $tag_close = '</table>';
	
	public function add_row( ) {
		$row = $this->add( new html_tablerow( ) );
		return $row;
	}
	
	public function head( ) {
		$result = NULL;
		foreach ( $this->objects as $object ) {
			if ( is_a( $object, 'html_tablehead' ) ) $result = $object;
		}
		if ( $result == NULL ) {
			$result = $this->add( new html_tablehead( ) );
		}
		return $result;
	}
	
	public function foot( ) {
		$result = NULL;
		foreach ( $this->objects as $object ) {
			if ( is_a( $object, 'html_tablefoot' ) ) $result = $object;
		}
		$result == NULL && $result = $this->add( new html_tablefoot( ) );
		return $result;
	}
}

class html_page extends html_object {
	protected $closed = FALSE;
	protected $actions;
	protected $html_head;
	protected $html_body;
	protected $last_object = NULL;
	protected $output_pointer = 0;

	public function __construct( $title, $parameters = array( ) ) {
		parent::__construct( $parameters );
		if ( !empty( $_REQUEST[ SID ] ) ) session_id( $_REQUEST[ SID ] );
		session_start( );
		$sid = session_id( );
		$this->actions = new indexeditemlist( );
		$this->set( array( SID => $sid, TITLE => $title ) );
		$html = $this->add( new html_document( ) );
		$this->html_head = $html->add( new html_head( $this->get( TITLE ) ) );
		$this->head( )->add( new html_title( $this->get( TITLE ) ) );
		$this->html_body = $html->add( new html_body( ) );
		if ( empty( $_REQUEST[ SID ] ) or $_REQUEST[ SID ] != $sid ) {
			session_destroy( );
			session_start( );
			$this->reload( );
		}
		session_cache_limiter( SESSION_CACHE_LIMITER );
		session_cache_expire( SESSION_CACHE_EXPIRE );
	}

	public function get( $parameter, $object = NULL ) {
		$result = NULL;
		$object === NULL && $result = parent::get( $parameter );
		( is_numeric( $object ) || is_string( $object ) ) && $result = $this->get_object( $object )->get( $parameter );
		return $result;
	}

	public function set( $parameter, $object = NULL ) {
		$object === NULL && parent::set( $parameter );
		( is_numeric( $object ) || is_string( $object ) ) && $this->get_object( $object )->set( $parameter );
	}

	public function initialize( ) {
		$this->start_buffering( );
		if ( !empty( $_SESSION[ PARAMETERS ] ) ) {
			$this->parameters = new itemlist( $_SESSION[ PARAMETERS ] );
		}
		if ( !empty( $_REQUEST[ ACTIONS ] ) ) {
			$this->actions = new indexeditemlist( $_SESSION[ ACTIONS ] );
			$item = $this->actions->get( $_REQUEST[ ACTIONS ] );
			$object = empty( $_REQUEST[ OBJECTS ] ) ? NULL : $_REQUEST[ OBJECTS ];
			foreach ( $item as $key => $value ) {
				isset( $_REQUEST[ $value ] ) && $item[ $key ] = $_REQUEST[ $value ];
			}
			$this->execute_action( $item );
			$this->reload( );
			die( );
		}
		unset( $_SESSION[ ACTIONS ] );
	}

	public function head( ) {
		return $this->html_head;
	}

	public function body( ) {
		return $this->html_body;
	}

	public function get_url( $item = SID, $object = NULL ) {
		$delimiter = '?';
		$result = $_SERVER[ 'PHP_SELF' ];
		if ( $item == SID or is_array( $item ) ) {
			$result .= $delimiter . sprintf( '%s=%s', SID, $this->get( SID ) );
			$delimiter = '&';
		}
		if ( $object != NULL ) {
			$result .= $delimiter . sprintf( '%s=%s', OBJECTS, $object );
			$delimiter = '&';
		}
		if ( is_array( $item ) ) {
			$result .= $delimiter . sprintf( '%s=%s', ACTIONS, $this->register_action( $item, $object ) );
			$delimiter = '&';
		}
		return $result;
	}

	public function register_action( $action, $object = NULL ) {
		$object != NULL && array_unshift( $action, array( OBJECTS => $object ) );
		$result = $this->actions->set( $action );
		return $result;
	}

	public function execute_action( $action ) {
		if ( empty( $action[ OBJECTS ] ) ) {
			$owner = $this;
		} else {
			$owner = $action[ OBJECTS ];
			array_shift( $action );
		}
		$message = array( );
		foreach ( $action as $key => $value ) $message[ ] = sprintf( '%s was set to %s', $key, $value );
		$this->set( array( MESSAGE => implode( ',', $message ) ) );
		$owner->parameters->set( $action );
	}

	public function start_buffering( ) {
		while ( ob_end_flush( ) );
		ob_implicit_flush( true );
		header( PAGE_HEADER );
		ob_start( );
	}

	public function flush_buffer( ) {
		$html = $this->output( );
		echo implode( PHP_EOL, array_slice( $html, $this->output_pointer ) );
		$this->output_pointer = count( $html );
		ob_flush( );
	}

	public function stop_buffering( $discard = FALSE ) {
		if ( $discard ) {
			@ob_clean( );
			@ob_end_clean( );
		} else {
			$this->flush_buffer( );
			ob_end_flush( );
		}
	}

	public function finish( $discard = FALSE ) {
		$_SESSION[ PARAMETERS ] = $this->parameters->encode( );
		$_SESSION[ ACTIONS ] = $this->actions->encode( );
		$this->close( );
		$this->stop_buffering( $discard );
		session_write_close( );
	}

	public function reload( ) {
		$this->finish( TRUE );
		header( sprintf( 'Location: %s', $this->get_url( ) ) );
		die( );
	}
}

function demo() {
	$page = new html_page( 'title' );
	$page->initialize();
	$div = $page->body()->add( new html_div( ) );
	$div->add( new html_text( 'text' ) );
	$div->add( new html_img( 'http://' ) );
	$div->close();
	$page->body()->add( new html_img( 'http://', 'text' ) );
	$page->body()->add( new html_span( 'text' ) );
	$page->body()->add( new html_span( new html_img( 'http://' ) ) );
	$page->body()->add( new html_anchor( 'text', 'http://' ) );
	$page->body()->add( new html_anchor( new html_img( 'http://' ), 'http://' ) );
	
	$list = $page->body()->add( new html_unorderedlist( ) );
	$list->add( new html_listitem( 'text' ) );
	$list->add( new html_listitem( new html_span( 'text' ) ) );
	
	$form = $page->body()->add( new html_form( 'name', 'text' ) );
	$form->add( new html_text( 'text' ) );
	$form->add( new html_input( 'name', 'value' ) );
	$form->add( new html_button( 'name', 'text', 'value' ) );
	$form->add( new html_select( 'name', array( 'value1','value2','value3' ), 'value2' ) );
	
	$table = $page->body()->add( new html_table( ) );
	$table->head()->add( 'test' );
	$table->head()->add( new html_text( 'text' ) );
	$table->head()->add( new html_anchor( 'text', 'http://' ) );
	$row = $table->add_row( );
	$row->add( 'test' );
	$row->add( new html_text( 'text' ) );
	$row->add( new html_anchor( 'text', 'http://' ) );
	$table->foot()->add( 'test' );
	$table->foot()->add( new html_text( 'text' ) );
	$table->foot()->add( new html_anchor( 'text', 'http://' ) );
	$table->close();
	$page->finish();
}
