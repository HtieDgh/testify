<?php
//Файл заменяет Класс для загрузки файлов
class Uploads{
    //Пути директорий относительно контроллера 
    public 
        $file_dir,
        $ava_dir;
    //$ext - Допустимые форматы файлов. Сохранить можно только фото, аудио и видео.
	public static $ext;
	public static function GetUserPath($user_test_data_path,$login)
	{
		return $user_path.'/'.md5($login).'/';
	}
	public function __construct($user_test_data_path,$login){
		
		$this->file_dir=static::GetUserPath($user_test_data_path,$login);
        $this->ava_dir='img/user_avas/u_id_'.$user_data['id'];
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
     * @param string index в масиве FILES
     * @return string сообщение об ошибке или пустая строка если ошибок нет
    */
	public function UploadFile($file_data,$test_link){
		$out='';
		if(in_array( $file_data['type'], static::$ext))
		{
            $tmp_file_name = $file_data["tmp_name"];
			$dest_file_name = $this->file_dir.$test_link.'/'.$file_data["name"];

			if(!is_dir($this->file_dir.$test_link.'/'))
			{
				mkdir($this->file_dir.$test_link.'/', 0777, true);
			}
            move_uploaded_file($tmp_file_name, $dest_file_name);
        }else{
            $out='Небыл загружен файл, проверьте формат файла для: '.$file_data["name"];
		}

		return $out;
	}
	/**
	 * <p>Помещает в текущую директорию файл test.txt содержащий json строку данных теста</p>
	 * @param string json строка данных теста
	 * @return mixed результат работы file_put_contents()
	*/
	public function UploadJSONTestData($json_str,$test_link){
		if(!is_dir($this->file_dir.$test_link.'/')) {
			mkdir($this->file_dir.$test_link.'/', 0777, true);
		}
		return file_put_contents($this->file_dir.$test_link.'/'.'test.txt',$json_str);
	}

	/**
	 * <p>Удаляет тест из файловой системы по его id</p>
	 * @param string test_link
	*/
	public function DeleteTest($test_link)
	{
		$this->_removeDir($this->file_dir.$test_link.'/');
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