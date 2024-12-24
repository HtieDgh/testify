<?php
class Tests{
    public static $db;
    public function __construct(&$db) {
        static::$db=$db;
    }
     /**
     * <p> Принимает test_data и добавляет тест в бд</p>
     * @param array td - данные о тесте 
     * @param array vd - данные о вариантах этого теста 
     * @param array user_id - автор теста
     * @return int id созданого теста
     */ 
    public function CreateTest($td,$user_id) {
        $q="SELECT MAX(id) as \"max_id\" FROM test";
        $cur_id=intval(static::$db->exec($q)[0]['max_id']);
        $cur_id++;
        $q="INSERT INTO test (
            id,
            s_a_id,
            title,
            \"description\",
            \"limit\",
            \"start\",
            \"end\"
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $rv=static::$db->exec($q,
        [
            $cur_id,
            $user_id,
            $td['title'],
            $td['description'],
            $td['limit'],
            $td['test_start'],
            $td['test_end']
        ]);

        return $cur_id;
    }
    //Возварщает variant_link активного теста для последующего заполнения вопросами
    public function CreateVariants($test_id,$vd){
        $q="SELECT MAX(id) as \"max_id\" FROM variant";
        $cur_id=intval(static::$db->exec($q)[0]['max_id']);
        $cur_id++;

        $q="INSERT INTO variant (
            id,
            test_id,
            title,
            link
        ) VALUES (?,?,?,?)";
        $out='';
        foreach ($vd as $v) 
        {
            $variant_link=md5($test_id.$v['title']);
            if($v['is_active']){
                $out=$variant_link;
            }
            static::$db->exec(
                $q,
                [
                    $cur_id,
                    $test_id,
                    $v['title'],
                    $variant_link
                ]
            );
            $cur_id++;
        }
        return $out;
    }
    public function UpdateVariants($vd,$test_id) {
        $out='';
        $q="SELECT MAX(id) as \"max_id\" FROM variant";
        $cur_id=intval(static::$db->exec($q)[0]['max_id']);
        $cur_id++;

        foreach ($vd as $v) {
            if($v['is_active']){
                $out = $v['link'] == '0'?md5($test_id.$v['title']):$v['link'];
            }
            if($v['link']=='0'){
                //Был добавлен новый вариант в старый тест
                static::$db->exec("INSERT INTO variant (
                    id,
                    test_id,
                    title,
                    link
                ) VALUES (?,?,?,?)",
                [
                    $cur_id++,
                    $test_id,
                    $v['title'],
                    md5($test_id.$v['title'])
                ]);
            }else{
                //TODO БД может и не обновить строки
                static::$db->exec("UPDATE variant
                SET title=?
                WHERE link=?",
                [
                    $v['title'],
                    $v['link']
                ]);
            }
        }
        return $out;
    }
    /**
     * <p> Принимает test_data и обновляет тест в бд</p>
     * @param array td - данные о тесте 
     * @return bool результат $db->exec()
     */ 
    public function UpdateTest($td) {

        $q="UPDATE test 
        SET title=?,
         \"description\"=?,
         \"limit\"=?, 
         \"start\"=?,
         \"end\"=?
        WHERE id=?";
        return static::$db->exec($q,
        [
            $td['title'],
            $td['description'],
            $td['limit'],
            $td['test_start'],
            $td['test_end'],
            $td['test_id']
        ]);
    }

    public function getQuestionData($variant_link){

        $q="SELECT q.* FROM question q
        INNER JOIN variant_question v_q ON v_q.question_id=q.id
        INNER JOIN variant v ON v.id=v_q.variant_id
        WHERE v.link='$variant_link'";
        $quest_data['questions']=static::$db->exec($q);
        $quest_data['variant']=$this->GetTestVariant($variant_link);
        
        foreach ($quest_data['questions'] as $v) 
        {
            $quest_data['answers'][$v['id']]=static::$db->exec("SELECT * FROM answer a WHERE a.question_id=".$v['id']);
            $quest_data['files'][$v['id']]=static::$db->exec("SELECT qf.* FROM question_file qf  WHERE qf.q_id=".$v['id']);
        }
        return $quest_data;
    }
    /**
     * <p> Принимает test_data и добавляет тест в бд</p>
     * @param array td - данные о тесте вопросах и вариантах ответов
     * @param array user_id
     * @param array user_name
     * @return array ассоциативный массив с id тестом и уникальным индентификатор-ссылкой для прохождения теста
    */
    /* public function CreateTest($td,$user_id,$user_name)
    {
        
    //Получение различных ids для создоваемого теста
   
    $q="SELECT MAX(id) as 'max_id' FROM test";
    $cur_test_id=intval(static::$db->exec($q)[0]['max_id']);

    $q="SELECT MAX(id) as 'max_id' FROM question";
    $cur_qst_id=intval(static::$db->exec($q)[0]['max_id']);

    $q="SELECT MAX(id) as 'max_id' FROM answer";
    $cur_answ_id=intval(static::$db->exec($q)[0]['max_id']);
        

        $test_link=md5($td['title'].$user_name);

        $cur_test_id++;
        $cur_qst_id++;
        $cur_answ_id++;
        $q_t="INSERT INTO test (
            id,
            s_a_id,
            title,
            description,
            limit,
            start,
            end,
            link
        ) VALUES (?, ?, ?, ?, ?, ?, ?,?)";
        $q_q="INSERT INTO question (
            id,
            test_id,
            title,
            text,
            is_open,
            is_vid_hidden
        ) VALUES (?, ?, ?, ?, ?, ?)";
        $q_qf="INSERT INTO question_file (
            q_id,
            file_name,
            mime
        ) VALUES (?, ?, ?)";
        $q_a="INSERT INTO answer (
            id,
            question_id,
            text,
            price,
            fine
        ) VALUES (?, ?, ?, ?, ?)";

    //Вставка Теста
    
        static::$db->exec($q_t,
        [
            $cur_test_id,
            $user_id,
            $td['title'],
            $td['descript'],
            $td['limit'],
            $td['test_start'],
            $td['test_end'],
            $test_link
        ]);

    //Вставка Вопроов TODO
       
        foreach ($td['qsts'] as $k => $v) {
        
            preparedQuery($q_q,
            [
                "iissii",
                $cur_qst_id,
                $cur_test_id,
                $v['title'],
                $v['text'],
                $v['type'],
                $v['is_vid_hidden']
            ]);

        //Вставка в question_file

            foreach ($v['file_names'] as $va) {
                preparedQuery($q_qf,
                ["iss",
                $cur_qst_id,
                $va['name'],
                strstr($va['mime'],'/',TRUE)
                ]);
            }

        //Вставка Ответов
       
            foreach ($v['answs'] as $va) {
                preparedQuery($q_a,
                [
                    "iisii",
                    $cur_answ_id,
                    $cur_qst_id,
                    $va['text'],
                    $va['price'],
                    $va['fine']
                ]);

                $cur_answ_id++;
            }
            $cur_qst_id++;
        }
        return ['link'=>$test_link,'test_id'=>$cur_test_id];
    } */
    /**
     * <p>Возвращает список тестов или теста, которых создал пользователь</p>
     * @param int user_id - id пользователя
     * @return array ассоциативный массив с полями результата
    */
    public function GetUserTests($user_id){
        return static::$db->exec("SELECT 
        id,title,start,\"end\"
        FROM test
        WHERE s_a_id=$user_id
        ");
    }
    public function GetUserTest($variant_link){
               
        return  static::$db->exec("SELECT t.* 
        FROM test t 
        INNER JOIN variant v ON t.id=v.test_id 
        WHERE v.link='$variant_link'");
    }
    public function GetAllTestVariants($variant_link) {
        return static::$db->exec("SELECT v.* 
        FROM variant v 
        WHERE v.test_id IN(
            SELECT _t.id 
            FROM test _t 
            INNER JOIN variant _v ON _t.id=_v.test_id 
            WHERE _v.link='$variant_link')
        ");
    }

    public function GetTestVariant($variant_link){
        return static::$db->exec("SELECT v.*
        FROM variant v
        WHERE v.link='$variant_link'
        ")[0];
    }
    public function GetAllTestVariants_tid($test_id) {
        return static::$db->exec("SELECT v.* 
            FROM variant v 
            WHERE v.test_id = ?",
            array($test_id)
        );
    }
    /**
     * <p>Создает вопросы для варианта</p>
    */
    public function saveQuestions($q_data,$variant_link) {
        $vid=$this->GetTestVariant($variant_link)['id'];
        $q="SELECT MAX(id) as max_id FROM question";
        $cur_qst_id=intval(static::$db->exec($q)[0]['max_id'])+1;

        $q="SELECT MAX(id) as max_id FROM answer";
        $cur_answ_id=intval(static::$db->exec($q)[0]['max_id'])+1;

        foreach ($q_data as $qst) {
            //обработка вопросов, вставка или обновление
            $qst['id']=intval($qst['id']);
            if($qst['id']!=0){
                $q="UPDATE question 
                SET title=:qtl,
                \"text\"=:qtxt,
                is_open=:qio,
                is_vid_hidden=:qisvh
                WHERE id=:qid";
                $cqid=$qst['id'];
                
            }else{
                $q="INSERT INTO question (
                id,
                title,
                \"text\",
                is_open,
                is_vid_hidden
                ) VALUES(:qid,:qtl,:qtxt,:qio,:qisvh)";
                $cqid=$cur_qst_id++;
                
            }
            static::$db->exec($q,
            array(
				":qid"=>$cqid,
				":qtl"=>$qst['title'],
				":qtxt"=>$qst['text'],
				":qio"=>$qst['is_open'],
				":qisvh"=>$qst['is_vid_hidden']
			));
            
            if($qst['id']==0){
                static::$db->exec("INSERT INTO variant_question (variant_id,question_id) VALUES(?,?)",[$vid,$cqid]);
            }
            //Удаление существующей информации о файлах вопроса, т к она может быть не актуальной
            static::$db->exec("DELETE FROM question_file WHERE q_id=?",array($cqid));
            foreach ($qst['file_names'] as $f) {
                static::$db->exec("INSERT INTO question_file (
                q_id,
                file_name,
                mime
                )VALUES(?, ?, ?)",
                array(
                    $cqid,
                    $f['name'],
                    strstr($f['mime'],'/',TRUE)
                ));
            }

            foreach($qst['answs'] as $a){
                //Обработка ответов аналогично
                $a['id']=intval($a['id']);
                if($a['id']>0){
                    $q="UPDATE answer 
                    SET \"text\"=:atxt,
                    price=:ap,
                    fine=:af
                    WHERE id=:aid";
                    $caid=$a['id'];
                    static::$db->exec($q,
                    array(
                        ':aid'=>$caid,
                        ':atxt'=>$a['text'],
                        ':ap'=>$a['price'],
                        ':af'=>$a['fine']
                    ));
                }else{
                    $q="INSERT INTO answer (
                    id,
                    question_id,
                    \"text\",
                    price,
                    fine
                    ) VALUES(:aid,:qid,:atxt,:ap,:af)";
                    $caid=$cur_answ_id++;
                    static::$db->exec($q,
                    array(
                        ':aid'=>$caid,
                        ':qid'=>$cqid,
                        ':atxt'=>$a['text'],
                        ':ap'=>$a['price'],
                        ':af'=>$a['fine']
                    ));
                }
            }
        }
    }

    /**
     * <p>Возвращает все данные теста, созданого пользователем, включая вопросы, ответы и файлы</p>
     * @param int test_id - id теста
     * 
     * @return array ассоциативный массив test_data с полями результата запроса на получение даных о тесте. Сожержит поле err и err_txt которое указывает на ошибку
    */
   /*  public function GetFullUserTest($test_link,$author_ids=-1){
        $test_data['test'] = static::$db->exec("SELECT * FROM test WHERE link='$test_link'");
        $test_data['err']=FALSE;

        if (count($test_data['test']) == 0) {
            $test_data['err']=TRUE;
            $test_data['err_txt']='Ошибка: такого теста не найдено';
        }else{
            $test_data['test']=$test_data['test'][0];
        }
        // Получение вопросов теста
        $test_data['question']=static::$db->exec(
            "SELECT * FROM question q WHERE q.test_id=".$test_data['test']['id']
        );
       
        foreach ($test_data['question'] as $v) {
            $test_data['answers'][$v['id']]=static::$db->exec("SELECT * FROM answer a WHERE a.question_id=".$v['id']);
            $test_data['files'][$v['id']]=static::$db->exec("SELECT qf.* FROM question_file qf  WHERE qf.q_id=".$v['id']);
        }
        
        return $test_data;
    } */



    /**
     * <p> TODO   Возвращает все вопросы созданые пользователем(-лями)</p>
     * @param string mode - режим работы для одного пользователя или для всех
     * @param int page - номер страницы в списке
     * @return array ассоциативный массив test_data с полями результата запроса на обьединение. Сожержит поле err и err_txt которое указывает на ошибку
     */ 
    /* public function GetUserQuestions($mode,$page=1) {
        $test_data['test'] = static::$db->exec("SELECT * FROM test WHERE link='$test_link'");
        $test_data['err']=FALSE;
        $limit='LIMIT '.($page*10-10).',10';
        switch ($mode) {
            case 'one':
                $test_data['a_question']=static::$db->exec(
                    "SELECT * FROM question q
                    INNER JOIN test_question t_q ON t_q.question_id=q.id
                    INNER JOIN test t ON t_q.test_id=t.id
                    WHERE t.s_a_id=$author_id
                    $limit"
                );
                break;
            case 'all':
                $test_data['a_question']=static::$db->exec(
                    "SELECT * FROM question q
                    $limit"
                );
                break;
            default:
                $test_data['err']=TRUE;
                $test_data['err_txt']='Ошибка: неверный mode, свяжитесь с разработчиком';
                break;
        }
        
        if($author_id!=-1){
            // Получение всех вопросов для выбора в меню вопросов. Только те вопросы которы создал пользователь ранее
            
        }
    } */
    

    /**
     * <p>Проверяет является ли автором теста по ссылке пользователь с переданым id</p>
     * @param string variant_link
     * @param int user_id
     * @return bool Истина если автор теста - это пользователь с переданным id
    */
    public function CheckTestAuthor_link($variant_link,$user_id)
    {
        $res=static::$db->exec("SELECT s_a_id 
        FROM test t INNER JOIN variant v ON t.id=v.test_id 
        WHERE v.link='$variant_link'
        ");
        return count($res)>0 && $user_id==$res[0]['s_a_id'];
    }

    /**
     * <p>Возвращает Логин автора для конструирования ссылок на файлы теста</p>
     * @param mixed $test_id
     * @return string логин автора
     */
    public function GetAuthorLogin($test_id)
    {
        return static::$db->exec("SELECT s.login 
        FROM test t 
        INNER JOIN s_a s ON t.s_a_id=s.id
        WHERE t.id=".$test_id)[0]['login'];
    }

    /**
     * <p>Удаление теста из БД</p>
     * @param int test_link
    */
    public function DeleteTest($test_link,)
    {
        static::$db->exec("DELETE FROM test WHERE link=?",
        array(
            $test_link
        ));
    }


    /**
     * <p>Формирует результат сдачи теста</p>
     *
     * @param string $test_link
     * @param int $user_id
     * @param array $answ_data
     * 
     */
 /*    public function AddResult($test_link, $user_id, $answ_data)
    {
        $date=date('Y-m-d H:i:s');
        //получение test id для Результата
        $test_id=static::$db->exec("SELECT id FROM test WHERE link='$test_link'")[0]['id'];


        //Сохранение Результата в БД:
        preparedQuery("INSERT INTO result 
        (
            s_a_id,
            test_id,
            status,
            date,
            sum
        ) 
        VALUES(?,?,?,?,?)",
        [
            'iiisi',
            $user_id,
            $test_id,
            0,
            $date,
            0
        ]);       


        //получение id
        $res_id=static::$db->exec("SELECT id FROM result 
            WHERE date='$date' AND
            s_a_id=$user_id AND
            test_id=$test_id            
        ")[0]['id'];
    

        foreach ($answ_data as $v) {
            foreach($v['answ'] as $user_answ_id){
                preparedQuery("INSERT INTO saved_answer 
                (
                    res_id,
                    question_id,
                    answer_id,
                    descriptor
                ) 
                VALUES(?,?,?,?)",
                [
                    'iiis',
                    $res_id,
                    $v['q_id'],
                    $user_answ_id,
                    $v['descriptor']==''?NULL:$v['descriptor']
                ]);
            }
        }


        //Перебор всех сохраненных ответов
        $test_answers=static::$db->exec("SELECT
        a.text as 'right_answ_txt',
        a.price as 'price',
        a.fine as 'fine',
        sa.descriptor as 'user_in',
        q.is_open as 'q_is_open',
        t.limit

        FROM result r
        INNER JOIN test t ON r.test_id=t.id
        
        INNER JOIN question q ON q.test_id=t.id
        INNER JOIN answer a ON a.question_id=q.id
        INNER JOIN saved_answer sa ON sa.answer_id=a.id AND sa.res_id=r.id
        WHERE t.link='$test_link' AND r.id=$res_id
        ");
        
        $sum=0;
       
        foreach ($test_answers as $v) {
            if($v['q_is_open']==1){
                //Вопрос открытый: сравнивается пользовательский ввод с правильным ответом
                if(strcasecmp($v['user_in'],$v['right_answ_txt'])==0){
                    $sum+=$v['price'];
                }else{
                    $sum+=$v['fine'];
                }
            }else{
                //Вопрос закрытый: Просто прибавляется цена ответа, в случае неверного ответа цена - на усмотрение автора теста
                $sum+=$v['price'];
            }
        }
        $status = $sum >= $test_answers[0]['limit'];//TRUE если Тест пройден

        preparedQuery("UPDATE result
            SET sum=?,
            status=?
            WHERE id=$res_id",
            [
                'ii',
                $sum,
                $status
            ]
        );
    } */
    /**
     * <p>Возвращает результаты пользователя у конкретного теста</p>
     *
     * @param mixed $test_link
     * @param mixed $user_id
     * 
     * @return array
     * 
     */
    public function GetUserTestResults($test_link,$user_id)
    {
        return static::$db->exec("SELECT
            t.*,
            r.date,r.status,r.sum 
            FROM result r
            INNER JOIN test t ON t.id=r.test_id
            WHERE t.link='$test_link' AND r.s_a_id=$user_id
        ");
        

    }

    /**
     * <p>
     * Возвращает Ассоциативный массив с лучшим результатом пользователя по каждому из пройденых им тестов, который выводится
     * на странице профиля. Если такого результата нет, то выведет последнюю попытку для пройденого пользователем теста
     * </p>
     *
     * @param int $user_id
     * 
     * @return array
     * 
     */
    public function GetUserResults($user_id){
        return static::$db->exec("SELECT 
        t.title,
        v.link,
        r.status,
        r.date
        FROM test t
        INNER JOIN result r ON r.variant_id=t.id
        INNER JOIN variant v ON v.test_id=t.id
        WHERE r.s_a_id=$user_id AND r.date =(
            SELECT 
            _r.date
            FROM result _r
            WHERE _r.s_a_id=$user_id AND _r.variant_id=v.id
            ORDER BY _r.status DESC, _r.date DESC LIMIT 1
        )
        "
        );
    }

    
    /**
     * <p>Возвразвращает попытки пользователей, для вывода на странице со статистикой Теста по его ссылке</p>
     *
     * @param string $test_link
     * @param string $search=''
     * 
     * @return array
     * 
     */
    public function GetTestStatistics_link($test_link, $where='')
    {
        return static::$db->exec("SELECT
            t.s_a_id as 'author_id',
            t.title,
            t.description,
            t.limit,
            t.start,
            t.end,
            t.link,
            s.name as 'user_name',
            r.date,
            r.status,
            r.sum
            FROM test t
            INNER JOIN result r ON r.test_id=t.id
            INNER JOIN s_a s ON r.s_a_id=s.id
            WHERE t.link='$test_link' ".($where!=''?"AND($where)":'')."
        ");
    }
    /**
     * <p>Возвращает секцию WHERE для SQL запросов</p>
     *
     * @param string $search - строка со словами разделеные пробелами по которым будет вестись поиск
     * @param array $fields - массив из строк-полей по которым необходима фильтрация выдачи
     * 
     * @return string
     * 
     */
    static public function GetWhere(string $search='',array $fields=null):string
    {
        if($search!==''){
            $words=getSearchList($search);
            $where='';
            foreach ($words as $word)
            {
                foreach ($fields as $field) {
                    $where.="$field LIKE '%$word%' OR ";
                }
                
            }
            return substr($where, 0, -3);
        }
        return '';
        
    }
}
?>