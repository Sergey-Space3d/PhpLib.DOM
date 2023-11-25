<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Clipped Table Class */
class ClippedTable extends ZebraTable
{
    protected $tArgs = null;
    protected $useAjax = false;
    
    public $NumItems = 0;
    public $TopRowIndex = 0;
    public $NumRows = 0;
    
    /** The constructor */
    public function __construct($name, $num_items, array $args, array $attrs = null, $use_ajax = false)
    {
        $this->tArgs = $args;
        $this->useAjax = $use_ajax;
        $this->NumItems = $num_items;
        
        $this->TopRowIndex = (int)CHtmlForm::get_value($name.'top_index');
        $this->NumRows = (int)CHtmlForm::get_value($name.'num_rows');
        if (!$this->NumRows) $this->NumRows = 20;
        
        switch (CHtmlForm::get_action())
        {
            case $name.'first':
                $this->TopRowIndex = 0;
                break;
                
            case $name.'prev':
                $this->TopRowIndex = $this->TopRowIndex - $this->NumRows;
                break;
                
            case $name.'next':
                $this->TopRowIndex = $this->TopRowIndex + $this->NumRows;
                break;
                
            case $name.'last':
                $this->TopRowIndex = $this->NumItems - 1;
                break;
                
            default:
                break;
        }
        
        $last = floor(($this->NumItems - 1) / $this->NumRows) * $this->NumRows;
        $this->TopRowIndex = min($this->TopRowIndex, $last);
        $this->TopRowIndex = max($this->TopRowIndex, 0);
        
        parent::__construct($name, $attrs, 1);
    }
    
    /** Get number of pages */
    public function get_num_pages(){ return (int)ceil($this->NumItems / $this->NumRows); }
    
    /** Get current page number, 1-based */
    public function get_cur_page() { return (int)floor(($this->TopRowIndex + 1) / $this->NumRows) + 1; }

    /** Inquire if the row is visible (row's index is zero-based) */
    public function is_row_visible($index) { return $index >= $this->TopRowIndex && $index < ($this->TopRowIndex + $this->NumRows); }

    /** Add navigation panel. Returns TD element */
    public function add_navigation_panel($item_name = null, $attrs = null)
    {
        if (!$attrs) $attrs = array('align'=>'left', 'cellpadding'=>'2');
        $table = new CHtmlTable($attrs);
        
        $tr = new CHtmlElement('tr');
        $table->add_inner($tr);
        
        $num_pages = $this->get_num_pages();
        $cur_page = $this->get_cur_page();
        if (!$item_name) $item_name = 'items';
        
        $tr->add_inner($this->make_form_td('first', 'First', $cur_page > 1));
        $tr->add_inner($this->make_form_td('prev', 'Prev', $cur_page > 1));
        $tr->add_inner($this->make_form_td('next', 'Next', $cur_page < $num_pages));
        $tr->add_inner($this->make_form_td('last', 'Last', $cur_page < $num_pages));
        
        $tr->add_inner(new CHtmlElement('td', array('style'=>'padding-left:20px;padding-right:20px;white-space:nowrap;'), 
            sprintf('Page %d of %d (total %d %s)', $cur_page, $num_pages, $this->NumItems, $item_name)));
        
        $tr->add_inner(new CHtmlElement('td', null, 'Show'));
        $tr->add_inner($this->make_num_rows_form_td($this->NumItems > 10));
        //$tr->add_inner(new CHtmlElement('td', null, sprintf('  Top Index: %d', $this->TopRowIndex)));
        
        $td = new CHtmlElement('td', array('class'=>self::$classes['table'], 'align'=>'left', 'colspan'=>'100%'), $table);
        $this->add_inner(new CHtmlElement('tr', null, $td));
        
        return $td;
    }
    
    /** Make form cell for navigation panel */
    protected function make_form_td($name, $label, $enabled)
    {
        $tname = $this->get_attr('name');
        $action = $tname.$name;
        $form = new CHtmlForm($action);
        
        $attrs = array('class'=>self::$classes['button']);
        if (!$enabled) $attrs += array('disabled'=>'disabled', 'style'=>'color:#DDDDDD;');
        $form->add_inner(new CHtmlSubmit($label, $attrs));
        
        if ($this->useAjax)
        {
            $this->setup_ajax($form, $action);
        }
        else
        {
            $form->add_inner(new CHtmlHidden($tname.'top_index', $this->TopRowIndex));
            $form->add_inner(new CHtmlHidden($tname.'num_rows', $this->NumRows));
            
            foreach ($this->tArgs as $key=>$val)
            {
                $form->add_inner(new CHtmlHidden($key, $val));
            }
        }
        
        $td = new CHtmlElement('td', null, $form);
        return $td;
    }
    
    /** Make form cell for the number of displayed items */
    protected function make_num_rows_form_td($enabled)
    {
        $tname = $this->get_attr('name');
        $action = $tname.'num_rows';
        $form = new CHtmlForm($action);
        
        $attrs = array('id'=>$action, 'class'=>self::$classes['select'], 'title'=>'Number of Items');
        if (!$enabled) $attrs += array('disabled'=>'disabled', 'style'=>'color:grey;');
        $cbox = new CHtmlCbox($action, $this->NumRows, $attrs);
        $form->add_inner($cbox);
        
        if ($this->useAjax)
        {
            $fn_refresh = 'refresh'.$tname;
            $data = CHtmlHttpRequest::make_post_data(array_merge($this->tArgs,
                array('action'=>$action, $tname.'top_index'=>$this->TopRowIndex)));
            
            $onsubmit = "return {$fn_refresh}(\"{$data}\");";
            $cbox->set_attr('onchange', $onsubmit);
        }
        else
        {
            $cbox->set_attr('onchange', 'this.form.submit();');
            $form->add_inner(new CHtmlHidden($tname.'top_index', $this->TopRowIndex));
        
            foreach ($this->tArgs as $key=>$val)
            {
                $form->add_inner(new CHtmlHidden($key, $val));
            }
        }
        
        $cbox->add_item('10');
        $cbox->add_item('20');
        $cbox->add_item('50');
        $cbox->add_item('100');
        
        $td = new CHtmlElement('td', null, $form);
        return $td;
    }
    
    /** Setup AJAX to refresh the table */
    protected function setup_ajax(CHtmlForm &$form, $action)
    {
        $tname = $this->get_attr('name');
        $fn_refresh = 'refresh'.$tname;
        $data = CHtmlHttpRequest::make_post_data(array_merge($this->tArgs, 
            array('action'=>$action, $tname.'top_index'=>$this->TopRowIndex)));
        
        $onsubmit = "return {$fn_refresh}(\"{$data}\");";
        $form->set_attr('onsubmit', $onsubmit);
    }
    
    /** Make JS callback to use on submit event. Note, that fn can be parental element */
    public static function make_js_callback($tname, $url, $fn = null)
    {
        $js = new CHtmlHttpRequest();
        
        if ($fn instanceof CHtmlElement)
        {
            $fn->set_id($tname);
            $fn = null;
        }
        
        if (!$fn)
        {
            $fn = 'response'.$tname;
            $lines = array();
            $lines[] = "var el = document.getElementById('{$tname}');";
            $lines[] = "el.innerHTML = response;";
            $js->add_function($fn, $lines, array('response'));
        }
        
        $fn_refresh = 'refresh'.$tname;
        $lines = array();
        $lines[] = "var cbox = document.getElementById('{$tname}num_rows');";
        $lines[] = "var option = cbox.options[cbox.selectedIndex];";
        $lines[] = "data += '&{$tname}num_rows=' + (option.value ? option.value : option.text);";
        $lines[] = "post_request('{$url}', data, '{$fn}');";
        $lines[] = 'return false;';
        $js->add_function($fn_refresh, $lines, array('data'));
        
        return $js;
    }
}
?>