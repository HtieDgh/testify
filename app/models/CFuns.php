<?php
class CFuns{
    static function sanitizeString($var)
    {
        foreach ($var as $k => $v) {
            if(is_array($v)){
                $v=sanitizeString($v);
            }else{
                $var[$k]=stripslashes(htmlentities(strip_tags(trim($v))));
            }
        }
        return $var;
    }
}

?>