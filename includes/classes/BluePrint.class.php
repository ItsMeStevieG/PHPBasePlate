<?php
class BluePrint {
    protected $templateFile;
    protected $replaceValues = array();
  
    public function __construct($file)
    {
        $this->templateFile = $file;
    }
    
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }
    
    public function multiSet($multiset)
    {
        foreach($multiset as $key=>$value)
        {
            $this->values[$key] = $value;   
        }
    }
    
    public function output($html=NULL)
    {
        if(!isset($html) || $html==NULL)
        {
            if (!file_exists($this->templateFile))
            {
                return "Error loading template file ($this->templateFile).";
            }
            $output = file_get_contents($this->templateFile);
        }
        else
        {
            $output=$html;
        }
        
        if(!empty($this->values))
        {
            foreach ($this->values as $key => $value)
            {
                $tagToReplace = "[@$key]";
                $output = str_replace($tagToReplace, $value, $output);
            }
        }
      
        return $output;
    }
}

?>