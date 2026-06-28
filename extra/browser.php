<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Browser Class */
class Browser
{
    /** Return true if Mobile User Agent is detected */
    public static function is_mobile_device()
    {
        if (!isset($_SESSION['is_mobile_browser']))
        {
            $_SESSION['is_mobile_browser'] = false;
            
            $mobile_agent = array(
                '/iphone/i' => 'iPhone',
                '/ipod/i' => 'iPod',
                '/ipad/i' => 'iPad',
                '/android/i' => 'Android',
                '/blackberry/i' => 'BlackBerry',
                '/webos/i' => 'Mobile');
            
            foreach($mobile_agent as $key=>$value)
            {
                if(@preg_match($key, $_SERVER['HTTP_USER_AGENT']))
                {
                    $_SESSION['is_mobile_browser'] = true;
                    break;
                }
            }
        }
        
        return $_SESSION['is_mobile_browser'];
    }
}
?>