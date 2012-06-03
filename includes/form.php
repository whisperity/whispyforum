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

// If prettyVar isn't already defined, we define it.
if ( !function_exists("prettyVar") )
{
	function prettyVar( $variable )
	{
		echo str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($variable,true))."<br>";
	}
}

/** Define the form object **/
class form
{
	/**
	 * The class form can be used to easily generate HTML forms using PHP calls.
	*/
	
	private $_action;
	private $_method;
	
	// _elements is an array of the forum elements added.
	private $_elements = array();
	
	public function __construct( $action = '#self', $method = "POST" )
	{
		/**
		 * At construction, the form will have its action (target page) and method set.
		*/
		
		if ( $action === "#self" )
			$this->_action = selfURL();
		
		if ( strtoupper($method) == "POST" || strtoupper($method) == "GET" )
			$this->_method = strtoupper($method);
	}
	
	public function add_element( $configuration = array() )
	{
		/**
		 * Adds the element to the array of elements.
		 * 
		 * Input $configuration is an array or a base_element derived object.
		 * To create elements, use the element objects (later defined in this file).
		*/
		
		// If the argument is not an array but a reference variable to an object,
		// we use the base_element::fetch() function to fetch the array.
		if ( is_object($configuration) && in_array(get_class($configuration), array('text', 'password', 'textarea', 'radio', 'check', 'button', 'submit', 'reset', 'select')) )
			$configuration = $configuration->fetch();
		
		// Fetch the miscellaneous configuration from the original input.
		$conf = $configuration;
		unset($conf['type'], $conf['name'], $conf['value']);
		
		return $this->_elements[] = array(
			'type'	=>	$configuration['type'],
			'name'	=>	$configuration['name'],
			'value'	=>	$configuration['value'],
			'conf'	=>	$conf);
	}
	
	public function header( $text )
	{
		/**
		 * This function will add a header $text to the form.
		 * Header elements are taken care of by this function, not the base_element and derivatives.
		*/
		
		return $this->add_element( array('type'	=>	"header", 'name'	=>	'header_' .(count($this->_elements) + 1), 'value'	=>	$text, 'linebreak') );
	}
	
	public function render()
	{
		/**
		 * The render() function renders the HTML code of the form.
		*/
		
		// Set header of form.
		$output = '<form method="' .$this->_method. '" action="' .$this->_action. '">' . "\n";
		
		foreach ( $this->_elements as $element )
		{
			$disabled = in_array("disabled", $element['conf']);
			
			// Add label if present and add mandatory mark.
			if ( @$element['conf']['label'] )
				$output .= @$element['conf']['label'] . (in_array("mandatory", $element['conf']) ? '<span class="red-star">*</span>' : NULL) . ":&nbsp;";
			
			switch ($element['type'])
			{
				// Based on element type, fetch the HTML output.
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
					/** This needs implementation **/
					
					break;
				case 'check':
					$checked = in_array("checked", $element['conf']);
					
					$output .= '<input type="checkbox" name="' .$element['name']. '" value="' .$element['value']. '" ' .($disabled ? 'disabled="disabled"' : NULL). ' ' .($checked ? 'checked="checked"' : NULL). '/>';
					
					break;
				case 'select':
					// Select elements can take option groups too.
					// To have option groups, the element of the 'value' array needs to be an array (multidimensional arrays).
					$size = @$element['conf']['size'];
					
					/** This needs implementation **/
					
					break;
				case 'button':
				case 'reset':
				case 'submit':
					$output .= '<input type="' .$element['type']. '" name="' .$element['name']. '" value="' .$element['value']. '" ' .($disabled ? 'disabled="disabled"' : NULL). '/>';
					
					break;
				default:
					break;
			}
			
			// Add line break if required.
			if ( in_array('linebreak', $element['conf']) )
				$output .= "<br>\n";
		}
		
		$output .= '</form>';
		
		// Send the output to the browser.
		echo $output;
	}
}

/** Define the individual elements **/
abstract class base_element
{
	/**
	 * base_element is an abstract class defining the default values on an element.
	*/
	
	// type is the type of the element.
	protected $type;
	
	// name is the name of the form element.
	protected $name;
	
	// value is the default value of the form element.
	protected $value;
	
	// the element will be printed as mandatory if not FALSE.
	protected $mandatory = 'mandatory';
	
	// the form element will be disabled if this value is not FALSE.
	protected $disabled = 'disabled';
	
	// a linebreak will be appended after the element if not FALSE.
	protected $linebreak = 'linebreak';
	
	// the label is output before the element if present.
	protected $label;
	
	public function __construct( $name, $value = "", $mandatory = FALSE, $disabled = FALSE, $linebreak = FALSE )
	{
		/**
		 * The constructor takes the base values and sets up the base of the element.
		*/
		
		// See the abstract class definition for what these values mean.
		$this->name = $name;
		$this->value = $value;
		
		// The following boolenas will be removed from memory if the value is FALSE.
		// (If not FALSE, the default key will remain and the element will be mandatory/disabled.)
		if ( $mandatory === FALSE )
			unset($this->mandatory);
		
		if ( $disabled === FALSE )
			unset($this->disabled);
		
		if ( $linebreak === FALSE )
			unset($this->linebreak);
	}
	
	public function label( $label )
	{
		/**
		 * Sets the label of the element to $label.
		*/
		
		$this->label = $label;
	}
	
	public function fetch()
	{
		/**
		 * The fetch function returns the configuration of the element.
		 * This array can be used for the form::add_element() function to add a certain element to the form.
		*/
		
		return get_object_vars($this);
	}
}

class hidden extends base_element
{
	/**
	 * Hidden elements are background values, they do not appear in the browser, only in the source code.
	 * Hidden elements are not having anything to configure besides their name and value.
	*/
	
	// Set the type of the element to 'hidden'
	protected $type = "hidden";
}

class text extends base_element
{
	/**
	 * A text box is a basic input box on the form.
	*/
	
	// Set the type of the element to 'text'
	protected $type = "text";
	
	public function set_size( $size )
	{
		/**
		 * Sets the size of the text box to $size.
		*/
		
		$this->size = $size;
	}
	
	public function set_maxlength( $maxlength )
	{
		/**
		 * Sets the maximum length of input to $maxlength.
		*/
		
		$this->maxlength = $maxlength;
	}
}

class password extends text
{
	/**
	 * Password inputs are having many things in common with text boxes.
	*/
	
	// Only the element type is changed to 'password' instead of 'text'.
	protected $type = "password";
}

class textarea extends base_element
{
	/**
	 * Textareas are multi-row input fields.
	*/
	
	// Set the type of the element to 'textarea'
	protected $type = "textarea";
	
	public function set_rows( $rows )
	{
		/**
		 * Sets the number of rows to $rows.
		*/
		
		$this->rows = $rows;
	}
	
	public function set_cols( $cols )
	{
		/**
		 * Sets the number of columns to $cols.
		*/
		
		$this->cols = $cols;
	}
}

class radio extends base_element
{
	/**
	 * Radio buttons are clickable selection which usually used to choose between Yes or No (along with checkboxes),
	 * or to vote on polls, select something from a range like 1, 2, 3, 4 or 5.
	*/
	
	// Set the type of the element to 'radio'
	protected $type = "radio";
	
	/** NEED IMPLEMENTATION OF ADDING INDIVIDUAL ELEMENTS TO SAME RADIO BUTTON "GROUP" **/
}

class check extends base_element
{
	/**
	 * Check boxes are boxes which are clickable and their return usually indicate Yes/No.
	*/
	
	// Set the type of the element to 'check'
	protected $type = "check";
	
	public function set_checked( $checked )
	{
		/**
		 * Sets the element to be checked by default.
		*/
		
		$this->checked = $checked;
	}
}

class select extends base_element
{
	/**
	 * Select elements are used to give a list of values the user chooses from.
	*/
	
	// Set the type of the element to 'select'
	protected $type = "select";
	
	public function set_size( $size )
	{
		/**
		 * Sets the vertical size of the select box to $size number of values.
		*/
		
		$this->size = $size;
	}
	/** THIS NEEDS THE SELECT OPTION AND OPTGROUP STUFF ADDED **/
	
}

class button extends base_element
{
	/**
	 * Buttons are clickable.
	*/
	
	// Set the type of the element to 'button'
	protected $type = "button";
}

class reset extends button
{
	/**
	 * Reset button delete the entered values from a form and resets it back to the original value.
	*/
	
	// Set the type of the element to 'reset'
	protected $type = "reset";
}

class submit extends button
{
	/**
	 * Submit buttons send the form's entered data to the page defined in 
	 * form::_action with the method form::_method (GET or POST)
	*/
	
	// Set the type of the element to 'submit'
	protected $type = "submit";
}

/** Begin generating sample form **/
$form = new form;
$form->header("Basic information");

// Name
$name = new text("name", "", TRUE, FALSE, TRUE);
$name->label("Name");
$name->set_size(35);

$form->add_element($name);

// Age
$age = new text("age", "", TRUE, FALSE, TRUE);
$age->label("Age");
$age->set_size(3);
$age->set_maxlength(2);

$form->add_element($age);

// Gender
// THIS NEEDS MODIFICATION
/**$gender_male = new radio("gender", "male", TRUE, FALSE, FALSE);
$gender_male->label("Gender");
$gender_male->set_option_label("Male");
$gender_female = new radio("gender", "female", FALSE, FALSE, TRUE);
$gender_female->set_option_label("Female");**/

$form->add_element($gender_male);
$form->add_element($gender_female);

// Married
$married = new check("married", "yes", FALSE, FALSE, TRUE);
$married->label("Are you married?");

$form->add_element($married);

// Vehicle
// THIS NEEDS MODIFICATION
/**$vehicle = new select("vehicle");
$vehicle->label("Your transportation mode");
$vehicle->add_choice("foot", "Walk");
$vehicle->add_choice("bicycle", "Bicycle");
$vehicle->add_optgroup("Cars", array('volkswagen'	=>	"Volkswagen", 'audi'	=>	"Audi"));
$vehicle->add_choice("bus", "Bus");**/

$form->add_element($vehicle);

$form->render();
prettyVar($form);

echo "<hr>Brought to you by:<br>\n";
highlight_file(__FILE__);
?>