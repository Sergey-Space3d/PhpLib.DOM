<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Numpad Class */
class Numpad extends CHtmlElement
{
	protected $m_class = null;
	protected $m_mask = true;
	protected $m_submit = false;
	protected $m_name = null;
	
	/** The constructor */
	public function __construct($name, array $attrs = null, $mask = true, $submit = false) 
	{
		parent::__construct('table', $attrs);
		
		$this->m_class = $attrs ? $attrs['class'] : null;
		$this->m_name = $name;
		$this->m_mask = $mask;
		$this->m_submit = $submit;
		
		if (!CHtmlStyle::has_selector('.numpad'))
		{
		    $style_el = new CHtmlStyle();
		    $this->add_inner($style_el);
		    
		    $style_el->add_selector('.numpad', 'color:#000000;background-color:#BBDDDD;font-weight:bold;font-size:20px;text-align:center;vertical-align:middle;');
		    $style_el->add_selector('TABLE.numpad', 'border:0px none;border-spacing:0px;padding:0px;');
		    $style_el->add_selector('TD.numpad', 'border:0px none;');
		    $style_el->add_selector('BUTTON.numpad', 'margin:2px 2px;border:2px solid #FFFFFF;width:40px;height:40px;');
		    $style_el->add_selector('INPUT.numpad', 'background-color:#CCEEFF;margin:2px 2px;border:2px solid #FFFFFF;width:25px;height:30px;');
		}
		
		// Display table ----------------------------------------------------
		
		$tr = new CHtmlElement('tr');
		$this->add_inner($tr);
		
		$table = $this->make_display_table($name);
		$tr->add_inner(new CHtmlElement('td', array('colspan'=>3), $table));

		// Row 1-2-3 --------------------------------------------------------

		$tr = new CHtmlElement('tr');
		$this->add_inner($tr);		
		
		$this->add_button(1, $tr);
		$this->add_button(2, $tr);
		$this->add_button(3, $tr);

		// Row 4-5-6 --------------------------------------------------------

		$tr = new CHtmlElement('tr');
		$this->add_inner($tr);
		
		$this->add_button(4, $tr);
		$this->add_button(5, $tr);
		$this->add_button(6, $tr);
	
		// Row 7-8-9 --------------------------------------------------------

		$tr = new CHtmlElement('tr');
		$this->add_inner($tr);
		
		$this->add_button(7, $tr);
		$this->add_button(8, $tr);
		$this->add_button(9, $tr);

		// Row C-0-B --------------------------------------------------------
		
		$tr = new CHtmlElement('tr');
		$this->add_inner($tr);
		
		$this->add_button('C', $tr, "_NumpadClear_(\"{$this->m_name}\")");
		$this->add_button(0, $tr);
		$this->add_button('B', $tr, "_NumpadBack_(\"{$this->m_name}\")");

		// JS: add digit ----------------------------------------------------
		
		if (!CHtmlJavaScript::has_function('_NumpadAdd_'))
		{
    		$lines = array(
        		"var el1 = document.getElementById('numpad_digit1');",
        		"var el2 = document.getElementById('numpad_digit2');",
        		"var el3 = document.getElementById('numpad_digit3');",
        		"var el4 = document.getElementById('numpad_digit4');",
        		
        		"if (el1.value == '')",
        		"{",
        		"el1.value = digit;",
        		"}",
        		"else if (el2.value == '')",
        		"{",
        		"el2.value = digit;",
        		"}",
        		"else if (el3.value == '')",
        		"{",
        		"el3.value = digit;",
        		"}",
        		"else if (el4.value == '')",
        		"{",
        		"el4.value = digit;",
        		"}",
        		
        		"document.getElementById(id).value = el1.value + el2.value + el3.value + el4.value;",
        		
        		"if (submit)",
        		"{",
                "if (el4.value != '')",
                "{",
                "this.form.submit();",
                "}",
        		"}",
    		);
    		CHtmlPage::get_js()->add_function('_NumpadAdd_', $lines, array('id', 'digit', 'submit'));

    		// JS: back ---------------------------------------------------------
    		
    		$lines = array(
        		"var el1 = document.getElementById('numpad_digit1');",
        		"var el2 = document.getElementById('numpad_digit2');",
        		"var el3 = document.getElementById('numpad_digit3');",
        		"var el4 = document.getElementById('numpad_digit4');",
        		
        		"if (el4.value != '')",
        		"{",
        		"el4.value = '';",
        		"}",
        		"else if (el3.value != '')",
        		"{",
        		"el3.value = '';",
        		"}",
        		"else if (el2.value != '')",
        		"{",
        		"el2.value = '';",
        		"}",
        		"else if (el1.value != '')",
        		"{",
        		"el1.value = '';",
        		"}",
        		
        		"document.getElementById(id).value = el1.value + el2.value + el3.value + el4.value;",
            );
    		CHtmlPage::get_js()->add_function('_NumpadBack_', $lines, array('id'));
    		
    		// JS: clear --------------------------------------------------------
    		
    		$lines = array(
        		"document.getElementById('numpad_digit1').value = '';",
        		"document.getElementById('numpad_digit2').value = '';",
        		"document.getElementById('numpad_digit3').value = '';",
        		"document.getElementById('numpad_digit4').value = '';",
        		"document.getElementById(id).value = '';",
    		);
    		CHtmlPage::get_js()->add_function('_NumpadClear_', $lines, array('id'));
		}
	}
	
	/** Add button to row */
	protected function add_button($label, $tr, $onclick = null)
	{
		$onclick = $onclick ? $onclick : "_NumpadAdd_(\"{$this->m_name}\", \"{$label}\", \"{$this->m_submit}\")";
		$attrs = array('type'=>'button', 'id'=>"numpad_{$label}", 'onclick'=>$onclick);
		if ($this->m_class) $attrs['class'] = $this->m_class;
		$button = new CHtmlElement('button', $attrs, $label);
		
		$attrs = $this->m_class ? array('class'=>$this->m_class) : null;
		$tr->add_inner(new CHtmlElement('td', $attrs, $button));	
	}
	
	/** Make the display table */
	protected function make_display_table($name)
	{
		$attrs = array('width'=>'100%');
		if ($this->m_class) $attrs['class'] = $this->m_class;
		$table = new CHtmlTable($attrs);
			
		$tr = new CHtmlElement('tr');
		$table->add_inner($tr);

		$td = $this->make_display_cell(1);
		$tr->add_inner($td);
		
		// Add hidden field (composed of 4 digits)
		$hidden = new CHtmlHidden($name, '', array('id'=>$name));
		$td->add_inner($hidden);

		$td = $this->make_display_cell(2);
		$tr->add_inner($td);
		
		$td = $this->make_display_cell(3);
		$tr->add_inner($td);
		
		$td = $this->make_display_cell(4);
		$tr->add_inner($td);	
		
		return $table;
	}
	
	/** Make the display cell */
	protected function make_display_cell($position)
	{
		$attrs = array('id'=>"numpad_digit{$position}", 'readonly'=>'true');
		if ($this->m_class) $attrs['class'] = $this->m_class;
		$el = $this->m_mask ? 
			new CHtmlPassword("numpad_digit{$position}", '', $attrs) :
			new CHtmlText("numpad_digit{$position}", '', $attrs);
		
		$attrs = $this->m_class ? array('class'=>$this->m_class) : null;
		$td = new CHtmlElement('td', $attrs, $el);
		
		return $td;
	}
}
?>