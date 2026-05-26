<?php namespace model;
class Test{
    public static \DB\SQL $db;
    public function __construct(&$db) {
        static::$db=$db;
    }
     /**
     * <p> Принимает test_data и добавляет тест в бд</p>
     * @param array td - данные о тесте 
     * @param array user_id - автор теста
     * @return int id созданого теста
     */ 
    public function CreateTest($td,$user_id):int
    {
        static::$db->exec(
        "INSERT INTO test (
            author_id,
            title,
            `description`,
            minimum,
            datetime_start,
            datetime_end
        ) VALUES (?, ?, ?, ?, ?, ?)",
        [
            $user_id,
            $td['title'],
            $td['description'],
            $td['minimum'],
            $td['test_start'],
            $td['test_end']
        ]);

        return static::$db->exec("SELECT LAST_INSERT_ID() as id")[0]['id'];
    }
    //Возварщает variant_link активного теста для последующего заполнения вопросами
    public function CreateVariants($test_id,$vd){
        $out='';
        foreach ($vd as $v) 
        {
            $variant_link=md5($test_id.$v['title']);
            if($v['is_active']){
                $out=$variant_link;
            }
            static::$db->exec(
               "INSERT INTO variant (
                    test_id,
                    title,
                    unique_url
                ) VALUES (?,?,?)",
                [
                    $test_id,
                    $v['title'],
                    $variant_link
                ]
            );
        }
        return $out;
    }
    public function UpdateVariants($vd,$test_id) {
        $out='';
        foreach ($vd as $v) {
            if($v['is_active']){
                $out = $v['unique_url'] == '0'?md5($test_id.$v['title']):$v['unique_url'];
            }
            if($v['unique_url']=='0'){
                //Был добавлен новый вариант в старый тест
                static::$db->exec("INSERT INTO variant (
                    test_id,
                    title,
                    unique_url
                ) VALUES (?,?,?)",
                [
                    $test_id,
                    $v['title'],
                    md5($test_id.$v['title'])
                ]);
            }else{
                static::$db->exec("UPDATE variant
                SET title=?
                WHERE unique_url=?",
                [
                    $v['title'],
                    $v['unique_url']
                ]);
            }
        }
        return $out;
    }
    /**
     * Принимает test_data и обновляет тест в бд
     * @param array td - данные о тесте 
     * @return bool результат $db->exec()
     */ 
    public function UpdateTest($td) {

        $q="UPDATE test 
        SET title=?,
         `description`=?,
         `minimum`=?, 
         `datetime_start`=?,
         `datetime_end`=?
        WHERE id=?";
        return static::$db->exec($q,
        [
            $td['title'],
            $td['description'],
            $td['minimum'],
            $td['test_start'],
            $td['test_end'],
            $td['test_id']
        ]);
    }

    public function getQuestionData($variant_link){

        $q="SELECT q.*,q_o.id as 'is_open',q_o.fine as 'fine' FROM question q
        INNER JOIN variant_question v_q ON v_q.question_id=q.id
        INNER JOIN variant v ON v.id=v_q.variant_id
        LEFT JOIN question_open q_o ON q_o.id=q.id
        WHERE v.unique_url=?";
        $quest_data['questions']=static::$db->exec($q,[$variant_link]);
        $quest_data['variant']=$this->GetVariant($variant_link)[0];
        
        foreach ($quest_data['questions'] as $v) 
        {
            $quest_data['answers'][$v['id']]=static::$db->exec("SELECT * FROM answer a WHERE a.question_id=".$v['id']);
            $quest_data['files'][$v['id']]=static::$db->exec("SELECT qf.* FROM question_file qf  WHERE qf.question_id=".$v['id']);
        }
        return $quest_data;
    }
    /**
     * <p>Возвращает список тестов или теста, которых создал пользователь</p>
     * @param int user_id - id пользователя
     * @param array where - секция where для поиска
     * @return array ассоциативный массив с полями результата
     * @see GetWhere
    */
    public function GetUserTests($user_id,$where=[]){
        
        return static::$db->exec("SELECT 
            t.id,title,datetime_start as 'start', datetime_end as 'end',s.name as author_name
            FROM test t
            INNER JOIN `profile` s ON s.id=t.author_id
            WHERE author_id=:aid AND(".$where['where'].")",
            array_merge($where['ws'],[':aid'=>$user_id])
        );
    }
    public function GetAllUserTests($where=[]){
        return static::$db->exec("SELECT 
            t.id,title,t.datetime_start as 'start',t.datetime_end as 'end',s.name as author_name
            FROM test t
            INNER JOIN `profile` s ON s.id=t.author_id
            WHERE ".$where['where'],
            $where['ws']
        );
    }
    public function GetUserTest($variant_link){
               
        return  static::$db->exec("SELECT t.* 
        FROM test t 
        INNER JOIN variant v ON t.id=v.test_id 
        WHERE v.unique_url=?",[$variant_link]);
    }
    public function GetAllTestVariants($variant_link) {
        return static::$db->exec("SELECT v.*,COUNT(v_q.question_id) as q_count
        FROM variant v 
        LEFT JOIN variant_question v_q ON v_q.variant_id=v.id
        WHERE v.test_id IN(
            SELECT _t.id 
            FROM test _t 
            INNER JOIN variant _v ON _t.id=_v.test_id 
            WHERE _v.unique_url=?)
        GROUP BY v_q.variant_id,v.id
        ",[$variant_link]);
    }

    public function GetVariant($variant_link){
        return static::$db->exec("SELECT v.*
        FROM variant v
        WHERE v.unique_url=?",$variant_link);
    }
    public function GetAllTestVariants_tid($test_id) {
        return static::$db->exec("SELECT v.* 
            FROM variant v 
            WHERE v.test_id = ?",
            [$test_id]
        );
    }
    /**
     * Создает вопросы для варианта
    */
    public function saveQuestions($q_data,$variant_link,$fileDir='') {
        $vid=$this->GetVariant($variant_link)[0]['id'];
        $q="SELECT MAX(id) as max_id FROM question";
        $cur_qst_id=intval(static::$db->exec($q)[0]['max_id'])+1;

        $q="SELECT MAX(id) as max_id FROM answer";
        $cur_answ_id=intval(static::$db->exec($q)[0]['max_id'])+1;
        
        foreach ($q_data as $qst) {
            //обработка вопросов, вставка или обновление
            $qst['id']=intval($qst['id']);
            if($qst['id']!=0)
            {
                //изменить вопрос
                $cqid=$qst['id'];
                static::$db->exec(
                    "UPDATE question 
                    SET title=:qtl,
                    `text`=:qtxt,
                    is_vid_hidden=:qisvh
                    WHERE id=:qid",
                    [
                        ":qid"=>$cqid,
                        ":qtl"=>$qst['title'],
                        ":qtxt"=>$qst['text'],
                        ":qisvh"=>$qst['is_vid_hidden']
                    ]
                );

                if(
                    $qst['is_open']=='0'
                ){
                    //вопрос стал закрытым => удалить из question_open
                    static::$db->exec(
                        "DELETE FROM question_open WHERE id=:qid",
                        [
                            ":qid"=>$cqid
                        ]
                    );
                }else if(
                    !empty(static::$db->exec(
                    "SELECT 1 FROM question_open WHERE id=:qid",
                    [
                        ":qid"=>$cqid
                    ]))
                ){
                    //Вопрос был открытым => обновить fine
                    static::$db->exec(
                        "UPDATE question_open 
                        SET fine=:qfine
                        WHERE id=:qid",
                        [
                            ":qid"=>$cqid,
                            ":qfine"=>$qst['answs'][0]['fine']
                        ]
                    );
                }else{
                    //Вопрос стал открытым => добавить id и fine
                    static::$db->exec(
                        "INSERT INTO question_open (id,fine)
                        VALUES(:qid,:qfine)",
                        [
                            ":qid"=>$cqid,
                            ":qfine"=>$qst['answs'][0]['fine']
                        ]
                    );
                }
            }else{
                //добавить новый вопрос
                $cqid=$cur_qst_id++;
                static::$db->exec(
                    "INSERT INTO question (
                    id,
                    title,
                    `text`,
                    is_vid_hidden
                    ) VALUES(:qid,:qtl,:qtxt,:qisvh)",
                    [
                        ":qid"=>$cqid,
                        ":qtl"=>$qst['title'],
                        ":qtxt"=>$qst['text'],
                        ":qisvh"=>$qst['is_vid_hidden']
                    ]
                );
                if($qst['is_open']=='1')
                {
                    //добавить открытый вопрос
                    static::$db->exec(
                        "INSERT INTO question_open (`id`,`fine`)VALUES(:qid,:fine)",
                        [
                            ":qid"=>$cqid,
                            ":fine"=>$qst['answs'][0]['fine']
                        ]
                    );
                }
            }

            
            //добавить новый вопрос в связующую таблицу
            if($qst['id']==0){
                static::$db->exec("INSERT INTO variant_question (variant_id,question_id) VALUES(?,?)",[$vid,$cqid]);
            }
            //Удаление существующей информации о файлах вопроса, т к она может быть не актуальной
            static::$db->exec("DELETE FROM question_file WHERE question_id=?",[$cqid]);
            foreach ($qst['file_names'] as $f) {
                static::$db->exec("INSERT INTO question_file (
                question_id,
                file_name,
                mime
                )VALUES(?, ?, ?)",
                [
                    $cqid,
                    $f['name'],
                    strstr($f['mime'],'/',TRUE)
                ]);
            }

            foreach($qst['answs'] as $a){
                //Обработка ответов аналогично
                $a['id']=intval($a['id']);
                if($a['id']>0){
                    $q="UPDATE answer 
                    SET `text`=:atxt,
                    price=:ap
                    WHERE id=:aid";
                    $caid=$a['id'];
                    static::$db->exec($q,
                    [
                        ':aid'=>$caid,
                        ':atxt'=>$a['text'],
                        ':ap'=>$a['price'],
                    ]);
                }else{
                    $q="INSERT INTO answer (
                    id,
                    question_id,
                    `text`,
                    price
                    ) VALUES(:aid,:qid,:atxt,:ap)";
                    $caid=$cur_answ_id++;
                    static::$db->exec($q,
                    [
                        ':aid'=>$caid,
                        ':qid'=>$cqid,
                        ':atxt'=>$a['text'],
                        ':ap'=>$a['price']
                    ]);
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
        $test_data['test'] = static::$db->exec("SELECT 
            t.id,
            t.author_id,
            t.title,
            t.description,
            t.created,
            t.minimum,
            t.datetime_start as 'start',
            t.datetime_end as 'end'
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            WHERE v.unique_url=?",
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
        $res=static::$db->exec("SELECT author_id 
        FROM test t INNER JOIN variant v ON t.id=v.test_id 
        WHERE v.unique_url=?
        ",[$variant_link]);
        return count($res)>0 && $user_id==$res[0]['author_id'];
    }

    public function isNotTest_link($variant_link) : bool {
        return empty(static::$db->exec("SELECT 1 FROM test t INNER JOIN variant v on v.test_id=t.id WHERE v.unique_url=?",$variant_link));
    }
    /**
     * <p>Проверяет является ли автором теста по id пользователь с переданым uid</p>
     * @param int test_id
     * @param int user_id
     * @return bool Истина если автор теста - это пользователь с переданным id
    */
    public function CheckTestAuthor_tid($test_id,$user_id)
    {
        $res=static::$db->exec("SELECT author_id 
        FROM test t WHERE id=?
        ",[$test_id]);
        return count($res)>0 && $user_id==$res[0]['author_id'];
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
        INNER JOIN secured_account s ON t.author_id=s.id
        WHERE t.id=".$test_id)[0]['login'];
    }

    /**
     * <p>Удаление теста из БД по его id. Удаляет также и вопросы/ответы всех вариантов этого теста</p>
     * @param int test_id
    */
    public function deleteTest($test_id)
    {
        static::$db->exec(
            [
            "DELETE FROM `try` WHERE variant_id IN(SELECT id FROM variant WHERE test_id=?)",
            "DELETE FROM question WHERE id IN(
                SELECT question_id FROM variant_question v_q 
                INNER JOIN variant v ON v.id=v_q.variant_id
                WHERE v.test_id=?
            )",
            "DELETE FROM test WHERE id=?"
            ],
            [
                [$test_id],
                [$test_id],
                [$test_id]
            ]
        );
    }
    /**
     * <p>Удаление варианта теста из БД по его unique_url. Не удаляет вопросы</p>
     * @param int test_link
    */
    public function deleteVariant($variant_link)
    {
        static::$db->exec("DELETE FROM variant
            WHERE unique_url=?",
        [
            $variant_link
        ]);
    }
    
    /**
     * Удаление варианта и всех его вопросов
     *
     * @param  mixed $variant_link
     * @return int
     * @see THttp->uploadBackup()
     */
    public function deleteVariantWithQuestions($variant_link){
        
        static::$db->exec(
            "DELETE FROM question WHERE id IN(
                SELECT question_id FROM variant_question v_q 
                INNER JOIN variant v ON v.id=v_q.variant_id
                WHERE v.unique_url=?
            )",
            $variant_link
        );
        $this->deleteVariant($variant_link);
        return static::$db->count();
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
        $variant_id=static::$db->exec("SELECT id FROM variant WHERE unique_url=?",[$variant_link])[0]['id'];

        //Сохранение Результата в БД:
        static::$db->exec("INSERT INTO try 
        (
            member_id,
            variant_id,
            `status`,
            `created`,
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
        $try_id=static::$db->exec("SELECT LAST_INSERT_ID() as id")[0]['id'];
        foreach ($answ_data as $v) {
            foreach($v['answ'] as $user_answ_id){
                static::$db->exec("INSERT INTO saved_answer 
                (
                    try_id,
                    question_id,
                    answer_id,
                    user_input
                ) 
                VALUES(?,?,?,?)",
                [
                    $try_id,
                    $v['q_id'],
                    $user_answ_id,
                    $v['user_input']==''?NULL:$v['user_input']
                ]);
            }
        }


        //Перебор всех сохраненных ответов для подсчета результата
        $test_answers=static::$db->exec("SELECT
        a.text as right_answ_txt,
        a.price as price,
        qo.fine as fine,
        sa.user_input as user_in,
        qo.id as q_is_open,
        t.minimum
        FROM try r
        INNER JOIN variant v ON r.variant_id=v.id
        INNER JOIN test t ON v.test_id=t.id
        INNER JOIN variant_question v_q ON v_q.variant_id=v.id
        INNER JOIN question q ON q.id=v_q.question_id
        LEFT JOIN question_open qo ON q.id=qo.id
        INNER JOIN answer a ON a.question_id=q.id
        INNER JOIN saved_answer sa ON sa.answer_id=a.id AND sa.try_id=r.id
        WHERE v.id=$variant_id AND r.id=$try_id
        ");
        
        $sum=0;
       
        foreach ($test_answers as $v) {
            if($v['q_is_open']!=null){
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
        $status = $sum >= $test_answers[0]['minimum'];//TRUE если Тест пройден

        static::$db->exec("UPDATE try
            SET sum=?,
            `status`=?
            WHERE id=$try_id",
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
            t.id,
            t.author_id,
            t.title,
            t.description,
            t.created,
            t.minimum,
            t.datetime_start as 'start',
            t.datetime_end as 'end',
            v.title as v_title,
            v.unique_url as v_link,
            r.created,r.status,r.sum 
            FROM try r
            
            INNER JOIN variant v ON v.id=r.variant_id
            INNER JOIN test t ON t.id=v.test_id
            WHERE v.unique_url=? AND r.member_id=?
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
    public function GetUserResults(int $userId,array $where){

        return static::$db->exec("SELECT 
        t.title,
        v.unique_url,
        v.title as 'v_title',
        r.status,
        r.created
        FROM test t
        INNER JOIN variant v ON v.test_id=t.id
        INNER JOIN try r ON r.variant_id=v.id
        WHERE r.member_id=$userId AND r.created =(
            SELECT 
            _r.created
            FROM try _r
            WHERE _r.member_id=$userId AND _r.variant_id=v.id
            ORDER BY _r.status DESC, _r.created DESC LIMIT 1
        ) AND(".$where['where'].")",
        $where['ws']
        );
    }

    /**
     * Возвразвращает попытки пользователей, для вывода на странице со статистикой Теста, по его id
     *
     * @param string $test_id
     * @param array $where[] - массив резульатов работы GetWhere
     * 
     * @return array 
     * 
     * @see Tests::GetWhere
     */
    public function GetTestStatistics_tid(int $test_id, array $varWhere, array $resWhere,$variant_link='')
    {
        $out=['test'=>[],'variants'=>[],'results'=>[]];
        $out['test']=static::$db->exec("SELECT DISTINCT 
            t.id as test_id,
            t.author_id,
            t.title,
            t.description,
            t.minimum,
            t.datetime_start as 'start',
            t.datetime_end as 'end'
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            WHERE t.id=:tid",
            [
                ':tid'=>$test_id
            ]
        )[0];
        
        $out['variants']=static::$db->exec("SELECT
            v.unique_url as v_link,
            v.title as v_title,
            COUNT(v_q.question_id) as q_count
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            LEFT JOIN variant_question v_q ON v_q.variant_id=v.id
            WHERE t.id=:tid AND(".$varWhere['where'].")".($variant_link!=''?"AND v.unique_url='$variant_link'":'')."
            GROUP BY v_q.variant_id,v.id",
            array_merge(
                [':tid'=>$test_id],
                $varWhere['ws']
            )
        );
        $out['results']=static::$db->exec("SELECT
            v.title as v_title,
            s.name as user_name,
            t.minimum,
            r.created,
            r.status,
            r.sum
            FROM test t
            INNER JOIN variant v ON v.test_id=t.id
            INNER JOIN try r ON r.variant_id=v.id
            INNER JOIN profile s ON r.member_id=s.id
            WHERE t.id=:tid AND(".$resWhere['where'].")".($variant_link!=''?"AND v.unique_url='$variant_link'":''),
            array_merge(
                [':tid'=>$test_id],
                $resWhere['ws']
            )
        );
        return $out;
    }
   
    /**
     * Возвращает секцию WHERE и ws массив для SQL запросов, для поиска
     *
     * @param string $search - строка со словами разделеные пробелами по которым будет вестись поиск
     * @param array $fields - массив из строк-полей по которым необходима фильтрация выдачи
     * 
     * @return array
     * 
     */
    public static function GetWhere(array $searchWords=[''],array $fields=[], array $havingFields=[]) : array
    {
        $ws=[];
        if($searchWords[0]!=''){
            $tmp=array_fill(0,count($fields),[]);
            $tmp2=array_fill(0,count($havingFields),[]); 
            foreach ($searchWords as $k=>$word)
            {
                foreach ($fields as $d=>$field) {
                    $tmp[$d][]="$field LIKE :word$k";
                }
                foreach ($havingFields as $d=>$field) {
                    $tmp2[$d][]="$field LIKE :hword$k";
                }
                $ws[":word$k"]="%$word%";
                $ws[":hword$k"]="%$word%";
            }            
            return [
                'where'=>implode(' OR ',array_map(function ($v) { return implode(' OR ',$v); },$tmp)),
                'having'=>implode(' OR ',array_map(function ($v) { return implode(' OR ',$v); },$tmp2)),
                'ws'=>$ws
            ];
        }
        return ['where'=>'1','having'=>'1','ws'=>[]];
    }
}
?>