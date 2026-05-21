<?php namespace view;

use view\ViewAbstract;
use \Genert\BBCode\BBCode;

class Authors extends ViewAbstract
{
    public string $pageHtml='';
    public int $count=0;
    public string $dropSearchHtml='';
    public string $content='';
    protected static $i;

    public static function i(): static{
        if (!(static::$i instanceof static)) {
			static::$i = new static();
		}
		return static::$i;
    }
    public function addPageNavigation($authorId,$subId, $pageNum, $count, $goToUrl): static
    {
        $this->_set_css(['buttons.css']);
        $this->count=$count;
        $this->pageHtml.='<a class="'.($pageNum==1?'invis':'').' page_nums_rev" href="'.$goToUrl.'?page='.($pageNum-1).($authorId!==0?'&a_id='.$authorId:'').($subId!==0?'&cur_sub=1':'').'">Предыдущая</a> ';
        for( $i=10; $i < $count+10; $i+=10 ){
            $this->pageHtml.=' <a class='.(($pageNum*10)==$i?'"page_nums"':'"page_nums_rev"').' href="'.$goToUrl.'?page='.($i/10).($authorId!==0?'&a_id='.$authorId:'').($subId!==0?'&cur_sub=1':'').'">'.($i/10).'</a>';
        }
        $this->pageHtml.='<a class="'.($i/10-1==$pageNum || $count==0?'invis':'').' page_nums_rev" href="'.$goToUrl.'?page='.($pageNum+1).($authorId!==0?'&a_id='.$authorId:'').($subId!==0?'&cur_sub=1':'').'">Следующая</a> ';
        return $this;
    }
    public function addDropSearch($goToUrl, $userSearchInput='', $count=-1) : static
    {
        $this->_set_css(['buttons.css']);
        if($userSearchInput!=''){ $this->dropSearchHtml="<p><br>Показаны результаты поиска на запрос: '$userSearchInput'".'  <a class="page_nums" href="'.$goToUrl.'">Отменить поиск</a> '; }
        if($count==0){ $this->dropSearchHtml.=' <br><br> Ничего не найдено. Попробуйте изменить запрос!</p>'; }
        return $this;
    }
    public function add(array $authors) : static 
    {
        $f3=\Base::instance();
        $this->_set_css(['general.css','flexable.css','subs.css']);
        $this->_set_js(['subs.js']);
        $this->content='<div class="note">
            <hr>';
         foreach($authors as $a){
             
             $this->content.='<div class="flex_sb_r_ac flex_wr">
                <div class="flex_fs_r_ac course_cont_block">
                    <div class="ava ava-rounded mr_r_20">
                        <img id="img_'.$a['id'].'" src="'.$f3->get('BASE').'/'.$a['ava_url'].'">
                    </div>
                    
                    <div class="inner_cont_block">
                        <h2 class="note_title">'.$a['name'].'</h2>
                        <p class="author_stats">'.$a['subs_count'].' подписчиков &#8226; Записи: '.$a['notes_count'].'</p>
                        <p>'.$a['status'].'</p>
                    </div>
                </div>
                
                 <div>
                     '.($a['is_subbed']==1?'<a id="authorid_'.$a['id'].'" class="w_100 sub_btn a_sub_btn page_nums_rev" href="#">Отписаться</a>'
                         :($a['is_subbed']==2?'<p class="sub_btn_noa page_nums_rev">Это вы</p>'
                             :($f3->get('user.isAuth')?'<a id="authorid_'.$a['id'].'" class="w_100 sub_btn a_sub_btn page_nums" href="#">Подписаться</a>'
                                :'<p>Для подписки на автора необходимо <a class="page_nums" href="'.$f3->get('BASE').'/login">Войти</a></p>'))).'
                 </div>   
             </div>
             <hr>';
         }
         $this->content.='</div>';
        return $this;
    }
}