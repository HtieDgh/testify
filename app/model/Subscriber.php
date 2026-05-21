<?php namespace model;
final class Subscriber
{
    public $userId;
    public $authors=[];
    public $courses=[];
    public $count=0;
    public $dropSearch;
    public $pageHtml;

    public function __construct($userId){
        $this->userId=$userId;
    }

    //Получить список курсов выбраного автора
    public function getCourseList(&$db,$searchWords=[0=>''],$authorId=0,$page=0,$isGetRqstCount=false, $courseId=0)
    {
        $requstCount=$isGetRqstCount?"(SELECT count(*) FROM `course_subscriber` WHERE `course_id`=c.`id` AND `is_confirmed`=0) as 'rqst_count',":'';
        $where='1';
        $limit=$page>1?'LIMIT '.($page*10-10).',10':'';
        $ws=[];
        if($searchWords[0]!=""){
            $tmp=array_fill(0,2,[]); $i=0;
            // Поиск среди авторов
            foreach($searchWords as $word){
                $tmp[0][]="`title` LIKE :word$i";
                $tmp[1][]="`description` LIKE :word$i";
                $ws[":word$i"]="%$word%";
                $i++;
            }
            $where=implode(' OR ',array_map(function ($v) { return implode(' OR ',$v); },$tmp));
           
        }
        if($authorId!=0){
            $ws[":authorId"]=$authorId;
            $where="c.`author_id`=:authorId AND($where)";
        }
        if($courseId!=0){
            $ws[":courseId"]=$courseId;
            $where="c.`id`=:courseId AND($where)";
        }

        $where="WHERE $where";
        $this->courses = $db->exec(
            "SELECT c.`id`,c.`title`,c.`description`,c.`ava_url`,DATE_FORMAT(c.`created`,'%Y-%m-%d') as 'created',c.`is_private`,
            (SELECT count(*) FROM `course_subscriber` WHERE `course_id`=c.`id` AND `is_confirmed`=1) as 'subs_count',
            $requstCount
            (SELECT count(*) FROM `note` n WHERE n.`course_id`=c.`id`) as 'notes_count',
            (SELECT 
                CASE WHEN EXISTS (
                            SELECT 1  FROM `course_subscriber` cs 
                            WHERE`subscriber_id` = {$this->userId} AND c.`id`=cs.`course_id` AND cs.`is_confirmed`=1
                            ) THEN 1
                    WHEN EXISTS(
                            SELECT 1 `subscriber_id` FROM `course_subscriber` cs
                            WHERE `subscriber_id` = {$this->userId} AND c.`id`=cs.`course_id` AND cs.`is_confirmed`=0
                        ) THEN 2
                    WHEN c.`author_id`={$this->userId} THEN 3
                    ELSE 0 
                END
                ) as 'is_subbed'
            FROM `course` c 
            $where 
            ORDER BY `created` DESC 
            $limit",
            $ws
        );
        
        $this->count=count($this->courses);
        return $this->courses;
    }

    //получить список курсов выбранного подписчика
    public function getMyCourseList(&$db,$searchWords=[0=>''],$page=0,$subscriberId=0)
    {
        $where='1';
        $limit=$page>1?'LIMIT '.($page*10-10).',10':'';
        $ws=[];
        if($searchWords[0]!=""){
            $tmp=array_fill(0,2,[]); $i=0;
            // Поиск среди авторов
            foreach($searchWords as $word){
                $tmp[0][]="`title` LIKE :word$i";
                $tmp[1][]="`description` LIKE :word$i";
                $ws[":word$i"]="%$word%";
                $i++;
            }
            $where=implode(' OR ',array_map(function ($v) { return implode(' OR ',$v); },$tmp));
        }
        if($subscriberId!=0){
            $ws[":subscriberId"]=$subscriberId;
            $where="cs.`subscriber_id`=:subscriberId AND($where)";
        }
        $where="WHERE $where";
        $this->courses = $db->exec(
            "SELECT c.`id`,c.`title`,c.`description`,c.`ava_url`,DATE_FORMAT(c.`created`,'%Y-%m-%d') as 'created',c.`is_private`,
            (SELECT count(*) FROM `course_subscriber` WHERE `course_id`=c.`id` AND `is_confirmed`=1) as 'subs_count',
            (SELECT count(*) FROM `note` n WHERE n.`course_id`=c.`id`) as 'notes_count',
            (SELECT 
                CASE WHEN EXISTS (
                            SELECT 1  FROM `course_subscriber` cs 
                            WHERE`subscriber_id` = $this->userId AND c.`id`=cs.`course_id` AND cs.`is_confirmed`=1
                            ) THEN 1
                    WHEN EXISTS(
                            SELECT 1 `subscriber_id` FROM `course_subscriber` cs
                            WHERE `subscriber_id` = $this->userId AND c.`id`=cs.`course_id` AND cs.`is_confirmed`=0
                        ) THEN 2
                    WHEN c.`author_id`=$this->userId THEN 3
                    ELSE 0 
                END
                ) as 'is_subbed'
            FROM `course` c 
            INNER JOIN `course_subscriber` cs on c.`id`=cs.`course_id`
            $where 
            ORDER BY `created` DESC 
            $limit",
            $ws
        );

        $this->count=count($this->courses);
        return $this->courses;
    }
    /**
     * Получить список авторов  
     *
     * @param  mixed $db
     * @param  mixed $searchWords Массив с фразами для поиска
     * @param  int $pageNum № страницы результата
     * @param  int $mode режим(0 - все авторы; 1 - только текущие подписки; 2 - только одного автора)
     * @param  int $authorId id автора для режима 2
     * @return array двумерный массив содержащий авторов контента
     */
    public function getAuthorsList($db, $searchWords, $pageNum=0, $mode=0, $authorId=0) : array
    {
        $limit=$pageNum!==0?'LIMIT '.($pageNum*10-10).',10':'';
        //Поиск
        $where='1';
        $ws=[];
        if($searchWords[0]!=''){
            $tmp=array_fill(0,2,[]); $i=0;
            // Поиск среди авторов
            foreach($searchWords as $word){
                $tmp[0][]="`name` LIKE :word$i";
                $tmp[1][]="`status` LIKE :word$i";
                $ws[":word$i"]="%$word%";
                $i++;
            }
            $where=implode(' OR ',array_map(function ($v) { return implode(' OR ',$v); },$tmp));
        }

        $where="WHERE  $where";
        switch ($mode) {
            case 0:// все авторы
                $q="SELECT `id`,`name`,`status`,`ava_url`
                    ,(SELECT count(*) FROM `subscriber` WHERE `author_id`=s_a.`id`) as 'subs_count' 
                    ,(SELECT count(*) FROM `note` WHERE `author_id`=s_a.`id`) as 'notes_count'
                    ,(SELECT CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM `subscriber` 
                            WHERE `author_id` = s_a.`id` AND `subscriber_id` = $this->userId
                        ) THEN 1 
                        WHEN s_a.`id` = $this->userId THEN 2 
                        ELSE 0 
                    END) as 'is_subbed'
                    FROM `secured_account` s_a
                    INNER JOIN `profile` USING(`id`)
                    INNER JOIN `author` USING(`id`)
                    $where
                    ORDER BY `subs_count` DESC 
                    $limit
                ";
                break;
            case 1:// только текущие подписки
                $where.=" AND `subscriber_id`=".$this->userId." AND `author_id`=`secured_account`.`id`";
                $q="SELECT `id`,`name`,`status`,`ava_url`
                    ,(SELECT count(*) FROM `subscriber` WHERE `author_id`=`secured_account`.`id`) as 'subs_count' 
                    ,(SELECT count(*) FROM `note` WHERE `author_id`=`secured_account`.`id`) as 'notes_count' 
                    ,(SELECT 1 FROM `subscriber` WHERE`subscriber_id`=$this->userId AND `author_id`=`secured_account`.`id`) as 'is_subbed'
                    FROM `secured_account` 
                    INNER JOIN `subscriber` on `author_id`=`id`
                    INNER JOIN `profile` USING(`id`)
                    $where
                    ORDER BY `subs_count` DESC $limit
                ";
                break;
            case 2: // только одного автора
                $q="SELECT `id`,`name`,`status`,`ava_url`
                    ,(SELECT count(*) FROM `subscriber` WHERE `author_id`=`secured_account`.`id`) as 'subs_count' 
                    ,(SELECT count(*) FROM `note` WHERE `author_id`=`secured_account`.`id`) as 'notes_count'
                    ,(SELECT (CASE WHEN (".$this->userId." IN (
                                                    SELECT `subscriber_id` FROM `subscriber` WHERE `secured_account`.`id`=`subscriber`.`author_id`
                                                    )
                                        ) THEN 1 
                                WHEN `secured_account`.`id`=".$this->userId." THEN 2 
                                ELSE 0 END) as 'ext' 
                        FROM `subscriber` GROUP BY 'ext'
                    ) as 'is_subbed'
                    FROM `secured_account`
                    INNER JOIN `profile` USING(`id`)
                    INNER JOIN `author` USING(`id`)
                    WHERE `secured_account`.`id`=:authorId
                ";
                $ws=[':authorId'=>$authorId];
                break;
            default:
                return [];
        }
        
        $this->authors=$db->exec($q,$ws);
        
        $this->count=count($this->authors);//кол-во авторов
        return $this->authors;
    }
    public static function getAuthorId($db,int $authorId)
    {
        return $db->exec(
            "SELECT `id` FROM `author` WHERE `id`=:authorId",
            [
                ':authorId'=>$authorId
            ]
        );
    }
    public static function getCourseAuthorByCourseId($db,int $courseId)
    {
        return $db->exec(
            "SELECT s.* FROM `profile` s INNER JOIN course c ON c.author_id=s.id WHERE c.id=:courseId",
            [
                ':courseId'=>$courseId
            ]
        );
    }
    public static function getCoursesBySubId($db,$subscriberId)
    {
        return $db->exec(
            "SELECT 
            *
            FROM `course` c
            INNER JOIN `course_subscriber` cs ON cs.`course_id`=c.`id`
            WHERE cs.`subscriber_id`=? AND cs.`is_confirmed`=1
            ",
            [$subscriberId]
        );
    }
    public static function subscribeToAuthor($db,int $subscriberId,int $authorId)
    {
        return $db->exec(
            "INSERT INTO  `subscriber`(`subscriber_id`,`author_id`)VALUES(:subscriberId,:authorId)",
            [
                'subscriberId'=>$subscriberId,
                ':authorId'=>$authorId
            ]
        );
    }
    public static function unsubscribeToAuthor($db,int $subscriberId,int $authorId)
    {
        return $db->exec(
            "DELETE FROM  `subscriber` WHERE `subscriber_id`=:subscriberId AND`author_id`=:authorId",
            [
                'subscriberId'=>$subscriberId,
                ':authorId'=>$authorId
            ]
        );
    }
    public static function subscribeToCourse($db,int $subscriberId,int $courseId,int $confirmed)
    {
        return $db->exec(
            "INSERT INTO  `course_subscriber`(`subscriber_id`,`course_id`,`is_confirmed`)VALUES(:subscriberId,:courseId,:confirmed)",
            [
                ':subscriberId'=>$subscriberId,
                ':courseId'=>$courseId,
                ':confirmed'=>$confirmed
            ]
        );
    }
    public static function checkIsConfirmedCourseSubscriber($db,int $subscriberId,int $courseId){
        $out=$db->exec(
            "SELECT `is_confirmed` FROM `course_subscriber` WHERE `course_id`=:courseId AND `subscriber_id`=:subscriberId",
            [
                'subscriberId'=>$subscriberId,
                ':courseId'=>$courseId
            ]
        );
        if(count($out)>0){
            return (bool)$out[0]['is_confirmed'];
        }
        return false;
    }
    public static function checkIsConfirmedSubscriber($db,int $subscriberId,int $authorId){
        $out=$db->exec(
            "SELECT 1 FROM `subscriber` WHERE `author_id`=:authorId AND `subscriber_id`=:subscriberId",
            [
                'subscriberId'=>$subscriberId,
                ':authorId'=>$authorId
            ]
        );
        if(count($out)>0){
            return true;
        }
        return false;
    }
    public static function unsubscribeToCourse($db,int $subscriberId,int $courseId)
    {
        return $db->exec(
            "DELETE FROM `course_subscriber` WHERE `course_id`=:courseId AND `subscriber_id`=:subscriberId",
            [
                'subscriberId'=>$subscriberId,
                ':courseId'=>$courseId
            ]
        );
    }
            
    /**
     * Получить список заявок на курс
     */
    public static function getRequestList($db, $searchWords, $pageNum=0, $courseId=0)
    {
        $limit=$pageNum!==0?'LIMIT '.($pageNum*10-10).',10':'';
        //Поиск
        $where='1';
        $ws=[];
        if($searchWords[0]!=''){
            $tmp=array_fill(0,2,[]); $i=0;
            // Поиск среди авторов
            foreach($searchWords as $word){
                $tmp[0][]="ss.`name` LIKE :word$i";
                $tmp[1][]="cs.`created` LIKE :word$i";
                $ws[":word$i"]="%$word%";
                $i++;
            }
            $where=implode(' OR ',array_map(function ($v) { return implode(' OR ',$v); },$tmp));
        }

        $where="WHERE ($where) AND cs.`course_id`=:courseId AND cs.`is_confirmed`=0 ";
        $ws[':courseId']=$courseId;

        return $db->exec(
            "SELECT ss.`name`, ss.`id` as 'user_id' ,ss.`ava_url`,cs.`course_id`
            FROM `profile` ss 
            INNER JOIN `course_subscriber` cs ON ss.`id`=cs.`subscriber_id`
            $where
            ORDER BY ss.`name` DESC
            $limit",
            $ws
        );
    }
    public static function getRequestCount(\DB\SQL &$db,array $where,int $courseId=0) :int
    {
        if($courseId!=0){
            $where['where']='cs.course_id=:courseId AND('.$where['where'].')';
            $where['ws'][':courseId']=$courseId;
        }
        return $db->exec(
            "SELECT COUNT(*) as 'r_count'
            FROM `profile` ss 
            INNER JOIN `course_subscriber` cs ON ss.`id`=cs.`subscriber_id`
            WHERE cs.is_confirmed=0 AND(".$where['where'].")
            ORDER BY ss.`name` DESC
            ",
            $where['ws']
        )[0]['r_count'];    
    }
    /**
     * Получить список участников курса (отличается от getRequestList() только `is_confirmed`=1 в WHERE секции)
     */
    public static function getCourseSubscribesList($db, $searchWords, $pageNum=0, $courseId=0)
    {
        $limit=$pageNum!==0?'LIMIT '.($pageNum*10-10).',10':'';
        //Поиск
        $where='1';
        $ws=[];
        if($searchWords[0]!=''){
            $tmp=array_fill(0,2,[]); $i=0;
            // Поиск среди авторов
            foreach($searchWords as $word){
                $tmp[0][]="ss.`name` LIKE :word$i";
                $tmp[1][]="cs.`created` LIKE :word$i";
                $ws[":word$i"]="%$word%";
                $i++;
            }
            $where=implode(' OR ',array_map(function ($v) { return implode(' OR ',$v); },$tmp));
        }

        $where="WHERE ($where) AND cs.`course_id`=:courseId AND cs.`is_confirmed`=1 ";
        $ws[':courseId']=$courseId;

        return $db->exec(
            "SELECT ss.`name`, ss.`id` as 'user_id' ,ss.`ava_url`,cs.`course_id`
            FROM `profile` ss 
            INNER JOIN `course_subscriber` cs ON ss.`id`=cs.`subscriber_id`
            $where
            ORDER BY ss.`name` DESC
            $limit",
            $ws
        );
    }
    public static function getCourseSubscribesCount($db, $where, $courseId=0):int
    {
        return $db->exec(
            "SELECT COUNT(*) as 'cs_count'
            FROM `profile` ss 
            INNER JOIN `course_subscriber` cs ON ss.`id`=cs.`subscriber_id`
            WHERE cs.`course_id`=:courseId AND cs.`is_confirmed`=1 AND (".$where['where'].")",
            array_merge($where['ws'],[':courseId'=>$courseId])
        )[0]['cs_count'];
    }
    public static function confimRqst($db,int $subscriberId,int $courseId)
    {
        return $db->exec(
            "UPDATE `course_subscriber` SET 
            `is_confirmed`=1 
            WHERE `course_id`=:courseId AND `subscriber_id`=:subscriberId",
            [
                ':courseId'=>$courseId,
                'subscriberId'=>$subscriberId
            ]
        );
    }
}

?>