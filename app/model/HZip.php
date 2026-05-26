<?php namespace model;

final class HZip
{

  /**
   * <p>Add files and sub-directories in a folder to zip file.</p>
   * @param string $folder
   * @param ZipArchive $zipFile
   * @param int $exclusiveLength Number of text to be exclusived from the file path.
   */

  private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
    $handle = opendir($folder);
    while (false !== $f = readdir($handle)) {
      if ($f != '.' && $f != '..') 
      {
        $filePath = "$folder/$f";
        // Remove prefix from file path before add to zip.
        $localPath = substr($filePath, $exclusiveLength);
      if (is_file($filePath)) 
      {
        $zipFile->addFile($filePath, $localPath);
      } elseif (is_dir($filePath)) 
      {
        // Add sub-directory.
        $zipFile->addEmptyDir($localPath);
        self::folderToZip($filePath, $zipFile, $exclusiveLength);
      }
      }
    }

    closedir($handle);

  }



  /**
   * <p>Zip a folder (include itself).
   * Usage:
   *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
   *</p>
   * @param string $sourcePath Path of directory to be zip.
   * @param string $outZipPath Path of output zip file.
   */

  public static function zipDir($sourcePath, $outZipPath)
  {

    $pathInfo = pathInfo($sourcePath);
    $parentPath = $pathInfo['dirname'];
    $dirName = $pathInfo['basename'];

    $z = new \ZipArchive();
    $z->open($outZipPath, \ZipArchive::CREATE);
    $z->addEmptyDir($dirName);
    self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
    $z->close();
  }
  
  // /**
  //  * Create folder from zip
  //  *
  //  * @param  mixed $srcZipFilePath - $_FILES['userfile']['tmp_name']
  //  * @param  mixed $destFolderPath - folder, like  "/your/new/destination"
  //  * @return void
  //  */
  // public static function zipToFolder(string $srcZipFilePath, string $destFolderPath) {
  //   $z = new \ZipArchive();
    
  //   if ($z->open($srcZipFilePath) === true) {
  //     for($i = 0; $i < $z->numFiles; $i++) {
  //         $filename = $z->getNameIndex($i);
  //         $fileinfo = pathinfo($filename);
  //         copy("zip://".$srcZipFilePath."#".$filename, $destFolderPath.'/'.$fileinfo['basename']);
  //     }                   
  //     $z->close();
  //     return $destFolderPath.'/'.$fileinfo['basename'];        
  //   }
  // }

  // public static function extractSubdirTo($zipPath,$destination, $subdir)
  // {
  //   $errors = array();
  //   $z=new \ZipArchive();
  //   $z->open($zipPath);

  //   // Prepare dirs
  //   $destination = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $destination);
  //   $subdir = str_replace(array("/", "\\"), "/", $subdir);

  //   if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, "UTF-8") * -1) != DIRECTORY_SEPARATOR)
  //     $destination .= DIRECTORY_SEPARATOR;

  //   if (substr($subdir, -1) != "/")
  //     $subdir .= "/";

  //   // Extract files
  //   for ($i = 0; $i < $z->numFiles; $i++)
  //   {
  //     $filename = $z->getNameIndex($i);

  //     if (substr($filename, 0, mb_strlen($subdir, "UTF-8")) == $subdir)
  //     {
  //       $relativePath = substr($filename, mb_strlen($subdir, "UTF-8"));
  //       $relativePath = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $relativePath);

  //       if (mb_strlen($relativePath, "UTF-8") > 0)
  //       {
  //         if (substr($filename, -1) == "/")  // Directory
  //         {
  //           // New dir
  //           if (!is_dir($destination . $relativePath))
  //             if (!@mkdir($destination . $relativePath, 0755, true))
  //               $errors[$i] = $filename;
  //         }
  //         else
  //         {
  //           if (dirname($relativePath) != ".")
  //           {
  //             if (!is_dir($destination . dirname($relativePath)))
  //             {
  //               // New dir (for file)
  //               @mkdir($destination . dirname($relativePath), 0755, true);
  //             }
  //           }

  //           // New file
  //           if (@file_put_contents($destination . $relativePath, $z->getFromIndex($i)) === false)
  //             $errors[$i] = $filename;
  //         }
  //       }
  //     }
  //   }
  //   $z->close();
  //   return $errors;
  // }

  /**
 * Извлекает все файлы из ZIP архива, включая файлы во вложенных папках
 * 
 * @param string $zipPath Путь к ZIP архиву
 * @param string $extractTo Путь для извлечения файлов
 * @return array Возвращает массив извлеченных файлов или false при ошибке
 */
  public static function extractZipFiles($zipPath, $extractTo) : array
  {
    // Проверяем существование ZIP файла
    if (!file_exists($zipPath)) {
      throw new \InvalidArgumentException("ZIP файл не найден: {$zipPath}");
    }
    // Создаем директорию для извлечения, если её нет
    if (!is_dir($extractTo)) {
      if (!mkdir($extractTo, 0755, true)) {
          throw new \RuntimeException("Не удалось создать директорию: {$extractTo}");
      }
    }
    
    $zip = new \ZipArchive();
    $extractedFiles = [];
    
    // Открываем ZIP архив
    if ($zip->open($zipPath) !== true) {
        throw new \RuntimeException("Не удалось открыть ZIP архив: {$zipPath}");
    }

    // Получаем количество файлов в архиве    
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $fileName = $zip->getNameIndex($i);
      
      // Пропускаем директории (они создадутся автоматически)
      if (substr($fileName, -1) === '/') {
          continue;
      }
    
      // Сохраняем структуру папок
      $targetPath = $extractTo . DIRECTORY_SEPARATOR . $fileName;
      
      // Создаем директорию для файла, если её нет
      $targetDir = dirname($targetPath);
      if (!is_dir($targetDir)) {
          mkdir($targetDir, 0755, true);
      }
      
      // Извлекаем файл
      if ($zip->extractTo($extractTo, $fileName)) {
          $extractedFiles[] = $targetPath;
      }
    }
    $zip->close();
    return $extractedFiles;
  }
  // public static function zipToFile(string $srcZipFilePath,string $fileName):string{
  //   $f3=\Base::instance();
  //   $z = new \ZipArchive();
  //   $res = $z->open('test_im.zip');
  //   if ($res === TRUE) {
  //       $z->extractTo($f3['BASE'].$f3['user_backup_test_folder'], array('test.json'));
  //       $z->close();
  //       return true;
  //   } 
  //   return false;
  // }
}

?>