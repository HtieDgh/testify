<?php
class XMainController
{
    /**
     * <p>Регистрация новго пользователя</p>
    */
    public function newUser($f3,$params=NULL){
        global $db;
        $return_out=array();
        if(isset($_POST['name'])&&
        isset($_POST['password'])&&
        isset($_POST['login'])
        ){
            if($_POST['password']!=''){
                //подготовка данных перед внесением в бд
                $_POST=CFuns::sanitizeString($_POST);
                $login=$_POST['login'];
                $pass=md5($_POST['password']);
                $name=$_POST['name'];
                // Регистрация пользователя
                $return_out=Security::registUser($db,$login,$pass,$name);
            }else{
                $return_out['err']=TRUE;
                $return_out['err_txt']='Заполниете все поля формы';
            }

        } else {
            //Редкая ситуация когда, из-за ошибки передачи, не все данные дошли до сервера
            $return_out['err']=TRUE;
            $return_out['err_txt']='Параметры не переданы! Свяжитесь с администратором или попробуйте снова';
        }
        echo json_encode($return_out);
    }
    /**
     * <p>Сохранение файлов теста</p>
    */
    public function saveTestFiles($f3,$params=NULL) {
        global $db;

        $return_out['err']=FALSE;
        $return_out['err_txt']='';

        $log_err_txt=Security::loginTest($f3,$db);

        if(preg_match("/[^0-9a-z]/",$params['variant_link']))
        {
            
            $return_out['err']=TRUE;
            $return_out['err_txt']='При создании теста параметры неверно передана ссылка на вариант, повторите снова';
            echo json_encode($return_out);
            exit;
        }

        if($f3->get('user.access')<=1)
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Пользователь не авторизован! Пройдите авторизацию.';
            echo json_encode($return_out);
            exit;
        }
        
        $_POST=CFuns::sanitizeString($_POST);
        $t=new Tests($db);
        $upl=new Uploads($f3->get('user_test_data_path'),$f3->get('user.login'));
  
        //Кеширование ссылки варианта для загрузки
        if(isset($_COOKIE['variant_link']))
        {
            $variant_link=$params['variant_link'];
            //Файл для изменяемого варианта уже был отправлен
            if($params['variant_link'] != $_COOKIE['variant_link'])
            {                                  
                //Возможна ситуация когда Пользователь изменяет другой вариант
                
                Security::updateCookie('variant_link',$params['variant_link'],"/",$_SERVER['HTTP_HOST'],TRUE);
                //Удаление файлов если они существуют
                $upl->DeleteVariant($variant_link);
            }
        }else if( $t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) )
        {
            //Изменение существующего
            $variant_link=$params['variant_link'];
            Security::updateCookie('variant_link',$params['variant_link'],"/",$_SERVER['HTTP_HOST'],TRUE);
            //Удаление файлов если они существуют
            $upl->DeleteVariant($variant_link);
        }else {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Пользователь не авторизован для изменения! Пройдите авторизацию.';
            echo json_encode($return_out);
            exit;
        }
        
        foreach ($_FILES as $v)
        {
            $err=$upl->UploadFile($v,$variant_link);
            if($err!=='')
            {
                $return_out['err']=TRUE;
                $return_out['err_txt'].=$err;
            }
        }
            
        echo json_encode($return_out);
    }
    /**
     * <p>Изменение/Сохранение нового теста</p>
    */
    public function editTest($f3,$params=NULL) {
        global $db;
        $return_out['err']=FALSE;
        $return_out['err_txt']='';

        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<=1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if (!isset($_POST['test_data']) || !isset($_POST['variant_data']))
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].='Ошибка при передаче данных теста и вариантов. Попробуйте снова.';
            echo json_encode($return_out);
            exit;
        }
        $t=new Tests($db);
        if( !$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id'))){
            if($f3->get('user.access')<2){
                $return_out['err']=TRUE;
                $return_out['err_txt'].='Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию или закройте данное окно и попробуйте создать свой тест.';
                echo json_encode($return_out);
                exit;
            }
        }
        
        $_POST=CFuns::sanitizeString($_POST);
        $json_str_test=htmlspecialchars_decode(htmlspecialchars_decode($_POST['test_data']));
        $json_str_var=htmlspecialchars_decode(htmlspecialchars_decode($_POST['variant_data']));

        $test_data=json_decode($json_str_test,TRUE);
        $variant_data=json_decode($json_str_var,TRUE);

        if($test_data===null||$variant_data===null)
        {
            $return_out['err']=TRUE;
            $return_out['err_txt'].='Ошибка парсинга файла JSON: Смените кодировку на UTF8.';
            echo json_encode($return_out);
            exit;
        }
        //Ошибки обработаны, выполенение запроса к БД:

        if(preg_match("/[^0-9]/",$test_data['test_id'])){
            $return_out['err']=TRUE;
            $return_out['err_txt'].='Неверно передан индентификатор. Попробуйте снова.';
            echo json_encode($return_out);
            exit;
        }

        if($test_data['test_id']=='0') {
            //Создание нового теста
            $newTestId=$t->CreateTest($test_data,$f3->get('user.id'));
            $return_out['variant_link']=$t->CreateVariants($newTestId,$variant_data);

        }else{
            //Изменение существующего
            $t->UpdateTest($test_data);
            $return_out['variant_link']=$t->UpdateVariants($variant_data,$test_data['test_id']);
        }
                
        echo json_encode($return_out);
        exit;       
    }
    /**
     * <p>Удаление теста со всеми его вариантами/вопросами/ответами/фалами</p>
    */
    public function deleteTest($f3,$params=NULL) {
        global $db;
        $return_out['err']=TRUE;
        $return_out['err_txt']='Ошибка передачи индентификатора теста, попробуйте снова';

        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<=1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if ( preg_match("/[^0-9]/",$params['test_id']) )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Передан невернный идентификатор теста';
            echo json_encode($return_out);
            exit;
        }
        if($params['test_id']!=='0'){
            $t=new Tests($db);
            $upl=new Uploads($f3->get('user_test_data_path'),$f3->get('user.login'));

            if( !$t->CheckTestAuthor_tid($params['test_id'],$f3->get('user.id')) )
            {
                if($f3->get('user.access')!=3){
                    $return_out['err']=TRUE;
                    $return_out['err_txt']='Пользователь не авторизован для данной операции удаления';
                    echo json_encode($return_out);
                    exit;
                }
            }
            $variants=$t->GetAllTestVariants_tid($params['test_id']);
            foreach ($variants as $v) {
                //Удаление файлов всех вариантов теста
                $upl->DeleteVariant($v['link']);   
            }
             //Удаление из БД
             $t->DeleteTest($params['test_id']);
             $return_out['err']=FALSE;
             $return_out['err_txt']='';
        }
        echo json_encode($return_out);
    }
    /**
     * <p>Удаление варианта теста со всеми его вопросами/ответами/фалами</p>
    */
    public function deleteVariant($f3,$params=NULL) {
        global $db;
        $return_out['err']=FALSE;
        $return_out['err_txt']='';

        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<=1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if ( preg_match("/[^0-9a-z]/",$params['variant_link']) )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Передан невернный идентификатор варианта';
            echo json_encode($return_out);
            exit;
        }
        if($params['variant_link']!=='0'){
            $t=new Tests($db);
            $upl=new Uploads($f3->get('user_test_data_path'),$f3->get('user.login'));

            if( !$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id')) )
            {
                if($f3->get('user.access')!=3){
                    $return_out['err']=TRUE;
                    $return_out['err_txt']='Пользователь не авторизован для данной операции удаления';
                    echo json_encode($return_out);
                    exit;
                }
            }

            //Удаление файлов варианта теста
             $upl->DeleteVariant($params['variant_link']);   
            
            //Удаление из БД
             $t->DeleteVariant($params['variant_link']);
        }
        echo json_encode($return_out);
    }
    /**
     * <p>Сохранение изменений вопросов</p>
    */
    public function editQuestions($f3,$params=null){
        global $db;
        $return_out['err']=FALSE;
        $return_out['err_txt']='';

        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<=1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if(preg_match("/[^0-9a-z]/",$params['variant_link']))
        {
            
            $return_out['err']=TRUE;
            $return_out['err_txt']='При создании теста параметры неверно передана ссылка на вариант, повторите снова';
            echo json_encode($return_out);
            exit;
        }

        if (!isset($_POST['question_data']))
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Ошибка при передаче данных вопросов. Попробуйте снова.';
            echo json_encode($return_out);
            exit;
        }
        $t=new Tests($db);
        if(!$t->CheckTestAuthor_link($params['variant_link'],$f3->get('user.id'))){
            if($f3->get('user.access')!=3){
                $return_out['err']=TRUE;
                $return_out['err_txt']='Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию.';
                echo json_encode($return_out);
                exit;
            }
        }
        $_POST=CFuns::sanitizeString($_POST);
        $json_str=htmlspecialchars_decode(htmlspecialchars_decode($_POST['question_data']));
        $q_data=json_decode($json_str,TRUE);

        if($q_data===null)
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Ошибка парсинга файла JSON: Исключите в вопросах и ответах спец символы, такие как двойные ковычки, знак доллара и т.п. Попробуйте снова.';
            echo json_encode($return_out);
            exit;
        }
        //Ошибки обработаны, выполенение запросов к БД:
            $t=new Tests($db);
        //Сохранение изменений вопросов у варианта
            $t->saveQuestions($q_data,$params['variant_link']);
            $return_out['variant_link']=$params['variant_link'];

        //Сохранение резервной копии теста
                                    
        $upl=new Uploads($f3->get('user_test_data_path'),$f3->get('user.login'));
        //получение данных теста
            $test_struct=$t->GetUserTest($params['variant_link']);
            if( count($test_struct)==0 ){
                $return_out['err']=TRUE;
                $return_out['err_txt']='Ошибка: Данные теста не найдены, повторите операцию';
                echo json_encode($return_out);
                exit;
            }
        
        if( $upl->UploadJSONTestData(json_encode([
            'test'=>$test_struct,
            'variant'=>$t->GetVariant($params['variant_link']),
            'qsts'=>$q_data
        ]), $params['variant_link']) !== FALSE )
        {
        
        //Ссылка на архив теста для загрузки на стороне пользователя
            $backup=$upl->GetBackupPath($params['variant_link']);

        //Генерация архива и ссылки на него
            HZip::zipDir($backup['folder'],$backup['link']);
            $return_out['test_file_link']=$f3->get('SITE_DOMAIN').$backup['link'];
        //генерация ссылки на готовый вариант теста
            $return_out['link'] = $f3->get('SITE_DOMAIN').'test/'.$params['variant_link'];
        }else{
            $return_out['err']=TRUE;
            $return_out['err_txt'].=' Ошибка при сохранении JSON данных теста, проверьте кодировку или попробуйте снова';
        }
        echo json_encode($return_out);
    }

    public function getVariantsLinks($f3,$params=null) {
        global $db;
        $return_out['err']=FALSE;
        $return_out['err_txt']='';
        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<=1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if ( preg_match("/[^0-9]/",$params['test_id']) )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Передан невернный идентификатор теста';
            echo json_encode($return_out);
            exit;
        }
        $t=new Tests($db);
        if(!$t->CheckTestAuthor_tid($params['test_id'],$f3->get('user.id'))){
            if($f3->get('user.access')!=3){
                $return_out['err']=TRUE;
                $return_out['err_txt']='Попытка изменить тест, которого не существует, или тест другого пользователя. Повторите операцию.';
                echo json_encode($return_out);
                exit;
            }
        }
        $return_out['variants']=$t->GetAllTestVariants_tid($params['test_id']);
        $return_out['absolute']=$f3->get('SITE_DOMAIN');

        echo json_encode($return_out);
    }

    /**
     * <p>Регистарция попытки пройти тест</p>
    */
    public function newResult($f3,$params=null) {
        global $db;
        $return_out['err']=FALSE;
        $return_out['err_txt']='';
        $log_err_txt=Security::loginTest($f3,$db);
        if( $f3->get('user.access')<1 )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']=$log_err_txt;
            echo json_encode($return_out);
            exit;
        }
        if ( preg_match("/[^0-9a-z]/",$params['variant_link']) )
        {
            $return_out['err']=TRUE;
            $return_out['err_txt']='Передан невернный идентификатор варианта';
            echo json_encode($return_out);
            exit;
        }
        if (!isset($_POST['answ_data'])){
            $return_out['err']=TRUE;
            $return_out['err_txt']='Данные теста не преданы';
            echo json_encode($return_out);
            exit;
        }
        //Добавление новой попытки в БД
         $t=new Tests($db);
         $t->AddResult($params['variant_link'],$f3->get('user.id'),$_POST['answ_data']);
         $return_out['result_link']=$f3->get('SITE_DOMAIN').'check/'.$params['variant_link'].'/';

        echo json_encode($return_out);
    }
}

?>