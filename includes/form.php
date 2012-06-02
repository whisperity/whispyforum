<?php
/**
 * 
 * 
 * /includes/form.php
*/

// If selfURL isn't already defined, we define it.
if ( !function_exists("selfURL") )
{
	function selfURL()
	{
		$ret = substr( strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos( strtolower($_SERVER['SERVER_PROTOCOL']), "/") ); // Add protocol (like HTTP)
		$ret .= ( empty($_SERVER['HTTPS']) ? NULL : ( ($_SERVER['HTTPS'] == "on") ? "s" : NULL) ); // Add 's' if protocol is secure HTTPS
		$ret .= "://" . $_SERVER['SERVER_NAME']; // Add domain name/IP address
		$ret .= ( $_SERVER['SERVER_PORT'] == 80 ? "" : ":".$_SERVER['SERVER_PORT'] ); // Add port directive if port is not 80 (default www port)
		$ret .= $_SERVER['REQUEST_URI']; // Add the rest of the URL
		
		return $ret; // Return the value
	}
}

class form
{
	/**
	 * The class form can be used to easily generate HTML forms using PHP calls.
	*/
	
	private $_action;
	private $_method;
	private $_elements = array();
	
	public function __construct( $action = '#self', $method = "POST" )
	{
		if ( $action === "#self" )
			$this->_action = selfURL();
		
		if ( strtoupper($method) == "POST" || strtoupper($method) == "GET" )
			$this->_method = strtoupper($method);
	}
	
	public function add_element( $type, $name, $value = "", $configuration = array() )
	{
		if ( !is_array($configuration) )
		{
			echo "Configuration argument for " .$name. " must be an array.";
			return FALSE;
		}
		
		return $this->_elements[] = array(
			'type'	=>	$type,
			'name'	=>	$name,
			'value'	=>	$value,
			'conf'	=>	$configuration);
	}
	
	public function render()
	{
		$output = '<form method="' .$this->_method. '" action="' .$this->_action. '">' . "\n";
		
		foreach ( $this->_elements as $element )
		{
			$disabled = in_array("disabled", $element['conf']);
						
			if ( @$element['conf']['label'] )
				$output .= @$element['conf']['label'] . (in_array("mandatory", $element['conf']) ? '<span class="red-star">*</span>' : NULL) . ":&nbsp;";
			
			switch ($element['type'])
			{
				case 'header':
					$output .= '<span class="form-header">' .$element['value']. '</span>' . "\n";
					
					break;
				case 'text':
				case 'password':
					$size = @$element['conf']['size'];
					$maxlength = @$element['conf']['maxlength'];
					
					$output .= '<input type="' . $element['type'] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $element['name'] . '" value="' . $element['value'] . '" ' .($disabled ? 'disabled="disabled"' : NULL). ' />';
					
					break;
				case 'textarea':
					$rows = @$element['conf']['rows'];
					$cols = @$element['conf']['cols'];
					
					if ( @$element['conf']['label'] )
						$output .= "<br>";
					
					$output .= '<textarea name="' . $element['name'] . '" rows="' . $rows . '" cols="' . $cols . '" ' .($disabled ? 'disabled="disabled"' : NULL). '>' . $element['value'] . '</textarea>';
					
					break;
				case 'radio':
					$option_label = ( @$element['conf']['option_label'] ? @$element['conf']['option_label'] : NULL );
					$checked = in_array("checked", $element['conf']);
					
					$output .= '<input type="radio" name="' .$element['name']. '" value="' .$element['value']. '" ' .($disabled ? 'disabled="disabled"' : NULL). ' ' .($checked ? 'checked="checked"' : NULL). '>&nbsp;' .$option_label;
					
					break;
				case 'check':
					$checked = in_array("checked", $element['conf']);
					
					$output .= '<input type="checkbox" name="' .$element['name']. '" value="' .$element['value']. '" ' .($disabled ? 'disabled="disabled"' : NULL). ' ' .($checked ? 'checked="checked"' : NULL). '/>';
					
					break;
				case 'button':
				case 'reset':
				case 'submit':
					$output .= '<input type="' .$element['type']. '" name="' .$element['name']. '" value="' .$element['value']. '" ' .($disabled ? 'disabled="disabled"' : NULL). '/>';
					
					break;
				case 'select':
					$size = @$element['conf']['size'];
					
					$output .= '<select name="' .$element['name']. '" size="' .($size ? $size : 1). '" ' .($disabled ? 'disabled="disabled"' : NULL). '>' . "\n";
					
					if ( is_array($element['value']) )
					{
						foreach ( $element['value'] as $value => $text )
						{
							if ( is_array($text) )
							{
								$output .= '<optgroup label="' .$value. '">\n';
								
								foreach ( $text as $subelement_value => $subelement_text )
									$output .= '<option value="' .$subelement_value. '">' .$subelement_text. '</option>' . "\n";
								
								$output .= '</optgroup>';
							} else {
								$output .= '<option value="' .$value. '">' .$text. '</option>' . "\n";
							}
						}
					}
					
					$output .= '</select>';
					
					break;
				default:
					break;
			}
			
			if ( in_array('linebreak', $element['conf']) )
				$output .= "<br>\n";
		}
		
		$output .= '</form>';
		
		echo $output;
	}
}

/** Begin generating sample form **/
$form = new form;
$form->add_element("header", "header1", "Some fields", array());
$form->add_element("text", "test", "text", array('label'	=>	"Text", 'linebreak', 'mandatory'));
$form->add_element("password", "testp", "somepassword", array('label'	=>	"Not working password", 'disabled', 'linebreak'));
$form->add_element("textarea", "area", "test", array('label'	=>	"Describe", 'rows'	=>	15, 'cols'	=>	20, 'linebreak'));
$form->add_element("radio", "radio_1", "value_1", array('label'	=>	"Use something", 'option_label'	=>	"1"));
$form->add_element("radio", "radio_1", "value_2", array('option_label'	=>	"2", 'linebreak', 'checked'));
$form->add_element("check", "is_installed", "installed", array('label'	=>	"Is installed", 'linebreak', 'checked'));
$form->add_element("select", "choose", array("calm"	=>	"Calm", "angry"	=>	"Angry", "lol"	=>	"Lol"), array('label'	=>	"select something", 'mandatory', 'linebreak'));
$form->add_element("select", "optgroups", array("a"	=>	array("lol"	=>	"something", "asd"	=>	"cool"), "b"	=>	array("anotherthingy"	=>	"another stuff")), array('label'	=>	"OptGroups", 'mandatory', 'linebreak'));
$form->add_element("submit", "buttonname", "Submit form!", array('linebreak'));
$form->add_element("reset", "reset", "Reset", array());
$form->render();

echo "<hr>Brought to you by:<br>\n";
highlight_file(__FILE__);
?>