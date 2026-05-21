<?php namespace model;

use model\CFuns;

final class NoteProcessor 
{
    public $count=0;
    public $pageCount=0;
    public $notesList=[];
    public $n_ids=[];   //для подсчета кол-ва просмотров
    public static $data=[
        'id'=>'',
        'course_id'=>'',
        'author_id'=>'',
        'views'=>'',
        'title'=>'',
        'article'=>'',
        'tags'=>'',
        'course_title'=>'',
        'course_ava_url'=>'',
        'is_private'=>'',
        'course_created'=>''
    ];

    public function getAll(
        \DB\SQL $db,
        int $authorId=0,
        int $userId=0,
        int $subId=0,
        int $courseId=0,
        int $pageNum=1,
        array $searchWords=[]
    ) : array
    {
        //Пагинация
        $limit=$pageNum!==0?'LIMIT '.($pageNum*10-10).',10':'';
        //Поиск
        $where='1';
        $ws=[];
        if($searchWords[0]!=''){
            $tmp=array_fill(0,7,[]); $i=0;
            
            foreach($searchWords as $word){
                $tmp[0][]=" n.`created` LIKE :word$i";
                $tmp[1][]=" n.`title` LIKE :word$i";
                $tmp[2][]=" n.`article` LIKE :word$i";
                $tmp[3][]=" n.`tags` LIKE :word$i";
                $tmp[4][]=" c.`title` LIKE :word$i";
                $tmp[5][]=" c.`description` LIKE :word$i";
                $tmp[6][]=" p.`name` LIKE :word$i";
                $ws[":word$i"]="%$word%";
                $i++;
            }

            $where=implode(' OR ',array_map(function ($v) { return implode(' AND ',$v); },$tmp));
        }
        $where="($where) AND ((c.`is_private`=0 OR n.`course_id` IS NULL) OR (cs.`subscriber_id`='$userId' AND cs.`is_confirmed`=1) OR c.`author_id`=$userId)";

        if($authorId!==0){
            $where="($where) AND n.`author_id`=$authorId";
        }
        if($subId!==0){
            $where="($where) AND ss.`subscriber_id`=$subId";
        }
        if($courseId!==0){
            $where="($where) AND c.`id`=$courseId";
        }
       
        $q="SELECT DISTINCT 
        n.`id` as 'note_id',
        n.`author_id`,
        n.`views`,
        n.`title`,
        n.`article`,
        n.`tags`,
        p.`name`,
        p.`ava_url`,
        DATE_FORMAT(n.`created`,'%e %M %Y') as 'note_created',
        c.`id` as 'course_id',
        c.`title` as 'course_title',
        c.`description` as 'course_article',
        c.`ava_url` as 'course_ava_url',
        c.`is_private`,
        DATE_FORMAT(c.`created`,'%Y-%m-%d') as 'created',
        (SELECT count(*) FROM `course_subscriber` as cs WHERE cs.`course_id`=c.`id`) as 'subs_count',
        (SELECT count(*) FROM `note` as n WHERE n.course_id=c.id) as 'notes_count',
        (SELECT count(*) FROM `comment` as c WHERE c.note_id=n.id) as 'com_count'
        FROM `note` as n 
            INNER JOIN `profile` as p on n.`author_id`=p.`id` 
            LEFT JOIN `subscriber` as ss on n.`author_id`=ss.`author_id`
            LEFT JOIN `course` as c on n.`course_id`=c.`id`
            LEFT JOIN `course_subscriber` as cs USING(`course_id`)
        WHERE $where 
        ORDER BY n.`created` DESC,n.`id` DESC 
        $limit
    ";
        $db->exec("SET lc_time_names ='ru_ru'");
        $this->notesList = $db->exec($q,$ws);
        $this->n_ids=array_column($this->notesList,'note_id');
        $this->count = $db->exec(
            "SELECT count(DISTINCT n.id) as 'note_count' 
            FROM note as n 
            INNER JOIN `profile` as p on n.`author_id`=p.`id` 
            LEFT JOIN `subscriber` as ss on n.`author_id`=ss.`author_id`
            LEFT JOIN `course` as c on n.`course_id`=c.`id`
            LEFT JOIN `course_subscriber` as cs USING(`course_id`)
            WHERE $where
            ",$ws)[0]['note_count'];
       
        return $this->notesList;
    }

    public static function get($db,$noteId)
    {
        $out = $db->exec(
            "SELECT 
            n.`id`,
            n.`course_id`,
            n.`author_id`,
            n.`created`,
            n.`views`,
            n.`title`,
            n.`article`,
            n.`tags`
            FROM `note` n 
            WHERE n.`id`=?
            ",
            [$noteId]
        );
        if(count($out)>0){
            return $out[0];
        }
        return $out;
    }
    public static function getAuthorId($db,$authorId)
    {
        return $db->exec(
            "SELECT 
            n.`id`,
            n.`course_id`,
            n.`author_id`,
            n.`created`,
            n.`views`,
            n.`title`,
            n.`article`,
            n.`tags`
            FROM `note` n 
            WHERE n.`author_id`=?
            ",
            [$authorId]
        );
    }

    public function getWithCourse($db,$noteId)
    {
        $out = $db->exec(
            "SELECT 
            n.`id`,
            n.`course_id`,
            n.`author_id`,
            n.`created`,
            n.`views`,
            n.`title`,
            n.`article`,
            n.`tags`,
            c.`title` as 'course_title',
            c.`ava_url` as 'course_ava_url',
            c.`is_private`,
            c.`created` as 'course_created'
            FROM `note` n 
            LEFT JOIN `course` c ON n.`course_id`=c.`id`
            WHERE n.`id`=?
            ",
            [$noteId]
        );
        if(count($out)>0){
            return $out[0];
        }
        return $out;
    }

    public function create($db, $course_id,$created,$title,$article,$tags,$author_id) : bool
    {
        return $db->exec(
            "INSERT INTO `note` (`course_id`,`created`, `title`, `article`,`tags`,`author_id`) 
            VALUES(:course_id,:created,:title,:article,:tags,:author_id)",
            [
                ':course_id'=>$course_id,
                ':created'=>$created,
                ':title'=>$title,
                ':article'=>$article,
                ':tags'=>$tags,
                ':author_id'=>$author_id
            ]
        );
    }
    public function update($db, $noteId, $course_id,$title,$article,$tags,$author_id) 
    {
        return $db->exec(
            "UPDATE `note` SET
            `course_id`=:course_id,
            `title`=:title,
            `article`=:article,
            `tags`=:tags,
            `author_id`=:author_id
            WHERE `id`=:noteid", 
            [
                ':noteid'=>$noteId,
                ':course_id'=>$course_id,
                ':title'=>$title,
                ':article'=>$article,
                ':tags'=>$tags,
                ':author_id'=>$author_id
            ]
        );
    }
    public function delete($db,$noteId) 
    {
        return $db->exec(
            "DELETE FROM `note` 
            WHERE `id`=:noteId", 
            [
                ':noteId'=>$noteId,
            ]
        );
    }  
    /**
     * <p>Обновление кол-ва просмотренных записей, с использованием сессии</p>
     *
     * @param  object \DB\SQL $db
     * @return void
     */
    public function updateViews(\DB\SQL &$db){
        //использование $_SESSION для подсчета кол-ва просмотров
        session_start([
            'cookie_lifetime' => 86400,
            'read_and_close'  => true,
        ]);

        //Обновление кол-ва просмотренных записей
        $u_n_ids=isset($_SESSION['vstd_ids'])?$_SESSION['vstd_ids']:[];
        $this->n_ids=array_diff($this->n_ids,$u_n_ids);
        $_SESSION['vstd_ids']=array_merge($u_n_ids,$this->n_ids);
        session_write_close();

        if(count($this->n_ids)>0){
            $where=[];
            foreach($this->n_ids as $u_id){
                $where[]="id=$u_id";
            }
            $where = implode(' OR ',$where);
            $db->exec("UPDATE `note` SET `views`=`views`+1 WHERE $where");
        }

    }
}
?>