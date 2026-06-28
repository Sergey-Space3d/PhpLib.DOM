<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** HTML page helper */
class CHtmlPageHelper
{
    /** Set page's title */
    public static function set_title($title)
    {
        if (self::$m_development) $title .= ' (DEVELOPMENT)';
        
        echo "<script type='text/javascript'>\n";
        echo "<!--\n";
        echo "document.title='{$title}';\n";
        echo "//-->\n";
        echo "</script>\n";
    }
    
    /** Close the page */
    public static function close()
    {
        echo "<script type='text/javascript'>\n";
        echo "<!--\n";
        echo "self.close();\n";
        echo "//-->\n";
        echo "</script>\n";
    }
    
    /** Refresh the page */
    public static function refresh_self()
    {
        echo "<script type='text/javascript'>\n";
        echo "<!--\n";
        echo "self.location.href = self.location.href;\n";
        echo "//-->\n";
        echo "</script>\n";
    }
    
    /** Refresh the parent's page */
    public static function refresh_parent()
    {
        echo "<script type='text/javascript'>\n";
        echo "<!--\n";
        echo "var pw = self.opener;\n";
        echo "if (pw) { pw.location.href = pw.location.href; }\n";
        echo "//-->\n";
        echo "</script>\n";
    }
    
    /** Make href element */
    public static function href($href, $title = null, $target='_blank')
    {
        if (!$title) $title = $href;
        return "<a href='".$href."' target='".$target."'>".$title."</a>";
    }
    
    /** Make href to popup window */
    public static function href_popup($href, $title, $attr = null)
    {
        echo "<script type='text/javascript'>\n";
        echo "<!--\n";
        echo "function popitup(url, title, attr)\n";
        echo "{\n";
        echo "	newwindow = window.open(url, title, attr);\n";
        echo "	if (window.focus) { newwindow.focus(); }\n";
        echo "	return false;\n";
        echo "}\n";
        echo "//-->\n";
        echo "</script>\n";
        
        $args = "'".$href."','".$title."'";
        
        if (is_array($attr))
        {
            $args .= ",'";
            
            foreach ($attr as $key=>$val)
                $args .= $key."=".$val.",";
                
                $args = substr($args, 0, -1);
                $args .= "'";
        }
        
        echo "<a href=".$href." onclick=\"return popitup(".$args.")\">".$title."</a>";
    }
    
    /** Make href javascript */
    public static function href_js($url, $title = null)
    {
        $str = null;
        static $init_js = false;
        
        if (!$init_js)
        {
            $init_js = true;
            
            $js = new CHtmlJavaScript();
            $args = array('url');
            $lines = array(
                "var w = window.open(url, '_blank', 'width=200,height=300,location=no,menubar=no,status=no,toolbar=no');",
                "w.focus();");
            $js->add_function('openMobiWin', $lines, $args);
            
            $str = $js;
        }
        
        // TODO: identify the mobile browser
        $str .= "<span style='cursor:hand;text-decoration:underline;' onclick=\"openMobiWin('".$url."')\">".($title ? $title : $url)."</span>";
        return $str;
    }
    
}
?>