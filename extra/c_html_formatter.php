<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** HTML formatter renders HTML/XML content to readable form */
class CHtmlFormatter
{
    /** Renders HTML/XML content to readable form */
    public static function format(CHtmlElement $el)
    {
        static $level = 0;
        
        $inner = $el->get_inner();
        
        if ($inner)
        {
            $level++;
            $t = str_repeat("\t", $level);
            
            if (!is_array($inner)) $inner = array($inner);
            
            $arr = array();
            $is_node = false;
            
            foreach ($inner as $item)
            {
                if ($item instanceof CHtmlElement)
                {
                    $is_node = true;
                    self::format($item);
                    $arr[] = "\n{$t}";
                }
                
                $arr[] = $item;
            }
            
            $level--;
            
            if ($is_node)
            {
                $arr[] = "\n".str_repeat("\t", $level);
            }
            
            $el->set_inner($arr);
        }
    }
}
?>