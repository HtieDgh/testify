<?php namespace view;

use view\ViewAbstract;

class Courses extends ViewAbstract
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
    public function addDropSearch($goToUrl, $userSearchInput='', $count=-1) : static
    {
        $this->_set_css(['buttons.css']);
        if($userSearchInput!=''){ $this->dropSearchHtml="<p><br>Показаны результаты поиска на запрос: '$userSearchInput'".'  <a class="page_nums" href="'.$goToUrl.'">Отменить поиск</a> '; }
        if($count==0){ $this->dropSearchHtml.=' <br><br> Ничего не найдено. Попробуйте изменить запрос!</p>'; }
        return $this;
    }
    public function addPageNavigation($pageNum, $count, $goToUrl) : static
    {
        $this->_set_css(['buttons.css']);
        $this->count=$count;
        $this->pageHtml.='<a class="'.($pageNum==1?'invis':'').' page_nums_rev" href="'.$goToUrl.'?page='.($pageNum-1).'">Предыдущая</a> ';
        for( $i=10; $i < $count+10; $i+=10 ){
            $this->pageHtml.=' <a class='.(($pageNum*10)==$i?'"page_nums"':'"page_nums_rev"').' href="'.$goToUrl.'?page='.($i/10).'">'.($i/10).'</a>';
        }
        $this->pageHtml.='<a class="'.($i/10-1==$pageNum || $count==0?'invis':'').' page_nums_rev" href="'.$goToUrl.'?page='.($pageNum+1).'">Следующая</a> ';
        return $this;
    }
    //добавить в content DOMString курсов
    public function add(\Base $f3,$courseList) : static
    {
        $this->_set_css(['general.css','flexable.css','subs.css']);
        $this->_set_js(['subs.js']);

        $this->content='<div class="note"><hr>';
        foreach($courseList as $c){
            $this->content.='
            <div id="crsblock_'.$c['id'].'" class="flex_sb_r_ac flex_wr">
                <div class="flex_fs_r_ac flex_wr">
                    <div class="ava ava-rounded mr_r_10">
                        <a href="'.$f3->get('BASE').'/?c_id='.$c['id'].'">
                            <img id="img_'.$c['id'].'" src="'.$c['ava_url'].'">
                        </a>
                    </div>
                    <div class="inner_cont_block">
                        <div class="flex_fs_r_ac">
                            <h2 class="note_title mr_r_10">'.$c['title'].'</h2>
                            <div class="flex_fe_r_ac">
                                <div class="test_btn mr_r_10">
                                    <a class="sub_btn" title="Изменить" href="'.$f3->get('BASE').'/editor/course/change/'.$c['id'].'"><img alt="Изменить" src="'.$f3->get("BASE").'/change_test.svg"></a>
                                </div>
                                <div class="test_btn"> 
                                    <a class="crs_del w_100 sub_btn" title="Удалить" href="'.$f3->get('BASE').'/course/'.$c['id'].'" data-cid="'.$c['id'].'"><img alt="Удалить" src="'.$f3->get("BASE").'/minus_test.svg"></a>
                                </div>
                            </div>
                        </div>
                        <p>'.($c['is_private']==1?'&#128274; &#8226; <a href="'.$f3->get('BASE').'/profile/courses/requests/'.$c['id'].'">Заявки: '.$c['rqst_count'].'</a> &#8226; <a href="'.$f3->get('BASE').'/profile/courses/subscribes/'.$c['id'].'">Участники: '.$c['subs_count'].'</a>' : 'Участники: '.$c['subs_count']).' &#8226; Записи: '.$c['notes_count'].'</p>
                        <p>'.$c['description'].'</p>
                    </div>  
                </div> 
                <div>
                   <p>'.$c['created'].'</p>
                    
                </div>    
            </div>
            <hr>';
        }
        $this->content.='</div>';
        return $this;
    }
    //Блоки курсов автора под главной страницей самого автора
    public function addAuthorCourses(\Base $f3,$courseList,$c_id=0,$a_id=0) : static
    {
        $this->_set_css(['general.css','flexable.css','subs.css']);
        $this->_set_js(['subs.js']);

        $this->content.='<div class="flex_c_r">';
		foreach ($courseList as $v) {
			$this->content.='
				<div class="note course_block">
					<div class="flex_sb_c">
                        
						<div class="ava_prof_block flex_c_r">
							<div class="ava_img">
                                <a href="'.$f3->get('BASE').'/?c_id='.$v['id'].'">
								    <img id="img_'.$v['id'].'" src="'.$v['ava_url'].'">
                                </a>
							</div>
						</div> 
						<div class="inner_cont_block">
							<div class="flex_sb_r_ac"><h2 class="note_title">'.$v['title'].'</h2><p>'.$v['created'].'</p></div>
							<div class="author_stats">'.($v['is_private']==1?'&#128274; &#8226; ':'').' Участники: '.$v['subs_count'].' &#8226; Записи:'.$v['notes_count'].'</div>
							<p>'.$v['description'].'</p>
						</div>
						
						<div class="flex_c_r">
						'.($v['is_subbed']==1?'<a data-cid="'.$v['id'].'" class="c_sub_btn page_nums_rev" href="#">Отписаться</a>'
                        :($v['is_subbed']==2?'<a data-cid="'.$v['id'].'" class="c_sub_btn c_rqst_send page_nums_rev" href="#">Заявка подана</a>'
                        :($v['is_subbed']==3?''
                        :($f3->get('user.isAuth')?'<a data-cid="'.$v['id'].'" class="c_sub_btn new_sub_btn page_nums" href="#">Подписаться</a>'
                        :'')))).'
						</div>
					</div>
					
				</div>
			';
		}
		$this->content.='</div>';
        return $this;
    }
    //получить разметку для выбора курса
    public function getCourseListHtml($courseList,$noteCourseId=-1) : string
    {
        $courseListHtml='';
        foreach ($courseList as $v) {
            $courseListHtml.='<option value="'.$v['id'].'" '.($v['id']==$noteCourseId?'selected':'').'>'.$v['title'].'</option>';
        }
        
        $courseListHtml='<select name="course" id="">
        <option value="0">Выбререте Курс</option >
        '.$courseListHtml.'
        </select>';
        return $courseListHtml;
    }
}
?>