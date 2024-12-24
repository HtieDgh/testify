<?php
//Файл заменяет Класс для загрузки файлов
class Uploads{
    //Пути директорий относительно контроллера 
    public 
        $file_dir;
    //$ext - Допустимые форматы файлов. Сохранить можно только фото, аудио и видео.
	public static $ext;

	public function __construct($user_test_data_path,$login){
		
		$this->file_dir=$user_test_data_path.'/'.md5($login).'/';

        if(!is_dir($this->file_dir)) {
			mkdir($this->file_dir, 0777, true);
		}
        static::$ext=[
                'image/bmp',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/svg',
                'video/mpeg',
                'video/mp4',
                'video/webm',
                'video/x-flv',
                'video/3gpp',
                'video/3gpp2',
                'audio/aac',
                'audio/ogg',
                'audio/mpeg',
                'audio/webm',
                'audio/x-ms-wma',
                'audio/x-ms-wax'
        ];
    }
    /**
     * <p>Сохраняет переданые файлы на сервер</p>
     * @param array file_data файл в масиве FILES
     * @param string variant_link ссылка на вариант
     * @return string сообщение об ошибке или пустая строка если ошибок нет
    */
	public function UploadFile($file_data,$variant_link){
		$out='';
		if(in_array( $file_data['type'], static::$ext))
		{
            $tmp_file_name = $file_data["tmp_name"];
			$dest_file_name = $this->file_dir.$variant_link.'/'.$file_data["name"];

			if(!is_dir($this->file_dir.$variant_link.'/'))
			{
				mkdir($this->file_dir.$variant_link.'/', 0777, true);
			}
            move_uploaded_file($tmp_file_name, $dest_file_name);
        }else{
            $out='Небыл загружен файл, проверьте формат файла для: '.$file_data["name"];
		}

		return $out;
	}
	/**
	 * <p>Помещает в текущую директорию файл test.json содержащий json строку данных теста, вопросов, файлов и ответов</p>
	 * @param string json строка данных теста
	 * @param string json строка данных теста
	 * @return mixed результат работы file_put_contents()
	*/
	public function UploadJSONTestData($json_str,$variant_link){
		if(!is_dir($this->file_dir.$variant_link.'/')) {
			mkdir($this->file_dir.$variant_link.'/', 0777, true);
		}
		return file_put_contents($this->file_dir.$variant_link.'/'.'test.json',$json_str);
	}
	/**
	 * <p>Возвращает путь и имя файла резервной копии, доступной для скачивания</p>
	*/
	public function GetBackupPath($variant_link) {
		return ['link'=>$this->file_dir.$variant_link.'/variant_'.date('is').'.zip',
		'folder'=>$this->file_dir.$variant_link.'/'];
	}

	/**
	 * <p>Удаляет файлы варианта из файловой системы по его variant_link или test_id</p>
	 * @param string link
	*/
	public function DeleteVariant($variant_link)
	{
		$this->_removeDir($this->file_dir.$variant_link.'/');
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

}
?>