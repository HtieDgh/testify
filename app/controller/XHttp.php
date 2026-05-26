<?php namespace controller;

use \model\CFuns;
use \model\Security as S;
use \model\Uploads;
use \model\NoteProcessor;
use \model\CourseProcessor;
use \model\Subscriber;
use \model\Comment;
use \model\Test;
use \model\HZip;

class XHttp
{    
    /**
     * Сформировать и получить объект подключения к базе данных
     */
    private function getDB(&$f3)
    {
        return new \DB\SQL
        (
            $f3->get('DB_TESTIFY.db_type').':host='.$f3->get('DB_TESTIFY.db_host').';port='.$f3->get('DB_TESTIFY.db_port').';dbname='.$f3->get('DB_TESTIFY.db_name'),
            $f3->get('DB_TESTIFY.db_login'),
            $f3->get('DB_TESTIFY.db_password')
        );
    }
    /**
     * Регистрация новго пользователя
    */
    public function newUser($f3,$params=NULL){
        $db = $this->getDB($f3);
        $returnOut=array();
        if(isset($_POST['name'])&&
        isset($_POST['password'])&&
        isset($_POST['login'])
        ){
            if($_POST['password']!=''){
                //подготовка данных перед внесением в бд
                $_POST=CFuns::sanitizeString($_POST);
                $login=$_POST['login'];
                $pass=hash('sha256',$_POST['password']);
                $name=$_POST['name'];
                // Регистрация пользователя
                $returnOut=S::registUser($db,$login,$pass,$name);
            }else{
                $returnOut['err']=TRUE;
                $returnOut['err_txt']='Заполниете все поля формы';
            }

        } else {
            //Редкая ситуация когда, из-за ошибки передачи, не все данные дошли до сервера
            $returnOut['err']=TRUE;
            $returnOut['err_txt']='Параметры не переданы! Свяжитесь с администратором или попробуйте снова';
        }
        echo json_encode($returnOut);
    }
    /**
     * Сохранение файлов теста
    */
    public function saveTestFiles($f3,$params=NULL) {
        $db = $this->getDB($f3);
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $log_err_txt=S::loginTest($db);
        try {
            if(preg_match("/[^0-9a-z]/",$params['variant_link']))
            {
                throw new \Exception("При создании теста параметры неверно передана ссылка на вариант, повторите снова", 403);
            }

            if(!$f3->get('user.isAuthor') && !$f3->get('user.isAdmin'))
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            $f3->set('POST',CFuns::sanitizeString($_POST));
            $t=new Test($db);
            $upl=new Uploads($f3->get('user_data_path'),$f3->get('user.login'));
    
            //Кеширование ссылки варианта для загрузки
            if(isset($_COOKIE['variant_link']))
            {
                $variant_link=$params['variant_link'];
                //Файл для изменяемого варианта уже был отправлен
                if($params['variant_link'] != $_COOKIE['variant_link'])
                {                                  
                    //Возможна ситуация когда Пользователь изменяет другой вариант
                    
                    S::updateCookie('variant_link',$params['variant_link'],"/",$_SERVER['HTTP_HOST']);
                    //Удаление файлов если они существуют
                    $upl->deleteVariant($variant_link);
                }
            }else if( $t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) || $f3->get('user.isAdmin'))
            {
                //Изменение существующего
                $variant_link=$params['variant_link'];
                S::updateCookie('variant_link',$params['variant_link'],"/",$_SERVER['HTTP_HOST']);
                //Удаление файлов если они существуют
                $upl->deleteVariant($variant_link);
            }else{
                throw new \Exception("Пользователь не авторизован", 403);
            }
            $returnOut['err']=FALSE;

            //загрузка файлов
            if( 
                !Uploads::isFileValid( $_FILES['file'], Uploads::$ext['IMG']) &&
                !Uploads::isFileValid( $_FILES['file'], Uploads::$ext['VIDEO']) &&
                !Uploads::isFileValid( $_FILES['file'], Uploads::$ext['AUDIO'])
            )
            {
                $returnOut['err']=TRUE;
                $returnOut['err_txt'].='Небыл загружен файл, проверьте формат файла для: '.$_FILES['file']["name"];
            }else{
                $upl->uploadFile($_FILES['file'],$upl->test_dir.$variant_link);
            }
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Изменение/Сохранение нового теста
    */
    public function editTest($f3,$params=NULL) {
        $db = $this->getDB($f3);
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $log_err_txt=S::loginTest($db);
        try {
            if( !$f3->get('user.isAuthor') && !$f3->get('user.isAdmin') )
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            $f3->set('POST',CFuns::sanitizeString($_POST));
            if ($f3->get('POST.test_data')===null || $f3->get('POST.variant_data')===null)
            {
                throw new \Exception("Ошибка при передаче данных теста и вариантов. Попробуйте снова.", 403);
            }
            if ( preg_match("/[^0-9a-z]/",$params['variant_link']) )
            {
                throw new \Exception("Передан невернный идентификатор варианта", 403);
            }
            $t=new Test($db);
            
            if(
                !$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) &&
                !$f3->get('user.isAuthor')
            ){
                throw new \Exception("Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию или закройте данное окно и попробуйте создать свой тест.", 403);
            }
            
            $json_str_test=htmlspecialchars_decode(htmlspecialchars_decode($f3->get('POST.test_data')));
            $json_str_var=htmlspecialchars_decode(htmlspecialchars_decode($f3->get('POST.variant_data')));
    
            $test_data=json_decode($json_str_test,TRUE);
            $variant_data=json_decode($json_str_var,TRUE);
    
            if($test_data===null || $variant_data===null)
            {
                throw new \Exception("Ошибка парсинга файла JSON: Смените кодировку на UTF8.");
            }
            
            if(preg_match("/[^0-9]/",$test_data['test_id'])){
                throw new \Exception("Неверно передан индентификатор. Попробуйте снова.");
            }
            //Ошибки обработаны, выполенение запроса к БД:
            if($test_data['test_id']=='0') {
                //Создание нового теста
                $newTestId=$t->CreateTest($test_data,$f3->get('user.id'));
                $returnOut['variant_link']=$t->CreateVariants($newTestId,$variant_data);
    
            }else{
                //Изменение существующего
                $t->UpdateTest($test_data);
                $returnOut['variant_link']=$t->UpdateVariants($variant_data,$test_data['test_id']);
            }
            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);    
    }
    /**
     * Удаление теста со всеми его вариантами/вопросами/ответами/фалами
    */
    public function deleteTest($f3,$params=NULL) {
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $returnOut['err']=TRUE;
        $log_err_txt=S::loginTest($db);
        try {
            if( !$f3->get('user.isAuthor') && !$f3->get('user.isAdmin') )
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if (
                preg_match("/[^0-9]/",$params['test_id']) ||
                $params['test_id']==='0'
            )
            {
                throw new \Exception("Передан невернный идентификатор теста", 403);

            }
            $t=new Test($db);
            $upl=new Uploads($f3->get('user_data_path'),$f3->get('user.login'));
            if( 
                !$t->CheckTestAuthor_tid($params['test_id'],$f3->get('user.id')) &&!$f3->get('user.isAdmin')
            ){
                throw new \Exception("Пользователь не авторизован для данной операции удаления", 403);                
            }
            $variants=$t->GetAllTestVariants_tid($params['test_id']);
            foreach ($variants as $v) {
                //Удаление файлов всех вариантов теста
                $upl->deleteVariant($v['unique_url']);   
            }
            //Удаление из БД
            $t->deleteTest($params['test_id']);
            $returnOut['err']=FALSE;
            
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Удаление варианта теста со всеми его вопросами/ответами/фалами
    */
    public function deleteVariant($f3,$params=NULL) {
        
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $log_err_txt=S::loginTest($db);
        try {
           
            if( !$f3->get('user.isAuthor') && !$f3->get('user.isAdmin') )
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if ( preg_match("/[^0-9a-z]/",$params['variant_link']) )
            {
                throw new \Exception("Передан невернный идентификатор варианта", 403);
            }
            if($params['variant_link']!=='0'){
                $t=new Test($db);
                $upl=new Uploads($f3->get('user_data_path'),$f3->get('user.login'));

                if( !$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) && !$f3->get('user.isAdmin'))
                {
                    throw new \Exception("Пользователь не авторизован", 403);
                }

                //Удаление файлов варианта теста
                $upl->deleteVariant($params['variant_link']);   
                
                //Удаление из БД
                $t->deleteVariant($params['variant_link']);
            }
            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Сохранение изменений вопросов
    */
    public function editQuestions($f3,$params=null){
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $log_err_txt=S::loginTest($db);
        try {
            if( !$f3->get('user.isAuthor') &&  !$f3->get('user.isAdmin'))
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if ( preg_match("/[^0-9a-z]/",$params['variant_link']) )
            {
                throw new \Exception("Передан невернный идентификатор варианта", 403);
            }

            if ($f3->get('POST.question_data')===null)
            {
                throw new \Exception("Ошибка при передаче данных вопросов. Попробуйте снова.", 403);
            }
            $t=new Test($db);
            if(
                !$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) && !$f3->get('user.isAdmin')
            ){
                throw new \Exception("Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию.", 403);                
            }
            $f3->set('POST',CFuns::sanitizeString($_POST));
            $json_str=htmlspecialchars_decode(htmlspecialchars_decode($f3->get('POST.question_data')));
            $q_data=json_decode($json_str,TRUE);

            if($q_data===null)
            {
                throw new \Exception("Ошибка парсинга файла JSON: Исключите в вопросах и ответах спец символы, такие как двойные ковычки, знак доллара и т.п. Попробуйте снова.", 403);   
            }
            //Ошибки обработаны, выполенение запросов к БД:
            //Сохранение изменений вопросов у варианта
                $upl=new Uploads($f3->get('user_data_path'),$f3->get('user.login'));
                $t->saveQuestions($q_data,$params['variant_link']);
                $returnOut['variant_link']=$params['variant_link'];

            //Сохранение резервной копии теста

            //получение данных теста
                $test_struct=$t->GetUserTest($params['variant_link']);
                if( empty($test_struct) ){
                    throw new \Exception("Ошибка: Данные теста не найдены, повторите операцию", 403);   
                }
            
            if( $upl->UploadJSONTestData(json_encode([
                'test'=>$test_struct,
                'variant'=>$t->GetVariant($params['variant_link'])[0],
                'qsts'=>$q_data
            ]), $params['variant_link']) === FALSE )
            {
                throw new \Exception("Ошибка при сохранении JSON данных теста, проверьте кодировку или попробуйте снова", 403); 
            }

        //Ссылка на архив теста для загрузки на стороне пользователя
            $backup=$upl->GetBackupPath($params['variant_link']);

        //Генерация архива и ссылки на него
            array_map('unlink', glob($backup['folder'].'*.zip'));//удалить все существующие zip в папке
            HZip::zipDir($backup['folder'],$backup['link']);
            $returnOut['test_file_link']=$f3->get('SITE_DOMAIN').'/'.$backup['link'];
        //генерация ссылки на готовый вариант теста
            $returnOut['link'] = $f3->get('SITE_DOMAIN').'/test/'.$params['variant_link'];
            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Получение списка ссылко на варианты для теста
     */
    public function getVariantsLinks($f3,$params=null) {
        $db = $this->getDB($f3);
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $log_err_txt=S::loginTest($db);
        try {
            if( !$f3->get('user.isAuthor') &&  !$f3->get('user.isAdmin'))
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if ( preg_match("/[^0-9]/",$params['test_id']) )
            {
                throw new \Exception("Передан невернный идентификатор теста", 403);
            }
            $t=new Test($db);
            if(
                !$t->CheckTestAuthor_tid($params['test_id'],$f3->get('user.id')) && !$f3->get('user.isAdmin')
            ){
                throw new \Exception("Пользователь не авторизован", 403);
            }

            $returnOut['variants']=$t->GetAllTestVariants_tid($params['test_id']);
            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Регистарция попытки пройти тест
    */
    public function newResult($f3,$params=null) {
        $db = $this->getDB($f3);
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $log_err_txt=S::loginTest($db);
        try {
            if( !$f3->get('user.isAuth') )
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if ( preg_match("/[^0-9a-z]/",$params['variant_link']) )
            {
                throw new \Exception("Передан невернный идентификатор варианта", 403);
            }
            if (!isset($_POST['answ_data'])){
                throw new \Exception("Данные теста не преданы", 403);
            }
            $f3->set('POST',CFuns::sanitizeString($_POST));
            //Добавление новой попытки в БД
            $t=new Test($db);
            $t->AddResult($params['variant_link'],$f3->get('user.id'),$f3->get('POST.answ_data'));
            $returnOut['result_link']=$f3->get('SITE_DOMAIN').'/check/'.$params['variant_link'].'/';
            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Возвращает список коментариев к определенной записи
     */
    public function getComments(\Base $f3,$params=null) 
    {

        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        S::loginTest($db);  
        try{
            if(!isset($params['note_id']) || preg_match("/[^0-9]/",$params['note_id']))
            {
                throw new \Exception("Ошибка передачи данных, попробуйте снова позже", 403);
            }
            $noteId=CFuns::sanitizeString($params)['note_id'];
            $returnOut['comments']=Comment::showCurComments($db,$noteId);
            //доступ к элементам управления по умолчанию есть у Админа или автора заметки
            $returnOut['access'] = $f3->get('user.isAdmin') || NoteProcessor::get($db,$noteId)['author_id']==$f3->get('user.id');
            $returnOut['err']=FALSE;
            $returnOut['err_txt']="";
        } catch (\Throwable $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Добавление нового комментария к записи
     */
    public function newComment(\Base $f3,$params=null) 
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        S::loginTest($db); 
        try{
            if(
                !isset($params['note_id']) ||
                preg_match("/[^0-9]/",$params['note_id']) ||
                $f3->get('POST.authorId')===null ||
                preg_match("/[^0-9]/",$f3->get('POST.authorId')) ||
                $f3->get('POST.text')===null 
            )
            {
                throw new \Exception('Ошибка передачи данных, попробуйте снова позже', 403);
            }
            $noteId=CFuns::sanitizeString($params)['note_id'];
            $text=CFuns::sanitizeString($f3->get('POST'))['text'];
            if(Comment::newComment($db,$noteId,$f3->get('POST.authorId'),$text)==0)
            {
                throw new \Exception('Ошибка при сохранении комментария, попробуйте снова позже', 500);
            }
            $returnOut['comments']=Comment::showCurComments($db,$noteId);
            //доступ к элементам управления по умолчанию есть у Админа или автора заметки
            $returnOut['access'] = $f3->get('user.isAdmin') || NoteProcessor::get($db,$noteId)['author_id']==$f3->get('user.id');
            $returnOut['err']=FALSE;
        } catch (\Throwable $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Удаление комментария под записью
     */
    public function deleteComment(\Base $f3,$params=null) 
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        S::loginTest($db); 
        try{
            if(!$f3->get('user.isAuth'))
            {
                throw new \Exception('Ошибка: пользователь не авторизован', 403);
            }
            if(
                !isset($params['comment_id']) ||
                preg_match("/[^0-9]/",$params['comment_id'])
            )
            {
                throw new \Exception('Ошибка передачи данных, попробуйте снова позже', 403);
            }
            $params=CFuns::sanitizeString($params);
            $commentId=$params['comment_id'];
            $res=Comment::getNoteAuthorId($db,$commentId);
            //Пользователь не является автором записи и
            // не является автором комментария и
            // не является админом
            if(
                $res['note_author_id'] != $f3->get('user.id') &&
                $res['comment_author_id']!=$f3->get('user.id') &&
                !$f3->get('user.isAdmin')
            ){
                //то у него нет прав на удаление комментария
                throw new \Exception('Ошибка: не достаточно прав', 403);
            }
            //удалить комментарий
            if(Comment::delete($db,$commentId)!=1)
            {
                throw new \Exception('Ошибка при удалении попробуйте позже', 500);
            }
            $returnOut['err']=FALSE;
        } catch (\Throwable $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Сохранение аватара пользоватлея
     */
    public function saveAva(\Base $f3)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        try {
            //проверка авторизации пользователя
            $db = $this->getDB($f3);
            $loginErr=S::loginTest($db);
            if($loginErr){ throw new \Exception($loginErr, 403); }

            $upl=new Uploads(
                users_dir:$f3->get('user_data_path'),
                login:$f3->get('user.login')
            );
            //проверить новую аватарку на валидность (доступны только png)
            if(!Uploads::isFileValid( $f3->get('FILES.user_ava'), Uploads::$ext['AVA']))
		    {
                throw new \Exception('Небыл загружен файл, проверьте формат файла для: '.$f3->get('FILES.user_ava.name'), 403);
            }
            //удалить пердыдущую аватарку если она была (кроме default_ava)
            if($f3->get('user.ava_url')!='user_avas/default_ava.png'){
                unlink($upl->static_img_dir.$f3->get('user.ava_url'));
            } 

            //Обновить базу данных и загрузить аватарку
            $ava_name='u_id_'.$f3->get('user.id').'_'.date("YmdHis").Uploads::$ext['AVA'][$f3->get('FILES.user_ava.type')];

            //загрузить новую
            $f3->set('FILES.user_ava.name',$ava_name);
    
            $upl->uploadFile(
                file_data:$f3->get('FILES.user_ava'),
                dir:$upl->static_img_dir.$upl->ava_dir
            );

            $returnOut['new_ava_url']=$upl->ava_dir.$ava_name;
            //обновить БД
            if(!S::changeUserAva(
                db:$db,
                userId:$f3->get('user.id'),
                ava_url:$returnOut['new_ava_url']
            )){
                throw new \Exception("Невозможно сохранить файл, попробуйте позже", 403);
            };
            $returnOut['err']=FALSE;

        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }

        echo json_encode($returnOut);
    }
    /**
     * Удаление записи
     */
    public function deleteNote(\Base $f3,$params)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        $nt=new NoteProcessor();
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(preg_match("/[^0-9]/",$params['id']))
            {
                throw new \Exception("Ошибка передачи данных, попробуйте снова позже", 403);
            }
            if(
                $nt->get($db,$params['id'])['author_id'] !== $f3->get('user.id') &&
                !$f3->get('user.isAdmin')
            )//пользователь не автор удаляемой записи и не админ
            {
                throw new \Exception("Пользователь не имеет прав на выполнение операции", 403);
            }
            if($nt->delete($db,$params['id'])==1){
                $returnOut['err']=FALSE;
            }else{
                throw new \Exception("Выполнить операцию не удалось, попробуйте позже", 403);
            }

        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        
        echo json_encode($returnOut);
    }    
    /**
     * Удаление файла
     */
    public function deleteFile(\Base $f3,$params)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }

            $fileUrls=json_decode($f3->get('BODY'));
            if(count($fileUrls)==0){ throw new \Exception("Не передан список файлов для удаления", 403);}
            
            $upl=new Uploads(
                users_dir:$f3->get('user_data_path'),
                login:$f3->get('user.login')
            );
            //преобразование url в path
            $filePaths=[];
            foreach ($fileUrls as $v) {
                if($upl->isNotUserFile($v) && !$f3->get('user.isAdmin')){
                    throw new \Exception("Не достаточно прав", 403);
                }
                //удаление @BASE из url файла, чтобы конвертировать в path
                $filePaths[]=str_replace($f3->get('BASE').'/','',$v);
            }
            //удаление файлов
            $upl->deleteFiles($filePaths);
            $returnOut['err']=FALSE;
        } catch (\Throwable $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Получение списка разрешенных к загрузке MIME типов для файлов
     */
    public function getFilesExtList(\Base $f3,$params)
    {
        $returnOut['err']=FALSE;
        $returnOut['err_txt']='';
        $returnOut['ext']=Uploads::getAcceptedMIMEList();
        echo json_encode($returnOut);
    }
    /**
     * Получение информации о файлах конкретного пользователя
     */
    public function getAllFiles(\Base $f3,$params)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(!isset($params['user_id'])||!isset($params['file_type'])){
                throw new \Exception("Параметры не переданы", 403);
            }
            if(
                preg_match("/[^(img|file|video)]/",$params['file_type']) || 
                preg_match("/[^0-9]/",$params['user_id']) ||
                (int)$params['user_id']!=$f3->get('user.id') && !$f3->get('user.isAdmin')
            )
            {
                throw new \Exception("Параметры не валидны", 403);
            }
            //получить файлы выбранного пользователя
            $login=S::getUserLogin($db,$params['user_id']);
            $upl=new Uploads(
                login:$login,
                users_dir:$f3->get('user_data_path')
            );
            switch ($params['file_type']) {
                case 'img':
                    $returnOut['files']=$upl->getUserFiles($upl->img_dir,0,PHP_INT_MAX );
                    break;
                case 'video':
                    $returnOut['files']=$upl->getUserFiles($upl->video_dir,0,PHP_INT_MAX );
                    break;
                case 'file':
                    $returnOut['files']=$upl->getUserFiles($upl->file_dir,0,PHP_INT_MAX );
            }
            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Подписаться на автора
     */
    public function subscribeToAuthor(\Base $f3,$params)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(
                preg_match("/[^0-9]/",$params['id']) || 
                preg_match("/[^0-9]/",$params['subscriber_id']) || 
                $params['subscriber_id']===$params['id'] ||
                empty(Subscriber::getAuthorId($db,$params['id']))
            ){
                throw new \Exception("Параметры не валидны", 403);
            }
            Subscriber::subscribeToAuthor($db,$params['subscriber_id'],$params['id']);
            $returnOut['err']=FALSE;
        
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Отписаться от автора
     */
    public function unsubscribeToAuthor(\Base $f3,$params)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(
                preg_match("/[^0-9]/",$params['id']) || 
                preg_match("/[^0-9]/",$params['subscriber_id']) || 
                $params['subscriber_id']===$params['id'])
            {
                throw new \Exception("Параметры не валидны", 403);
            }
            Subscriber::unsubscribeToAuthor($db,$params['subscriber_id'],$params['id']);
            $returnOut['err']=FALSE;
        
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Удалить курс
     */
    public function deleteCourse(\Base $f3,$params)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(preg_match("/[^0-9]/",$params['id']))
            {
                throw new \Exception("Ошибка передачи данных, попробуйте снова позже", 403);
            }
            if(
                CourseProcessor::get($db,$params['id'])['author_id'] !== $f3->get('user.id') &&
                !$f3->get('user.isAdmin')
            )//пользователь не автор удаляемой записи и не админ
            {
                throw new \Exception("Пользователь не имеет прав на выполнение операции", 403);
            }
            if(CourseProcessor::delete($db,$params['id'])==1){
                $returnOut['err']=FALSE;
            }else{
                throw new \Exception("Выполнить операцию не удалось, попробуйте позже", 403);
            }

        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
    /**
     * Подписаться на курс
     */
    public function subscribeToCourse(\Base $f3,$params)
    {
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(
                preg_match("/[^0-9]/",$params['course_id']) || 
                preg_match("/[^0-9]/",$params['subscriber_id'])||
                empty(CourseProcessor::get($db,$params['course_id']))
            ){
                throw new \Exception("Параметры не валидны", 403);
            }
            
            if(Subscriber::checkIsConfirmedCourseSubscriber($db,$params['subscriber_id'],$params['course_id'])){
                throw new \Exception("Заявка была уже подана ранее", 403);
            }
            if(!$f3->get('user.isAdmin') && $params['subscriber_id']!=$f3->get('user.id')){
                throw new \Exception("Подписаться можно только за себя", 403);
            }

            // Если
            //  is_private = 1  =>  is_confirmed = 0, 
            // иначе
            //  is_private = 0  =>  is_confirmed = 1, 
            $confirmed=abs(CourseProcessor::get($db,$params['course_id'])['is_private']-1); 
            
            //подписка на курс
            Subscriber::subscribeToCourse($db,$params['subscriber_id'],$params['course_id'],$confirmed);
            //подписка на курс автоматически дает подписку на автора
            $authorId=Subscriber::getCourseAuthorByCourseId($db,$params['course_id'])[0]['id'];
            if( !Subscriber::checkIsConfirmedSubscriber($db,$params['subscriber_id'],$authorId) )
            {
                Subscriber::subscribeToAuthor(
                    $db,
                    $params['subscriber_id'],
                    $authorId
                );
            }

            $returnOut['err']=FALSE;
            $returnOut['data']=$confirmed==1?'Отписаться':'Заявка подана';
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Отписаться от курса
     */
    public function unsubscribeToCourse(\Base $f3,$params)
    {        
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuth'))//если пользователь не авторизован
            {
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(
                preg_match("/[^0-9]/",$params['course_id']) || 
                preg_match("/[^0-9]/",$params['subscriber_id'])||
                empty(CourseProcessor::get($db,$params['course_id']))
            )
            {
                throw new \Exception("Параметры не валидны", 403);
            }
            if(Subscriber::checkIsConfirmedCourseSubscriber($db,$params['subscriber_id'],$params['course_id'])===-1){
                throw new \Exception("Пользователь еще не подписан на крус", 403);
            }
            //отмена заявки на курс
            Subscriber::unsubscribeToCourse($db,$params['subscriber_id'],$params['course_id']);

            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }   
    /**
     * Подтвердить заявку на курс
     */
    public function confirmRequest(\Base $f3,$params)
    {   
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuthor'))
            {//если пользователь не авторизован
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(
                preg_match("/[^0-9]/",$params['course_id']) || 
                preg_match("/[^0-9]/",$params['subscriber_id'])
            ){
                throw new \Exception("Параметры не валидны", 403);
            }
            $course=CourseProcessor::get($db,$params['course_id']);
            if(empty($course)){
                throw new \Exception("Параметры не валидны", 403);
            }
            if(
                !$f3->get('user.isAdmin') && 
                $f3->get('user.id') != $course['author_id']
            ){
                throw new \Exception("Пользователь не авторизован", 403);
            }
            Subscriber::confimRqst($db,$params['subscriber_id'],$params['course_id']);
            $returnOut['err']=FALSE;

        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Отклонить завявку на курс
     */
    public function cancelRequest(\Base $f3,$params)
    {   
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if(!$f3->get('user.isAuthor'))
            {//если пользователь не авторизован
                throw new \Exception("Пользователь не авторизован", 403);
            }
            if(
                preg_match("/[^0-9]/",$params['course_id']) || 
                preg_match("/[^0-9]/",$params['subscriber_id'])
            ){
                throw new \Exception("Параметры не валидны", 403);
            }
            $course=CourseProcessor::get($db,$params['course_id']);
            if(empty($course)){
                throw new \Exception("Параметры не валидны", 403);
            }
            if(
                !$f3->get('user.isAdmin') && 
                $f3->get('user.id') != $course['author_id']
            ){
                throw new \Exception("Пользователь не авторизован", 403);
            }
            Subscriber::confimRqst($db,$params['subscriber_id'],$params['course_id']);
            $returnOut['err']=FALSE;

        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }    
    /**
     * Удалить пользователя
     */
    public function deleteUser(\Base $f3,$params)
    {   
        $returnOut['err']=TRUE;
        $returnOut['err_txt']='';
        $db = $this->getDB($f3);
        $loginErr=S::loginTest($db);
        try{
            if( preg_match("/[^0-9]/",$params['id'])){
                throw new \Exception("Параметры не валидны", 403);
            }
            if(
                !$f3->get('user.isAdmin')
            ){
                throw new \Exception("Пользователь не авторизован", 403);
            }
            S::delete($db,$params['id']);
            $returnOut['err']=FALSE;
        } catch (\Exception $e) {
            $returnOut['err']=TRUE;
            $returnOut['err_txt']=$e->getMessage();
        }
        echo json_encode($returnOut);
    }
}
?>