<?php namespace view;

use view\ViewAbstract;
use \Genert\BBCode\BBCode;

class Notes extends ViewAbstract
{
    public string $pageHtml='';
    public array $notesHtmlList=[];
    public string $dropSearchHtml='';
    public string $count='';
    protected static $i;

    public static function i(): static{
        if (!(static::$i instanceof static)) {
			static::$i = new static();
		}
		return static::$i;
    }

    public function addPageNavigation($authorId,$subId,$courseId, $pageNum, $count, $goToUrl): void
    {
        $this->_set_css(['buttons.css']);
        $this->count=$count;
        $this->pageHtml.='<a class="'.($pageNum==1?'invis':'').' page_nums_rev" href="'.$goToUrl.'?page='.($pageNum-1).($authorId!==0?'&a_id='.$authorId:'').($subId!==0?'&cur_sub=1':'').($courseId!=0?'&c_id='.$courseId:'').'">Предыдущая</a> ';
        for( $i=10; $i < $count+10; $i+=10 ){
            $this->pageHtml.=' <a class='.(($pageNum*10)==$i?'"page_nums"':'"page_nums_rev"').' href="'.$goToUrl.'?page='.($i/10).($authorId!==0?'&a_id='.$authorId:'').($subId!==0?'&cur_sub=1':'').($courseId!=0?'&c_id='.$courseId:'').'">'.($i/10).'</a>';
        }
        $this->pageHtml.='<a class="'.($i/10-1==$pageNum || $count==0?'invis':'').' page_nums_rev" href="'.$goToUrl.'?page='.($pageNum+1).($authorId!==0?'&a_id='.$authorId:'').($subId!==0?'&cur_sub=1':'').($courseId!=0?'&c_id='.$courseId:'').'">Следующая</a> ';
    }

    public function addNotes(array $notes) : void 
    {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','general.css','email.css']);
        $this->_set_js(['noteControls.js']);
        //конвертировать bbcode в html
        $converter = new BBCode();
        $old_c_title=NULL;//Доп переменая для хранения старого значения c_tilte

        foreach($notes as $k=>$rec){
            $control='';
        /*Кнопки управления в зависимости от доступа*/
           
            if($f3->get('user.isAdmin') || $f3->get('user.id')==$rec['author_id']){
                $control='
                <div class="flex_fe_r_ac">
                    <div class="test_btn mr_r_10">
                        <a class="note_ed" title="Изменить" href="'.$f3->get('BASE').'/editor/note/change/'.$rec['note_id'].'"><img alt="Изменить" src="'.$f3->get("BASE").'/change_test.svg"></a>
                    </div>
                    <div class="test_btn"> 
                        <a class="note_del" title="Удалить" data-nid="'.$rec['note_id'].'" href="'.$f3->get('BASE').'/note/'.$rec['note_id'].'"><img alt="Удалить" src="'.$f3->get("BASE").'/minus_test.svg"></a>
                    </div>
                </div>';
            }
            //Вывод курса если есть
            $course_html='';
            if($old_c_title!==$rec['course_title'] ){//Курс новый?
                if($rec['course_title']!==NULL){//Текущий курс не пустой?
                    if($k-1>=0 && $notes[$k-1]['course_title']!==NULL){//А был ли до текущего курса, другой курс выведен ?
                        $course_html.='</div>';//закрыть текущий
                    }
                    // Вывести новый курс
                    $course_html.='
                    <div class="note" id="course_'.$rec['course_id'].'">
                        <div class="flex_sb_r_ac flex_wr">
                            <div class="flex_fs_r_ac flex_wr">
                                
                                <div class="ava ava-rounded mr_r_10">
                                    <a href="'.$f3->get('BASE').'/?c_id='.$rec['course_id'].'">
                                    <img id="cimg_'.$rec['course_id'].'" src="'.$rec['course_ava_url'].'"></a>
                                </div>
                                
                                <div class="course_cont_block">
                                    <h2 class="note_title mr_r_10">Курс: '.$rec['course_title'].'</h2>
                                    <p>'.($rec['is_private']==1?'&#128274; &#8226; ':'').'Участники: '.$rec['subs_count'].' &#8226; Записи: '.$rec['notes_count'].'</p>
                                    <p>'.$rec['course_article'].'</p>
                                </div>
                            </div>
                            <p>'.$rec['created'].'</p>
                        </div>
                        <hr>
                ';
                }else{
                    //Текущий курс пустой
                    $course_html.='</div>';
                }
                $old_c_title=$rec['course_title'];
            }

            $this->notesHtmlList[] = $course_html.'
            <article class="note" id="note_'.$rec['note_id'].'">
            <div class="flex_sb_r_ac">
                <div class="flex_fs_r flex_wrr">
                    <h2 class="note_title mr_r_10" id="notetitle_'.$rec['note_id'].'">'.$rec['title'].'</h2>
                    '.$control.'
                </div>
                <div class="flex_sb_r_ac">
                    <div class="mr_r_10">
                        <p class="note_auth_name">'.$rec['name'].'</p>
                        <p class="note_date italyc">' . $rec['note_created']. '</p>
                    </div>
                    <a  href="'.$f3->get('BASE').'?a_id='.$rec['author_id'].'">
                        <div class="ava ava-small ava-rounded">
                            <img id="img_'.$rec['author_id'].'" src="'.$rec['ava_url'].'">
                        </div>
                    </a>
                </div>
            </div>
            
            <p class="note_text">'.$converter->convertToHtml($rec['article']).'</p><br>
            <div class="flex_sb_r"><p class="italyc">Теги:'.$rec['tags'].'</p><p>&#128065; '.$rec['views'].' &#8226; &#128172; '.$rec['com_count'].'</p></div>
            <hr>
            <div class="flex_sb_r_ac">
                <a class="note_cmt note_cntrl_btn page_nums" data-nid="'.$rec['note_id'].'" href="'.$f3['BASE'].'/comments/'.$rec['note_id'].'">Открыть комментарии...</a>
                
            </div>
            <div id="cmntblock_'.$rec['note_id'].'" class="cmnt_block"></div>
                
            </article>';
        }
        
    }
    public function addDropSearch($goToUrl, $userSearchInput='', $count=-1) : void{
        if($userSearchInput!=''){ $this->dropSearchHtml="<p><br>Показаны результаты поиска на запрос: '$userSearchInput'".'  <a class="page_nums" href="'.$goToUrl.'">Отменить поиск</a> '; }
        if($count==0){ $this->dropSearchHtml.=' <br><br> Ничего не найдено. Попробуйте изменить запрос!</p>'; }
    }
}


?>