<?php
class CFuns{
    static function sanitizeString($var)
    {
        foreach ($var as $k => $v) {
            if(is_array($v)){
                $v=static::sanitizeString($v);
            }else{
                $var[$k]=stripslashes(htmlentities(strip_tags(trim($v))));
            }
        }
        return $var;
    }
    //Возврат массива с запросами на поиск
    static function getSearchList($user_search=''){
        $search= preg_replace("/-{2,}|\s{2,}|{, }/"," ",$user_search);
        return explode(' ',$search);
    }
}

?>