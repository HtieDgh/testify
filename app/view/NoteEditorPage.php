<?php namespace view;

use view\PageAbstract;
use view\FilesPage;

final class NoteEditorPage extends PageAbstract
{
    protected string $content='';
    protected string $filesHtml='';
    protected string $backBtns='';

    protected static $i;

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }
    public function addEditorForm(\Base &$f3,$params,array $noteData,$coursesListHtml) : static
    {
        $this->_set_css(['general.css','flexable.css','editor.css','decor_form.css', 'color_theme.css','buttons.css']);
        $this->_set_js(['editor.js']);
        $this->content='
        <div class="note">

            <div class="flex_c_r">
                <form class="decor" id="editor_form" method="post" action="'.$f3->get('BASE').'/editor/note/'.$params['ed_type'].'/'.$params['id'].'">
                    <div class="form-inner">
                        <p class="italyc"> 
                        <h3>'.($params['ed_type']=='change'?'Изменить запись':'Новая запись').'</h3><br>
                        <h3>Текущая дата: '.date('Y-m-d').'</h3><br>
                        </p>
                
                        <input type="text" name="note_title" placeholder="Заголовок" value="'.$noteData['title'].'" required>
                        <textarea name="note_txt" id="note_txt"  class="edit_txt" placeholder="Текст записи" rows="15" required>'.$noteData['article'].'</textarea>
                        <input type="text" name="note_tags" placeholder="Теги" value="'.$noteData['tags'].'">
                        <div class="mr_t_10">'.$coursesListHtml.'</div>
                        <div class="flex_se_r flex_wr mr_t_10">
                            
                            <a id="insert_fl" href="#" class="page_nums_rev">Вставить фото/файл</a>
                            <a href="#" id="confirm_edit_btn" class="page_nums">Готово</a>
                
                        </div>
                    </div>
                </form>
            </div>
        </div>';
        return $this;
    }
    public function addEditorCourseForm(\Base &$f3, $params, array $course,$msgHtml=['class'=>'','msg'=>'']): static
    {
        $this->_set_css(['general.css','editor.css','decor_form.css','buttons.css']);
        $this->_set_js(['burger.js']);
        $this->content='
        <div class="wrap_block">
            <form class="decor" method="POST" action="'.$f3->get('BASE').'/editor/course/'.$params['ed_type'].'/'.$params['id'].'" enctype="multipart/form-data">
                    <div class="form-inner">
                    <p class="italyc"> 
                    <h3>'.($params['ed_type']=='change'?'Изменить курс':'Новый курс').'</h3><br>
                    <h3>Текущая дата: '.date('Y-m-d').'</h3><br>
                    </p>
                   
                    <div class="flex_c_r">
                        <div class="ava_img flex_c_c">
                            <img id="imgprof_'.$course['id'].'" src="'.$course['ava_url'].'">
                        </div>
                    </div>
                    <br>
                
                    <div class="flex_c_r_ac"><p class="good_txt">Изменить фото</p></div>
                    <div class="flex_c_r_ab">
                        <input class="file_in" id="course_ava" accept="image/bmp,image/jpeg,image/png" name="course_ava" class="UserIn" type="file">
                    </div>

                    <p class="good_txt mr_t_10">Заголовок</p>
                    <input type="text" name="title" required placeholder="Заголовок" value="'.$course['title'].'">

                    <p class="good_txt mr_t_10">Описание</p>
                    <textarea name="description" class="edit_txt" placeholder="Описание" rows="20" required>'.$course['description'].'</textarea>
                    <div class="flex_fs_r_ac mr_t_10">
                        <input class="mr_r_10" type="checkbox" name="is_private" id="is_private"'.($course['is_private']?'checked':'').'>
                        <label for="is_private">Закрытый курс?</label>
                    </div>
                    <div class="flex_c_r mr_t_10 flex_wr">
                        <input class="page_nums_rev" type="submit" value="OK">
                    </div>
                </div>
            </form>
            '.(
                $msgHtml['msg']==''?'':
            '<div class="wrap_block">
                <div class="flex_c_r">
                <p class="fs12_txt '.$msgHtml['class'].'">'.$msgHtml['msg'].'</p><br>
                </div>
            </div>'
            ).'
        </div>';
        return $this;    
    }
    public function addGoBackBtns() : static
    {
        $f3=\Base::instance();
        $this->backBtns='
        <div class="wrap_block">
                <a class="page_nums" href="'.$f3->get('BASE').'/profile">К профилю</a><a class="page_nums" href="'.$f3->get('BASE').'">На главную</a></p>
        </div>';
        return $this;
    }
    public function addFilesList( FilesPage $f) : static
    {
        $this->js=array_merge($this->js, $f->js);
        $this->css=array_merge($this->css, $f->css);
        $this->filesHtml='
        <div>
            '.$f->imgBlock.'
            '.$f->videoBlock.'
            '.$f->filesBlock.'
        </div>
        '.$f->modalFileForm.'
        ';
        return $this;
    }
    //возвращает тег <body>
    public function body(): string
    {
        $f3=\Base::instance();
        return '<body>
            '.
            $this->header.
            $this->burgerMenu.
            '<section class="content">
            
                '.$this->content.'
                '.$this->filesHtml.'
                '.$this->backBtns.'
           
            </section>'.
            $this->errorModalWrap.
            $this->footer.
            $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }
}
?>