<?php namespace view;

abstract class ViewAbstract{
    public array $css=[];
    public array $js=[];

    protected function __construct() {}
    /**
	 * @return static
	 */

	abstract public static function i():static;

    /**
     * <p>Сохраняет css для <head> текущего представления</p>
     * @param array массив строк названий файлов css
    */
    protected function _set_css($css_a=[])
    {
        foreach ($css_a as $v) {
            if(!isset($this->css[$v])){
                $this->css[$v]=$v;
            }
        }
    }
    /**
     * <p>Сохраняет js для <head> текущего представления</p>
     * @param array массив строк из названий файлов js
    */
    protected function _set_js($js_a=[])
    {
        foreach ($js_a as $v) {
            if(!isset($this->js[$v])){
                $this->js[$v]=$v;
            }
        }
    }

    /**
     * <p>Врозвращает css <link> для <head> текущего представления</p>
     * @param string URL сайта
     * @return string <link> для встраивания в <head>
    */
    public function css2link($basedir, $css=[])
    {
        $out='';
        foreach ($css as $v)
        {
            $out.='<link rel="stylesheet" type="text/css" href="'.(str_contains($v,'http') ? $v : $basedir.'/'.$v).'">';
        }
        return $out;
    }
    /**
     * <p>Врозвращает js <link> для <head> текущего представления</p>
     * @param string URL сайта
     * @return string <link> для встраивания в <head>
    */
    public function js2link($basedir, $js=[])
    {
        $out='';
        foreach ($js as $v)
        {
            $out.='<script src="'.(str_contains($v,'http')?$v : $basedir.'/'.$v).'"></script>';
        }
        return $out;
    }
    public function get_static(string $siteDomain):string
    {
        return $this->css2link($siteDomain,$this->css).
        $this->js2link($siteDomain,$this->js);
    }
}


?>