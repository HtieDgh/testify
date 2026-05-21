<?php namespace view;

use view\PageAbstract;
use \Template;

final class TestPage extends PageAbstract
{
    protected string $testHtml='';
    protected string $statistics='';
    protected string $searchHtml='';
    protected string $backBtns='';

    protected static $i;

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }  
  
    /**
     * <p>Добавляет разметку Плеера для теста</p>
     *
     * @param  mixed $test_data
     * @param  mixed $testDir
     * @return static
     */
    public function addTest($test_data=null, $testDir=''): static
    {
        $this->_set_css(['flexable.css','general.css','decor_form.css','test.css','jquery.modal.min.css']);
        $this->_set_js(['test_player.js','jquery.modal.min.js']);
        $this->testHtml='';

        if($test_data!=null){
            $this->testHtml.='
                <div class="note">
                    <div class="flex_c_r">
                        <form class="decor" action="">
                            <div class="form-inner">
                            <input type="hidden" id="variant_link" name="variant_link" value="'.$test_data['variant']['unique_url'].'">
                                <h3 class="italyc_txt">'.$test_data['test']['title'].'</h3>
                                <p class="ac_txt mr_t_10">Название варианта: '.$test_data['variant']['title'].'</p>
                                <br>
                                <div class="flex_fe_r_ac">
                                    <p class="mr_r_10">Минимум баллов для прохождения:</p>
                                    <span>'.$test_data['test']['minimum'].'</span>
                                </div>
                                <p class="ac_txt mr_t_10">Текущая дата: '.date('d.m.Y').' </p>

                                <hr>
                                <p class="ac_txt mr_t_10">Данный тест проводится</p>
                                <div class="flex_sb_r_ac">
                                    <p class="mr_r_10">с: </p>
                                    <span>'.$test_data['test']['start'].'</span>
                                </div>
                                <div class="flex_sb_r_ac">
                                    <p class="mr_r_10">по:</p>
                                    <span>'.$test_data['test']['end'].'</span>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
                <div class="note">
                    <p>Описание:</p>
                    <p class="mr_t_10">'.$test_data['test']['description'].'</p>
                </div>
                <div>';
                    
            $q_count = count($test_data['questions']);
            $q_ids=[];
            for ($i=0; $i < $q_count; $i++)
            { 
                $this->testHtml.=$this->_getTestQuestion(
                    $test_data['questions'][$i],
                    $test_data['answers'][$test_data['questions'][$i]['id']],
                    $test_data['files'][$test_data['questions'][$i]['id']],
                    $testDir,
                    $i+1,$q_count
                );
                $q_ids[]=['id'=>$test_data['questions'][$i]['id'],'is_open'=>$test_data['questions'][$i]['is_open']];
            }
                
            $this->testHtml.='</div>
                <div class="note">
                    <div class="flex_c_r ">
                        <a href="#" class="ac_txt start_test_btn" data-questions=\''.json_encode($q_ids).'\' data-q-count="'.$q_count.'">Начать тест</a>
                    </div>
                </div>';
        }
        return $this;
    }
    /**
     * <p>Возвращает разметку вопроса для Плеера теста</p>
     *
     * @param mixed $q=null - test_data['question']
     * @param mixed $a=null - test_data['answer'][q_id]
     * @param mixed $f=null - test_data['file'][q_id]
     * @param mixed $testDir='' - путь до файлов теста
     * @param mixed $q_cur_num=1 - текущий номер вопроса
     * @param mixed $q_count=1 - всего вопросов
     * 
     * @return string DOMstring
     * 
     */
    protected function _getTestQuestion($q, $a, $f, $testDir='', $q_cur_num=1,$q_count=1)
    {
        $out='
        <div id="'.$q['id'].'_q" class="note disp_none transparent">
            <div class="flex_sb_r">
                <form class="decor" action="">
                    <div class="form-inner">
                    <h3>'.$q['title'].'</h3>
                    <p class="mr_t_10">'.$q['text'].'</p>
                    
                    <div class="flex_se_r flex_wr">
                    ';
            
            foreach ($f as $k=>$v) {
                $out.=' <div class="file_wrap mr_t_10">';
                switch ($v['mime']) {
                    case 'image':
                        $out.='<img src="'.$testDir.'/'.$v['file_name'].'" alt="">';
                        break;

                    case 'audio':
                        $out.='<audio src="'.$testDir.'/'.$v['file_name'].'" controls></audio>';             
                        break;

                    case 'video':
                        $out.='<video src="'.$testDir.'/'.$v['file_name'].'" '.($q['is_vid_hidden']==1?'class="invis " id="'.$q['id'].'_'.$k.'_v"':'').' controls></video>';
                        if($q['is_vid_hidden']==1){
                            $out.='<a id="'.$q['id'].'_'.$k.'" href="" class="open_video_btn">Показать?</a>';
                        }
                        break;

                    default:
                        $out.='<p class="alert_txt italyc_txt">Ошибка: файл не определен</p>';
                        break;
                }
                
                $out.='</div>';
            }
            
        $out.='</div><hr>';
        foreach ($a as $v) {
            
            if($q['is_open']!=null){
                $out.='<div class="'.$q['id'].'_qst_answ flex_fs_r_ac">

                    <input type="text" name="answ" data-answerid="'.$v['id'].'" id="'.$q['id'].'_'.$v['id'].'" value="" placeholder="Ваш ответ" class="mr_r_10">
                </div>
                ';
            }else{
                $out.='<div class="'.$q['id'].'_qst_answ flex_fs_r_ac">
                    <input type="checkbox" name="answ"  data-answerid="'.$v['id'].'" id="'.$q['id'].'_'.$v['id'].'" value="'.$v['id'].'" class="mr_r_10">
                    <label for="'.$q['id'].'_'.$v['id'].'">'.$v['text'].'</p>
                </div>';
            }
            
        }
        $out.='
                </div>
                </form>
                <div class="qst_number ac_txt fs12_txt">
                    <span class="cur_all_qsts">'.$q_cur_num.'</span>/<span class="all_qsts">'.$q_count.'</span>
                </div>
            </div>
        </div>';
        return $out;
    }
    /**
     * <p>Вывод всех попыток пользователя у конкретного варианта</p>
    */
    public function addCheck($res_data):static
    {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','general.css','decor_form.css','check.css','editor.css']);
               
        $this->testHtml.='
            <div class="note">
                <div class="flex_c_r">
                    <form class="decor" action="">
                        <div class="form-inner">
                        
                            <h3 class="italyc_txt">'.$res_data[0]['title'].'</h3><br>
                            <p class="ac_txt mr_t_10">Название варианта: '.$res_data[0]['v_title'].'</p>
                            <br>
                            <div class="flex_fe_r_ac">
                                <p class="mr_r_10">Минимум баллов для прохождения:</p>
                                <span>'.$res_data[0]['minimum'].'</span>
                            </div>
                            <p class="ac_txt mr_t_10">Текущая дата: '.date('d.m.Y').' </p>

                            <hr>
                            <p class="ac_txt mr_t_10">Данный тест проводится</p>
                            <div class="flex_sb_r_ac">
                                <p class="mr_r_10">с: </p>
                                <span>'.$res_data[0]['start'].'</span>
                            </div>
                            <div class="flex_sb_r_ac">
                                <p class="mr_r_10">по:</p>
                                <span>'.$res_data[0]['end'].'</span>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
            <div class="note">
                <p>Описание:</p>
                <p class="mr_t_10">'.$res_data[0]['description'].'</p>
            </div>
            <div class="note">
                <div class="flex_sb_r_ac">
                    <h2>Ваши результаты:</h2>
                    <p class="mr_r_10">Баллы</p>
                </div>
                <hr>';
        
            foreach ($res_data as $k=>$v) {
                $this->testHtml.=$this->_GetDetailResultWrap($k,$v);
            }
                $this->testHtml.='
                <a title="Пройти тест еще раз" id="add_qst_btn" class="qst_btn" href="'.$f3->get("SITE_DOMAIN").'/test/'.$res_data[0]['v_link'].'"><img alt="Пройти тест" src="'.$f3->get('BASE').'/add_test.svg"></a>      
            </div>
        ';
        return $this;
    }
    public function addGoBackBtns() : static
    {
        $f3=\Base::instance();
        $this->_set_css(['general.css','buttons.css']);
        $this->backBtns='
        <div class="wrap_block">
            <div class="flex_c_r_ac flex_wr">
                <a class="page_nums" href="'.$f3->get('BASE').'/profile">В профиль</a>
                <a class="page_nums" href="'.$f3->get('BASE').'">На главную</a>
                <a class="page_nums" href="'.$f3->get('BASE').'/profile/tests">К тестам</a>
            </div>
        </div>';
        return $this;
    }
    /**
     * <p>Страница результатов попыток для автора теста</p>
    */
    public function addStatistic($view_data, $test, $variants, $results=[],$pageHtml=''):static
    {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','general.css','decor_form.css','check.css','variants.css']);
        
        $this->statistics='
        <div class="note">
            <div class="flex_c_r">
                <form class="decor" action="">
                    <div class="form-inner">
                    
                        <h3 class="italyc_txt">'.$test['title'].'</h3><br>
                        <div class="flex_fe_r_ac">
                            <p class="mr_r_10">Минимум баллов для прохождения:</p>
                            <span>'.$test['minimum'].'</span>
                        </div>
                        <p class="ac_txt mr_t_10">Текущая дата: '.date('d.m.Y').' </p>

                        <hr>
                        <p class="ac_txt mr_t_10">Данный тест проводится</p>
                        <div class="flex_sb_r_ac">
                            <p class="mr_r_10">с: </p>
                            <span>'.date('d.m.Y H:i:s',strtotime($test['start'])).'</span>
                        </div>
                        <div class="flex_sb_r_ac">
                            <p class="mr_r_10">по:</p>
                            <span>'.date('d.m.Y H:i:s',strtotime($test['end'])).'</span>
                        </div>

                    </div>
                </form>
            </div>
        </div>
        <div class="note">
            <p>Описание:</p>
            <p class="mr_t_10">'.$test['description'].'</p>
        </div>
        '.$this->searchHtml.'
                    
        <div class="note">
            <div class="flex_c_r">                            
                <h3 class="italyc_txt">Варианты теста</h3>
            </div>
            <hr>
            ';
            foreach ($variants as $k=>$v) {
                $this->statistics.=$this->_getStatisticsVariant($v['v_title'],$v['v_link'],$v['q_count'],$test['test_id'],$k+1);
            }
            
            $this->statistics.='
            
        </div>
        <div class="wrap_block">
            <div class="w_100 page_block">
                <hr>
                <p>Перейти на страницу:</p>
                    '.$pageHtml.'
            </div>
        </div>
        <div class="note">
            <div class="flex_sb_r_ac">
                <h2 class="mr_t_10">Результаты пользователей: '.$view_data['s_cancel'].'</h2>
                <p class="mr_r_10">Баллы</p>
            </div>
        <hr>
        ';
        foreach ($results as $k=>$v) {
            $this->statistics.=$this->_GetDetailResultWrap($k,$v);
        }
        $this->statistics.='</div>
        <div class="wrap_block">
            <div class="page_block">
                <p>Перейти на страницу:</p>
                '.$pageHtml.'
            </div>
        </div>';
        return $this;
    }
    /**
     * <p>Добавляет разметку поиска</p>
     *
     * @param  mixed $searchUrl
     * @return static
     */
    public function addSearch($searchUrl='') : static
    {
        $this->_set_css(['general.css','flexable.css','search.css']);
        \Base::instance()->set('parameter',$searchUrl);
        $this->searchHtml='<div class="wrap_block">
        '.Template::instance()->render('search.htm').'
        </div>';
        return $this;
    }
    /**
     * <p>Возвращает разметку Варианта для страницы статистики</p>
    */
    protected function _getStatisticsVariant($v_title,$v_link,$v_qount,$test_id,$cur_num=1){
        $f3=\Base::instance();
        return '<div class="flex_sb_r_ac stat_variant">
                    <p class="fs14_txt answ_number mr_r_10">'.$cur_num.'</p> 

                    <a href="'.$f3->get("BASE").'/test/statistics/'.$test_id.'/'.$v_link.'" class="title mr_r_10">'.$v_title.'</a>

                    <a title="Количество вопросов в варианте, нажмите для редактирования" class="editqv_btn fs12_txt mr_l_10" href="'.$f3->get("BASE").'/editor/questions/'.$v_link.'">'.$v_qount.'</a>
                </div>';
    }
    protected function _GetDetailResultWrap($k,$v)
    {
        return '
            <div class="flex_sb_r_ac flex_wr test_line mr_t_10">
                <span class="fs14_txt">'.($k+1).'</span>
                '.(isset($v['user_name'])? '<p>'.$v['user_name'].'</p><p>'.$v['v_title'].'</p>' : '' ).'
                <p>'.date('d.m.Y H:i:s',strtotime($v['created'])).'</p>
                '.(
                    $v['status']>0 ? 
                    '<p class="italyc_txt good_txt">Сдан</p>'
                    :
                    '<p class="italyc_txt alert_txt">Не сдан</p>'
                ).'
                    
                    <div class="fs14_txt">
                        <span >'.$v['sum'].'</span>/<span>'.$v['minimum'].'</span>
                    </div>
                
            </div>
        ';
        
    }
    //возвращает тег <body>
    public function body(): string
    {
        $f3=\Base::instance();
        return '<body>
            '.
            $this->header.
            $this->burgerMenu.
            '<section class="content">'.
            $this->testHtml.
            $this->statistics.
            $this->backBtns.
            '</section>'.
            $this->errorModalWrap.
            $this->footer.
            $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }
}
?>