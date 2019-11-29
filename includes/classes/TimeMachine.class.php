<?php

class TimeMachine {
    function getTimeZones($mode="basic")
    {
        $RETURN=array();
        $TIMEZONES=timezone_identifiers_list();
        if($mode=="basic")
        {
            $RETURN=$TIMEZONES;
        }
        else if($mode=="full")
        {
            foreach($TIMEZONES as $tz)
            {
                if(strstr($tz,"/"))
                {
                    $TMP=explode("/",$tz,2);
                }
                else
                {
                    $TMP[0]=$tz;
                    $TMP[1]=$tz;    
                }
                $RETURN[$TMP[0]][]=$TMP[1];
            }
        }
        else if($mode=="countries")
        {
            foreach($TIMEZONES as $tz)
            {
                $TMP=explode("/",$tz,2);
                if(!in_array($TMP[0],$RETURN))
                {
                    $RETURN[]=$TMP[0];
                }
            }   
        }
        return $RETURN;
    }
    
    function setTZ($tz="UTC")
    {
        $TIMEZONES=timezone_identifiers_list();
        if(in_array($tz,$TIMEZONES))
        {
            date_default_timezone_set($tz);
            return true;
        }
        else
        {
            die("<strong style='color: red;'>ERROR: </strong>setTZ -> Invalid Timezone, timezone must be specified like this Australia/Sydney (see full list of timezones here <a href='http://php.net/manual/en/timezones.php' target='_blank'>http://php.net/manual/en/timezones.php</a>)");
            return false;
        }
    }
    
    function getTZ($mode="basic")
    {
        $tz = date_default_timezone_get();
        
        if($mode=="basic")
        {
            return $tz;
        }
        else if($mode=="full")
        {
            $RETURN['timezone']=$tz;
            $RETURN['dstactive']=date("I");
            return $RETURN;
        }
    }
    
    function timeZoneConvert($time, $fromTimezone, $toTimezone)
    {
        $utctime=gmdate("Y-m-d H:i:s",strtotime($time));
        $offset=$this->getTZOffset("UTC",$toTimezone);
        $result=date("Y-m-d H:i:s",strtotime("$utctime $offset second"));
        
        return $result;
    }
    
    function getTZOffset($remote_tz, $origin_tz = null)
    {
        if($origin_tz === null)
        {
            if(!is_string($origin_tz = date_default_timezone_get()))
            {
                return false; // A UTC timestamp was returned -- bail out!
            }
        }
        $origin_dtz = new DateTimeZone($origin_tz);
        $remote_dtz = new DateTimeZone($remote_tz);
        $origin_dt = new DateTime("now", $origin_dtz);
        $remote_dt = new DateTime("now", $remote_dtz);
        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        return $offset;
    }
}

//$tz = new TimeMachine();

//Set Timezone
//$tz->setTZ("Hogwarts");
//$tz->setTZ("Australia/Sydney");
//$tz->setTZ("Australia/Brisbane");

//Get Timezone
//$RETURN=$tz->getTZ();
//$RETURN=$tz->getTZ($mode="basic");
//$RETURN=$tz->getTZ($mode="full");

//Get All Timezones
//$RETURN=$tz->getTimeZones('basic');
//$RETURN=$tz->getTimeZones('full');
//$RETURN=$tz->getTimeZones('countries');

//Convert Times
//$sourcetime="2000-01-01 17:30:00";
//$sourcetime=date("Y-m-d H:i:s");
//$RETURN=$tz->timeZoneConvert($sourcetime, "America/New_York", "Africa/Harare");
//$RETURN=$tz->timeZoneConvert($sourcetime, "Australia/Sydney", "UTC");

//die("<pre>".print_r($RETURN,true)."</pre>");

?>
