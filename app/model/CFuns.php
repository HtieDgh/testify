<?php namespace model;
class CFuns{
    static function sanitizeString(array $var)
    {
        foreach ($var as $k => $v) {
            if(is_array($v)){
                $v=static::sanitizeString($v);
            }else{
                $var[$k]=stripslashes(htmlentities(strip_tags(trim($v)),ENT_QUOTES |ENT_SUBSTITUTE| ENT_HTML401, 'UTF-8', false));
            }
        }
        return $var;
    }
    //Возврат массива с фразами на поиск
    static function getSearchList($user_search=''):array
    {
        return explode(' ',preg_replace("/-{2,}|\s{2,}|{, }/"," ",$user_search));
    }
    //замена ключей и значений местами, @see Uploads::uploadFile @see BHttp::filesEditorPage
    static function reArrayFiles($file) : array {
        {
            $file_array = array();
            $file_count = count($file['name']);
            $file_key = array_keys($file);
           
            for($i=0;$i<$file_count;$i++)
            {
                foreach($file_key as $val)
                {
                    $file_array[$i][$val] = $file[$val][$i];
                }
            }
            return $file_array;
        }
    }

}

?>