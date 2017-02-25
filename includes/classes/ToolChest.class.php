<?php

class ToolChest {
    
    //Dumps Variable Contents and Formats Array In Human Readable Format
    public function printVar($var,$die=false)
    {
        if(is_array($var)) //Array Passed
        {
            $return="<pre>".print_r($var,true)."</pre>";
        }
        else
        {
            $return="<pre>$var</pre>";
        }
        
        if(isset($die) && $die==true)
        {
            die($return);
        }
        else
        {
            return $return;    
        }
    }
}

?>