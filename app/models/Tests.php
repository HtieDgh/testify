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
        WHERE v.link=?";
        $quest_data['questions']=static::$db->exec($q,[$variant_link]);
        $quest_data['variant']=$this->GetTestVariant($variant_link);
        
        foreach ($quest_data['questions'] as $v) 
        {
            $quest_data['answers'][$v['id']]=static::$db->exec("SELECT * FROM answer a WHERE a.question_id=".$v['id']);
            $quest_data['files'][$v['id']]=static::$db->exec("SELECT qf.* FROM question_file qf  WHERE qf.q_id=".$v['id']);
        }
        return $quest_data;
    }
    /**
     * <p>Возвращает список тестов или теста, которых создал пользователь</p>
     * @param int user_id - id пользователя
     * @param int where - секция where для поиска
     * @return array ассоциативный массив с полями результата
     * @see GetWhere
    */
    public function GetUserTests($user_id,$where=''){
        return static::$db->exec("SELECT 
            id,title,start,\"end\"
            FROM test
            WHERE s_a_id=? ".($where!=''?"AND($where)":''),
            [$user_id]
        );
    }
    public function GetUserTest($variant_link){
               
        return  static::$db->exec("SELECT t.* 
        FROM test t 
        INNER JOIN variant v ON t.id=v.test_id 
        WHERE v.link=?",[$variant_link]);
    }
    public function GetAllTestVariants($variant_link) {
        return static::$db->exec("SELECT v.*,COUNT(v_q.question_id) as q_count
        FROM variant v 
        LEFT JOIN variant_question v_q ON v_q.variant_id=v.id
        WHERE v.test_id IN(
            SELECT _t.id 
            FROM test _t 
            INNER JOIN variant _v ON _t.id=_v.test_id 
            WHERE _v.link=?)
        GROUP BY v_q.variant_id,v.id
        ",[$variant_link]);
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
     * <p>Возвращает все данные варианта теста, созданого пользователем, включая вопросы, ответы и файлы</p>
     * @param int test_id - id теста
     * 
     * @return array ассоциативный массив test_data с полями результата запроса на получение даных о тесте. Сожержит поле err и err_txt которое указывает на ошибку
    */
    public function GetFullUserTest($variant_link){
        $test_data['test'] = static::$db->exec("SELECT t.*
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            WHERE v.link=?",
            [$variant_link]
        );
        $test_data['err']=FALSE;

        if (count($test_data['test']) == 0) {
            $test_data['err']=TRUE;
            $test_data['err_txt']='Ошибка: Тест не найден';
        }else{
            $test_data['test']=$test_data['test'][0];
        }
        // Получение вопросов теста
        $test_data=array_merge($test_data,$this->getQuestionData($variant_link));
        
        return $test_data;
    }
    

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
        WHERE v.link=?
        ",[$variant_link]);
        return count($res)>0 && $user_id==$res[0]['s_a_id'];
    }


    /**
     * <p>Проверяет является ли автором теста по id пользователь с переданым uid</p>
     * @param int test_id
     * @param int user_id
     * @return bool Истина если автор теста - это пользователь с переданным id
    */
    public function CheckTestAuthor_tid($test_id,$user_id)
    {
        $res=static::$db->exec("SELECT s_a_id 
        FROM test t WHERE id=?
        ",[$test_id]);
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
     * <p>Удаление теста из БД по его id. Удаляет также и вопросы/ответы всех вариантов этого теста</p>
     * @param int test_link
    */
    public function DeleteTest($test_id)
    {
        static::$db->exec("DELETE FROM question q WHERE q.id IN(
            SELECT question_id FROM variant_question v_q 
            INNER JOIN variant v ON v.id=v_q.variant_id
            WHERE v.test_id=?
        )",
        [
            $test_id
        ]);
        static::$db->exec("DELETE FROM test WHERE id=?",
        [
            $test_id
        ]);
    }
    /**
     * <p>Удаление варианта теста из БД по его link. Не удаляет вопросы</p>
     * @param int test_link
    */
    public function DeleteVariant($variant_link)
    {
        static::$db->exec("DELETE FROM variant
            WHERE link=?",
        [
            $variant_link
        ]);
    }

    /**
     * <p>Формирует результат сдачи теста</p>
     *
     * @param string $test_link
     * @param int $user_id
     * @param array $answ_data
     * 
     */
    public function AddResult($variant_link, $user_id, $answ_data)
    {
        $date=date('Y-m-d H:i:s');
        //получение variant id для Результата
        $variant_id=static::$db->exec("SELECT id FROM variant WHERE link=?",[$variant_link])[0]['id'];

        //Сохранение Результата в БД:
        static::$db->exec("INSERT INTO result 
        (
            s_a_id,
            variant_id,
            \"status\",
            \"date\",
            sum
        ) 
        VALUES(?,?,?,?,?)",
        [
            $user_id,
            $variant_id,
            0,
            $date,
            0
        ]);

        //получение id
        $res_id=static::$db->exec("SELECT id FROM result 
            WHERE date='$date' AND
            s_a_id=$user_id AND
            variant_id=$variant_id            
        ")[0]['id'];
    
        foreach ($answ_data as $v) {
            foreach($v['answ'] as $user_answ_id){
                static::$db->exec("INSERT INTO saved_answer 
                (
                    res_id,
                    question_id,
                    answer_id,
                    descriptor
                ) 
                VALUES(?,?,?,?)",
                [
                    $res_id,
                    $v['q_id'],
                    $user_answ_id,
                    $v['descriptor']==''?NULL:$v['descriptor']
                ]);
            }
        }


        //Перебор всех сохраненных ответов для подсчета результата
        $test_answers=static::$db->exec("SELECT
        a.text as right_answ_txt,
        a.price as price,
        a.fine as fine,
        sa.descriptor as user_in,
        q.is_open as q_is_open,
        t.limit

        FROM result r
        INNER JOIN variant v ON r.variant_id=v.id
        INNER JOIN test t ON v.test_id=t.id
        INNER JOIN variant_question v_q ON v_q.variant_id=v.id
        INNER JOIN question q ON q.id=v_q.question_id
        INNER JOIN answer a ON a.question_id=q.id
        INNER JOIN saved_answer sa ON sa.answer_id=a.id AND sa.res_id=r.id
        WHERE v.id=$variant_id AND r.id=$res_id
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

        static::$db->exec("UPDATE result
            SET sum=?,
            \"status\"=?
            WHERE id=$res_id",
            [
                $sum,
                $status
            ]
        );
    }
    /**
     * <p>Возвращает результаты пользователя у конкретного варианта теста</p>
     *
     * @param mixed $variant_link
     * @param mixed $user_id
     * 
     * @return array
     * 
     */
    public function GetUserTestResults($variant_link,$user_id)
    {
        return static::$db->exec("SELECT
            t.*,
            v.title as v_title,
            v.link as v_link,
            r.date,r.status,r.sum 
            FROM result r
            
            INNER JOIN variant v ON v.id=r.variant_id
            INNER JOIN test t ON t.id=v.test_id
            WHERE v.link=? AND r.s_a_id=?
        ",[$variant_link,$user_id]);
        

    }

    /**
     * <p>
     * Возвращает Ассоциативный массив с лучшим результатом пользователя по каждому из пройденых им тестов, который выводится
     * на странице профиля. Если такого результата нет, то выведет последнюю попытку для пройденого пользователем теста
     * </p>
     * @param int $user_id
     * @return array
     */
    public function GetUserResults($user_id,$where=''){
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
        )".($where!=''?"AND($where)":'')
        );
    }

    /**
     * <p>Возвразвращает попытки пользователей, для вывода на странице со статистикой Теста по его ссылке</p>
     *
     * @param string $test_id
     * @param array $where[] - массив резульатов работы GetWhere
     * 
     * @return array 
     * 
     * @see Tests::GetWhere
     */
    public function GetTestStatistics_tid($test_id, $where)
    {
        $out['test']=static::$db->exec("SELECT
            t.id as test_id,
            t.s_a_id as author_id,
            t.title,
            t.description,
            t.limit,
            t.start,
            t.end
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            WHERE t.id=?",
            [
                $test_id
            ]
        )[0];
        $out['variants']=static::$db->exec("SELECT
            v.link as v_link,
            v.title as v_title,
            COUNT(v_q.question_id) as q_count
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            LEFT JOIN variant_question v_q ON v_q.variant_id=v.id
            WHERE t.id=? 
            ".($where['variants']!=''?'AND('.$where['variants'].')':'').
            "GROUP BY v_q.variant_id,v.id",
            [
                $test_id
            ]
        );
        $out['results']=static::$db->exec("SELECT
            v.title as v_title,
            s.name as user_name,
            t.limit,
            r.date,
            r.status,
            r.sum
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            INNER JOIN result r ON r.variant_id=v.id
            INNER JOIN s_a s ON r.s_a_id=s.id
            WHERE t.id=? ".($where['results']!=''?"AND(".$where['results'].")":''),
            [
                $test_id
            ]
        );
        return $out;
    }
   
    /**
     * <p>Возвращает секцию WHERE для SQL запросов, для поиска</p>
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
            $words=CFuns::getSearchList($search);
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