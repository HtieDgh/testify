<?php namespace model;
//Файл заменяет Класс для загрузки файлов
class Uploads{
    //Пути директорий относительно контроллера 
    public 
        $test_dir,//персональный каталог тестов 
		$img_dir,//персональный каталог картинок
		$file_dir,//персональный каталог файлов
		$video_dir,//персональный каталог видео
		$ava_dir,//каталог аватарок аккаунтов
		$course_ava_dir,//каталог аватарок курсов
		$hashed_dir,//персональный каталог пользователя
		$static_img_dir,//каталог со статическими файлами картинок (используется для путей аватарок)
		$users_dir,
		$fileCount;//общий каталог со всеми файлами пользователей
    //$ext - Допустимые форматы файлов. Сохранить можно только фото, аудио и видео с определеным MIME.
	public static $ext=[
		'IMG'=>[
			'image/bmp'=>'.bmp',
			'image/jpeg'=>'.jpg',
			'image/png'=>'.png',
			'image/gif'=>'.gif',
			'image/svg+xml'=>'.svg',
			'image/svg'=>'.svg'
		],
		'FILE'=>[			
			'image/bmp'=>'.bmp',
			'image/jpeg'=>'.jpg',
			'image/png'=>'.png',
			'image/gif'=>'.gif',
			'image/svg+xml'=>'.svg',
			'image/svg'=>'.svg',
			'video/mpeg'=>'.mpeg',
			'video/mp4'=>'.mp4',
			'video/webm'=>'.webm',
			'video/x-flv'=>'.flv',
			'video/3gpp'=>'.3gpp',
			'video/3gpp2'=>'.3gpp',
			'audio/aac'=>'.aac',
			'audio/ogg'=>'.ogg',
			'audio/mpeg'=>'.mp3',
			'text/plain'=>'.txt',
			'text/xml'=>'.xml',
			'application/vnd.ms-excel'=>'.xlsx',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'.xlsx',
			'application/msword'=>'.docx',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'.docx',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'.pptx',
			'application/vnd.ms-powerpoint'=>'.pptx',
			'application/x-shockwave-flash'=>'.swf',
			'application/x-rar-compressed'=>'.rar'
		],
		'VIDEO'=>[
			'video/mpeg'=>'.mpeg',
			'video/mp4'=>'.mp4',
			'video/webm'=>'.webm',
			'video/x-flv'=>'.flv',
			'video/3gpp'=>'.3gpp',
			'video/3gpp2'=>'.3gpp',
		],
		'AUDIO'=>[
			'audio/aac'=>'.aac',
			'audio/ogg'=>'.ogg',
			'audio/mpeg'=>'.mp3'
		],
		'AVA'=>[
			'image/bmp'=>'.bmp',
			'image/jpeg'=>'.jpg',
			'image/png'=>'.png'
		]
	];
	public function __construct(string $users_dir,string $login){
		$this->users_dir=$users_dir;

		$this->hashed_dir=$this->users_dir.'/'.md5($login);

		$this->img_dir=$this->hashed_dir.'/imgs/';
		$this->file_dir=$this->hashed_dir.'/files/';
		$this->video_dir=$this->hashed_dir.'/video/';
		$this->static_img_dir='app/static/img/';
		$this->ava_dir='user_avas/';
		$this->course_ava_dir='course_avas/';
		$this->test_dir=$this->hashed_dir.'/tests/';		
    }
    /**
     * <p>Сохраняет переданые файлы по их имени на сервер</p>
     * @param array file_data файл в масиве FILES
     * @param string dir каталог для файла
     * @param array ext массив MIME типов для проверки соответствия файла списку разрешенных
     * @return string сообщение об ошибке или пустая строка если ошибок нет
    */
	public function uploadFile(array $file_data, string $dir)
	{
		$tmp_file_name = $file_data["tmp_name"];
		$dest_file_name = $dir.'/'.$file_data["name"];

		if(!is_dir($dir.'/'))
		{
			mkdir($dir.'/', 0777, true);
		}
		move_uploaded_file($tmp_file_name, $dest_file_name);
	}
	public static function isFileValid(array $file_data, array $ext) : bool 
	{
		return isset( $ext[$file_data['type']] );
	}
		
	public function isNotUserFile(string $file) : bool
	{
		return strpos($file,$this->hashed_dir)===FALSE;
	}
	public function deleteFiles(array $filePaths) {
		foreach($filePaths as $file){
			unlink($file);
		}
	}
	/**
	 * getUserFiles

	 * @param  int $start 
	 * @param  int $end
	 * @param  string $dir принимает путь до деректории относительно корня 
	 * @return array массив с src и именами файлов
	 * @see $this->img_dir, $this->file_dir, $this->video_dir
	 */
	public function getUserFiles(string $dir,int $start=0,int $end=9) : array 
	{
		//проверка директорий и их создание если их нет
		if(!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		$out=scandir($dir,SCANDIR_SORT_DESCENDING);
		$this->fileCount=count($out)-2;
		if($out===false) return array();
		$out=array_diff($out, array('..', '.'));
		foreach ($out as $k => $v) {
				$out[$k]=['name'=>$v,'src'=>$dir.$v];
		}
		
		return array_slice($out,$start,$end-$start);
	}
	public function getFileCount(string $dir) : int
	{
		$out=scandir($dir);
		
		if($out===false){
			return 0;
		}
		return count($out)-2;// ./ ../ не считать
	}

	/**
	 * <p>Помещает в текущую директорию файл test.json содержащий json строку данных теста, вопросов, файлов и ответов</p>
	 * @param string json строка данных теста
	 * @param string json строка данных теста
	 * @return mixed результат работы file_put_contents()
	*/
	public function uploadJSONTestData($json_str,$variant_link){
		if(!is_dir($this->test_dir.$variant_link.'/')) {
			mkdir($this->test_dir.$variant_link.'/', 0777, true);
		}
		return file_put_contents($this->test_dir.$variant_link.'/'.'test.json',$json_str);
	}
	/**
	 * <p>Возвращает путь и имя файла резервной копии, доступной для скачивания</p>
	*/
	public function getBackupPath($variant_link) {
		return ['link'=>$this->test_dir.$variant_link.'/variant_'.date('is').'.zip',
		'folder'=>$this->test_dir.$variant_link.'/'];
	}

	/**
	 * Удаляет файлы варианта из файловой системы по его variant_link
	 * @param string link
	*/
	public function deleteVariant($variant_link)
	{
		$this->_removeDir($this->test_dir.$variant_link.'/');
	}
	protected function _removeDir($path)
	{
		// если путь существует и это папка
		if ( file_exists($path) AND is_dir($path) ) 
		{
			$dir = opendir($path);
			while(( $element = readdir($dir) ) !== false) {
			// удаление содержимого папки
				if ( $element != '.' AND $element != '..' )  {
					$tmp = $path . '/' . $element;
					chmod( $tmp, 0777 );
					//Рекурсия на подпапки
					if ( is_dir( $tmp ) ) {
						$this->_removeDir( $tmp );
					
					} else {
						//Удаление файла
						unlink( $tmp );
					}
				}
			}
			
			closedir($dir);
			// Удаление верхней папки
			if ( file_exists( $path ) ) {
				rmdir( $path );
			}
		}
	}


	public static function getAcceptedMIMEList() 
	{
		$out['IMG']=implode(',',array_keys(static::$ext['IMG']));
		$out['FILE']=implode(',',array_keys(static::$ext['FILE']));
		$out['VIDEO']=implode(',',array_keys(static::$ext['VIDEO']));
		return $out;
	}
}
?>