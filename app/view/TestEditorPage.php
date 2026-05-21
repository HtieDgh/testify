<?php namespace view;

use Base;
use view\PageAbstract;

final class TestEditorPage extends PageAbstract
{
    protected string $testEditorHtml='';
    protected string $questionEditor='';

    protected static $i;

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }
    /**
     * <p>Добавить DOMString Редактора теста.</p>
     * @param array Данные теста которые необходимо отредактировать.
     * @param array Данные Вариантов которые необходимо отредактировать.
     * @return string
    */
    public function addTestEditor($td=null,$vd=null) : static
    {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','general.css','decor_form.css','editor.css','variants.css']);
        $this->_set_js(['test_edit.js']);

        $this->testEditorHtml=$this->_getEditorTestTitle($td);

        $this->testEditorHtml='<section class="content">
                <div>
                    <p class=" ar_txt">Шаг 1/3</p>
                </div>
                '.$this->testEditorHtml.'
                '.$this->variantEditor($vd).'
                <div class="note">
                    <div class="flex_c_r ">
                        <a href="'.$f3->get("BASE").'" id="confirm_edit_btn" class="confirm_edit_btn ac_txt">Следующий шаг</a>
                    </div>  
                </div>
                <div class="note">
                    <div class="flex_c_r ">
                        <a href="'.$f3->get("BASE").'/profile/tests/my" id="cancel_edit_btn" class="confirm_edit_btn_alt ac_txt">Отмена</a>
                    </div>
                </div>
                
            </section>';
        return $this;
    }
     /**
     * <p>Возвращает заголовок редактора теста</p>
     * @param array test_data['test'] - данные заголовка теста test_cu - новый или старый тест
     * @return string DOMString
    */
    protected function _getEditorTestTitle($test=null,$variant_link='0')
    {
        if($test===null){
            $test=[
                'id'=>'0',
                'title'=>'',
                'minimum'=>'1',
                'description'=>'',
                'datetime_start'=>date("Y-m-d H:i"),
                'datetime_end'=>date("Y-m-d H:i",time()+60*60*24*7)
            ];
        }
        return '<div class="note">
                    <div class="flex_c_r">
                        <form class="decor" method="post" action="/">
                            <div class="form-inner">
                                <input id="test_title" class="fs12_txt" type="text" name="test_title" placeholder="Название Теста" value="'.$test['title'].'" required>
                                <div class="flex_fe_r_ac">
                                    <label class="mr_r_10" for="minimum">Минимум баллов для прохождения:</label>
                                    <input id="minimum" type="number" name="minimum" min="1" max="9999" value="'.$test['minimum'].'">
                                </div>
                                <textarea name="test_description" id="test_description"  class="edit_txt" placeholder="Описание теста" rows="5" required>'.$test['description'].'</textarea>
                                
                            
                                <p class="ac_txt">Текущая дата: '.date('d.m.Y').' </p>
                                <p class="mr_l_10">Начать тестирование с:</p>
                                <input type="datetime-local" id="start" name="start" value="'.$test['datetime_start'].'" min="'.date("Y-m-d H:i").'" max="'.date(DATE_ATOM,time()+60*60*24*365).'">
                                <p class="mr_l_10">по:</p>
                                <input type="datetime-local" id="end" name="end" value="'.$test['datetime_end'].'" min="'.date("Y-m-d H:i").' max="'.date("Y-m-d H:i",time()+60*60*24*365).'">
                                <br>
                                
                                <input type="hidden" id="test_id" value="'.$test['id'].'">
                            </div>
                        </form>
                    </div>
                </div>';
    }

    /**
     * <p>Возвращает DOMString Редактора Варианта.
     * @param array Данные Варианта которые необходимо отредактировать.
     * @return string
    */
    public function variantEditor($vd=[],$test_id=0) {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','general.css','decor_form.css','editor.css']);
        $this->_set_js(['test_edit.js']);

        $out='            
        <div class="note">
        <div class="flex_c_r">
            <form class="decor" method="post" action="/">
                <div class="form-inner">
                
                    <h3 class="italyc_txt">Варианты теста</h3>
                    <p class="mr_t_10">
                        Название варианта может быть любым. Рекомендуется выбирать название в форме <span class="italyc_txt">Вариант <Номер варианта></span>
                    </p> 
                    <br><br>';
        foreach ($vd as $k=>$v) {
            $out.=$this->_getEditorVariant($v,$test_id,$k+1);
        }
        $out.='<a  title="Добавить вариант теста" class="qst_btn add_elem_btn" href="'.$test_id.'"><img alt="Добавить вариант теста" src="'.$f3->get('BASE').'/add_test.svg"></a>
        
                </div>
                <input id="v_countjs" type="hidden" value="'.count($vd).'">
            </form>
        </div>
        </div>
        ';
        return $out;
    }
    /**
     * <p>Возвращает DOMString Редактора вопросов теста. Для идентификации ответа в закрытом вопросе: 1(вопрос)_1(id ответа)_qst_answ</p>
     * @param array Данные вараинта которые необходимо отредактировать.
     * @return static
    */
    public function addQuestionEditor($qst_data):static 
    {
        $f3=\Base::instance();
        $this->_set_css(['flexable.css','general.css','decor_form.css','editor.css','jquery.modal.min.css','modal.css']);
        $this->_set_js(['test_question_edit.js','jquery.modal.min.js']);
        
        
        //Заголовок теста
        $this->questionEditor.='
        <div class="content"> 
            <div>
                <p class=" ar_txt">Шаг 2/3</p>
            </div>
            '.$this->_getEditorVariantTitle($qst_data['variant']);
        $q_count=count($qst_data['questions']);
        if($q_count==0){
            $this->questionEditor.=$this->_getEditorQuestion($f3);
        }else{
            for ($i=0; $i < $q_count; $i++) { 
                $this->questionEditor.=$this->_getEditorQuestion(
                    $f3,
                    $qst_data['questions'][$i],
                    $qst_data['answers'][$qst_data['questions'][$i]['id']],
                    $qst_data['files'][$qst_data['questions'][$i]['id']],
                    $i+1,$q_count
                );
            }
        }

        $this->questionEditor.='
        <a title="Добавить вопрос: Двойной клик - добавить уже созданый" id="add_qst_btn" class="qst_btn" href="#"><img alt="Добавить вопрос" src="'.$f3->get('BASE').'/add_test.svg"></a>
        
            <div class="note">
                <div class="flex_c_r ">
                    <a href="" id="confirm_edit_btn" class="confirm_edit_btn ac_txt">Следующий шаг</a>
                </div>
            </div>
        
        </div>
        <div id="ex1" class="modal">
            <p class=" ar_txt">Шаг 3/3</p>
            <p>
                Ссылка на проохождение варианта теста: <a href="" id="a_test_link"></a><br><br>
                Прохождение варианта будет доступно только для тех, у кого есть эта ссылка!
            </p>
            <br>
            <p>
                Архив с вариантом теста был закружен на ваше устройство в качестве резервной копии. Сохраните его в удобном для вас месте, позже вы сможете изменить этот тест, загрузив этот архив на странице профиля.
                <br><br>
            </p>
            
            <a href="'.$f3->get("BASE").'/profile/tests/my">Выйти из редактора</a>
        </div>';
        return $this;
    }
    protected function _getEditorVariantTitle($var) {
        return '<div class="note">
            <h3>Редактировние варианта: '.$var['title'].'</h3>
            <p>Вы можете добавить вопрос нажав на кнопку +</p>
            <input type="hidden" value="'.$var['unique_url'].'" id="variant_link">
        </div>';
    }
    /**
     * <p>Возвращает разметку Вопроса</p>
     * @param array $q - QuestionData['question'][i] данные текущего вопроса
     * @param array $f - QuestionData['file'][i] информация о файлах
     * @param array $a - QuestionData['answer'][i] данные ответов на этот вопрос
     * @param int $q_cur_num - текущий номер вопроса
     * @param int $q_count - всего вопросов в тесте
     * @return string DOMString
    */
    protected function _getEditorQuestion(\Base &$f3,$q=[],$a=[],$f=[],$q_cur_num=1,$q_count=1)
    {
        if(empty($q)){
            $q=[
                'id'=>0,
                'title'=>'',
                'is_open'=>0,
                'text'=>'',
                'is_vid_hidden'=>0,
                'fine'=>'0'
            ];
            $a[0]=[
                'id'=>0,
                'text'=>'',
                'price'=>'0'
            ];
            $f=[];
        }

        $html='
        <div id="'.$q['id'].'_q" class="questionjs note">
            <div class="flex_sb_r">
                <form class="decor" method="post" action="new_test/">
                    <div class="form-inner">
                        <input type="hidden" name="qidjs" class="qidjs" value="'.$q['id'].'">
                        <input type="text" class="fs12_txt qst_title" name="qst_title" placeholder="Заголовок" value="'.$q['title'].'" required>
                        <div class="flex_sb_r flex_wr">
                            <div class="qst_type flex_sb_r_ac">
                                <div class="flex_fs_r_ac">
                                    <input  class="mr_r_10" type="radio" id="'.$q['id'].'_type" name="type" value="0" '.($q['is_open']!==null?'':'checked').' />
                                    <label for="'.$q['id'].'_type">Закрытый</label>
                                </div>

                                <div class="flex_fs_r_ac">
                                    <input class="mr_r_10" type="radio" id="'.$q['id'].'_2_type" name="type" value="1" '.($q['is_open']!==null?'checked':'').'/>
                                    <label for="'.$q['id'].'_2_type">Открытый</label>
                                </div>
                            </div>
                            
                        </div>

                        <textarea name="note_txt" class="edit_txt qst_txt" placeholder="Текст вопроса" rows="4" required>'.$q['text'].'</textarea>
                        <div class="flex_sb_r_ac flex_wr">
                            <input class="fileinjs UserIn" id="'.$q['id'].'_files" accept="image/*,video/*,audio/*" name="user_files[]" type="file" multiple>
                            <div class="flex_fs_r_ac">

                                <input class="mr_r_10" type="checkbox" name="is_vid_hidden" id="'.$q['id'].'_is_vid_hidden" '.($q['is_vid_hidden']==1?'checked':'').'>
                                <label for="'.$q['id'].'_is_vid_hidden">Скрыть видео?</label>
                            </div>
                        </div>';
                            //Файлы
                            $f_count=count($f);
                            if($f_count>0){
                                $html.='<p>Файлы ниже были использованы в вопросе:<br><br></p>';
                                for ($i=0; $i < $f_count; $i++) { 
                                    $html.='<p class="italyc_txt">'.$f[$i]['file_name'].'</p>';
                                }
                                $html.='<p class="alert_txt"><br>Перед тем как приступить к Следующему шагу выберете эти файлы заново</p>';
                            }
                            
                        
                            
                        $html.='<div class="mr_t_10">
                            <p class="ac_txt">Варианты ответов:</p>';
                            $answ_count=count($a);
                            $html.='
                            <input id="'.$q['id'].'_answ_count" type="hidden" name="answ_count" value="'.$answ_count.'">
                            <hr id="'.$q['id'].'_answ_list">
                            ';
                            
                                //Перебор ответов
                                if($q['is_open']!==null){//Если вопрос открытый
                                    $html.='
                                    <div class="qstanswjs flex_fs_r_ac">
                <input type="hidden" name="answidjs" class="answidjs" value="'.$a[0]['id'].'">
                
                                        <textarea rows="1" class=" mr_r_10" name="'.$q['id'].'_0_qst_answ" placeholder="Текст ответа" value="'.$a[0]['text'].'" required>'.$a[0]['text'].'</textarea>
                                        
                                        <input type="number" name="price" min="-1000" max="1000" value="'.$a[0]['price'].'">
                                        <input type="number" name="fine" style="display:block;" min="-1000" max="1000" value="'.$q['fine'].'">
                        
                                    </div>';
                                }else{//закрытый
                                    foreach ($a as $k=>$v) {
                                        
                                        $html.='
                                        <div class="qstanswjs flex_fs_r_ac">
                                            <input type="hidden" name="answidjs" class="answidjs" value="'.$v['id'].'">
                                        
                                            <span class="fs14_txt answ_number mr_r_10">'.($k+1).'</span>
                                            <textarea rows="1" class=" mr_r_10" name="'.$q['id'].'_'.
                                            $k.'_qst_answ" placeholder="Текст ответа" value="'.
                                            $v['text'].'" required>'.$v['text'].'</textarea>
                                            
                                            <input type="number" name="price" min="-1000" max="1000" value="'.$v['price'].'">
                                            <input type="number" name="fine" min="-1000" max="1000" value="'.$q['fine'].'">
                                            <a title="Удалить вариант ответа" class="qst_btn_alt del_answ_btn" href="'.$q['id'].'"><img alt="Удалить вариант ответа" src="'.$f3->get('BASE').'/minus_test.svg"></a>
                                        
                                        </div>
                                        ';
                                    }
                                    $html.='<a  title="Добавить вариант ответа" class="qst_btn add_answ_btn" href="'.$q['id'].'"><img alt="Добавить вариант ответа" src="'.$f3->get('BASE').'/add_test.svg"></a>';
                                }
                            $html.='
                            
                        </div>
                        
                    </div>
                </form>
                <div class="flex_fs_c_ac">
                    <div class="qst_number ac_txt fs12_txt">
                        <span class="cur_all_qsts">'.$q_cur_num.'</span>/<span class="all_qsts">'.$q_count.'</span>
                    </div>
                    
                    <a title="Удалить вопрос из теста" class="qst_btn_alt del_qst_btn" href="'.$q['id'].'">
                        <div class="flex_c_c">
                            <img alt="Удалить вопрос из теста" src="'.$f3->get('BASE').'/minus_test.svg">
                        </div>
                    </a>
                    
                </div>
                
            </div>
        </div>';
        return $html;
    } 
     /**
     * <p>Возвращает разметку Варианта для Редактора теста</p>
    * @param array $vd - данные варианта
    * @param int $test_id - текущий id теста
    * @param int $cur_num=1 - текущий номер варианта
    * 
    * @return string DOMstring
    * 
    * @see variantEditor()
    */
    protected function _getEditorVariant($vd,$test_id,$cur_num=1){
        $f3=\Base::instance();
        return '
        <div class="'.$test_id.'_test_var flex_fs_r_ac">
            <div class="flex_c_r_ac">
                <input type="radio" class="chosen_variant mr_r_10" name="variant" value="'.$vd['unique_url'].'">
                <span class="fs14_txt answ_number mr_r_10">'.$cur_num.'</span>
            </div>               

            <textarea rows="1" class="title mr_r_10" name="'.$vd['id'].'_id_var" placeholder="Название Варианта" value="'.
            $vd['title'].'" required>'.$vd['title'].'</textarea>
            
            <input type="hidden" name="variant_id" class="variant_id"  value="'.$vd['id'].'">


            <a title="Удалить вариант теста" class="qst_btn_alt del_answ_btn" href="'.$f3->get('BASE').'/delete/variant/'.$vd['unique_url'].'"><img alt="Удалить вариант теста" src="'.$f3->get('BASE').'/minus_test.svg"></a>

            <a title="Количество вопросов в варианте, нажмите для редактирования" class="editqv_btn fs12_txt mr_l_10" href="'.$f3->get('BASE').'/editor/questions/'.$vd['unique_url'].'">'.$vd['q_count'].'</a>
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
            $this->testEditorHtml.
            $this->questionEditor.
            $this->errorModalWrap.
            $this->footer.
            $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }
}
?>