<?php namespace view;

use view\PageAbstract;
use Template;

final class FilesPage extends PageAbstract
{
    public string $imgBlock='';
    public string $filesBlock='';
    public string $videoBlock='';
    public string $backBtns='';
    public string $modalFileForm='';
    protected string $filesHtml='';


    protected static $i;

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }

    //добавляет список изображений
    public function addImgBlock(array $userFiles, bool $isAddCntrlBtns=false, bool $isLoadMoreBtn=false) : static 
    {
        $f3=\Base::instance();
        $this->_set_js(['files.js']);
        $this->_set_css(['general.css','buttons.css','files.css']);
		$this->imgBlock='
        <img src="" id="open_full_img" alt="Вспомогательное изображение">
            <div class="wrap_block">
				<div class="photos_block">
					<div>
                        <p>Двойное нажатие по фото увеличит его</p>
                        <p>Выбраные фото:<span id="chosen_ph"></span></p>
						
					</div>
					<div class="file_wrap">
			';
			if(count($userFiles)>0){
				
				foreach($userFiles as $val){
					$this->imgBlock.='<img class="files_img" src="'.$f3->get('BASE').'/'.$val['src'].'" alt="Картинка '.$val['name'].'">';
				}
				
			}else{
				$this->imgBlock.='<p class="good_txt">У вас нет ни одного фото</p>';
			}
		$this->imgBlock.='</div>
                </div>
        </div>
        '.($isAddCntrlBtns?'
        <div class="wrap_block">
            <div class="flex_fe_r_ac flex_wr w_100">
                <a id="photo_del" class="page_nums_red_rev" href="#">Удалить выбранные фото</a>
                '.($isLoadMoreBtn?'<a href="'.$f3->get('BASE').'/all/img/'.$f3->get('user.id').'" data-file-scope="photos_block" class="page_nums_rev get_fl">Просмотреть все фото</a>':'').'
                <a href="img" class="page_nums upl_fl">Загрузить фото</a>
            </div>
        </div>
        ':'');
        return $this;
    }
    public function addFilesBlock(array $userFiles, bool $isAddCntrlBtns=false, bool $isLoadMoreBtn=false) : static 
    {
        $f3=\Base::instance();
        $this->_set_js(['files.js']);
        $this->_set_css(['general.css','buttons.css','files.css']);
		$this->filesBlock='
        <div class="wrap_block">
            <div class="files_block">
                <div class="files_control">
                    <div>
                        <p>Выбраные файлы:<span id="chosen_fl"></span></p>
                    </div>
                   
                </div>
                <div class="file_wrap">
        ';
        if(count($userFiles)>0){
			foreach($userFiles as $val){
                $this->filesBlock.='<p><input class="file_chk" type="checkbox" name="file_url" value="'.$f3->get('BASE').'/'.$val['src'].'"><a download="" href="'.$f3->get('BASE').'/'.$val['src'].'"">'.$val['name'].'</a></p>';
			}
		}else{
			$this->filesBlock.='<p class="good_txt">У вас нет ни одного файла</p>';
		}
		$this->filesBlock.='</div>
                </div>
        </div>
        '.($isAddCntrlBtns?
        '<div class="wrap_block">
            <div class="flex_fe_r flex_wr w_100">
                <a id="file_del" class="page_nums_red_rev" href="#">Удалить выбранные файлы</a>
                '.($isLoadMoreBtn?'<a href="'.$f3->get('BASE').'/all/file/'.$f3->get('user.id').'" data-file-scope="files_block" class="page_nums_rev get_fl">Просмотреть все файлы</a>':'').'
                <a href="file" class="page_nums upl_fl">Загрузить файлы</a>
            </div>
        </div>'
        :'');
        return $this;
    }
    public function addVideoBlock(array $userFiles, bool $isAddCntrlBtns=false, bool $isLoadMoreBtn=false) : static 
    {
        $f3=\Base::instance();
        $this->_set_js(['files.js']);
        $this->_set_css(['general.css','buttons.css','files.css']);
        $this->videoBlock.='
            <div class="wrap_block"> 	
				<div class="video_block mr_b_10"">
					<div class="video_control">
						<div>
							<p>Выбраные видео:<span id="chosen_vd"></span></p>
						</div>
					</div>
					<div class="file_wrap">
			';
			if(count($userFiles)>0){
				foreach($userFiles as $val){
					
                    $this->videoBlock.='<p><input class="video_chk" type="checkbox" name="file_url" value="'.$f3->get('BASE').'/'.$val['src'].'"><a class="video" href="'.$f3->get('BASE').'/'.$val['src'].'"" target="_blank">'.$val['name'].'</a></p>';
					
				}
			}else{
				$this->videoBlock.='<p class="good_txt">У вас нет ни одного видео</p>';
			}
		$this->videoBlock.='</div>
                </div>
        </div>
        '.($isAddCntrlBtns?'
        <div class="wrap_block">
            <div class="flex_fe_r flex_wr w_100">
                <a id="video_del" class="page_nums_red_rev" href="#">Удалить выбранные видео</a>
                '.($isLoadMoreBtn?'<a href="'.$f3->get('BASE').'/all/video/'.$f3->get('user.id').'" data-file-scope="video_block" class="page_nums_rev get_fl">Просмотреть все файлы</a>':'').'
                <a href="video" class="page_nums upl_fl">Загрузить видео</a>
            </div>
        </div>':'');
        return $this;
    }
    public function addGoBackBtns() : static
    {
        $f3=\Base::instance();
        $this->backBtns='
        <div class="wrap_block">
                <a class="page_nums" href="'.$f3->get('BASE').'/profile">В профиль</a><a class="page_nums" href="'.$f3->get('BASE').'">На главную</a>
        </div>';
        return $this;
    }
    public function addModalFileForm() : static
    {
        $this->_set_js(['jquery.modal.min.js','files.js']);
        $this->_set_css(['jquery.modal.min.css','buttons.css','general.css']);

        $this->modalFileForm='
                <div id="modal_file" class="modal">
                    <form enctype="multipart/form-data" method="POST" action="'.\Base::instance()->get("BASE").'/editor/files " class="flex_sb_r flex_wr">
                        <div>
                            <input type="hidden" value="none" id="upld_type" name="upld_type">
                            <p id="upl_type_txt"></p>
                            <input name="user_files[]" class="UserIn mr_t_10" id="upload_files" type="file" multiple required>
                        </div>
                        
                        <div class="flex_c_r mr_t_10 w_100">
                            <input class="page_nums_rev" type="submit" value="Отправить">
                        </div>
                    </form>
                </div>
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
                <div class="ClearFix">'.
                $this->imgBlock.
                $this->filesBlock.
                $this->videoBlock.
                $this->backBtns.
                '</div>
               
            </section>
            '.
            $this->modalFileForm.
            $this->errorModalWrap.
            $this->footer.
            $this->js2link($f3->get("BASE"),$this->js).
        '</body>';
    }
}
?>