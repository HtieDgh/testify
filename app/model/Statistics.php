<?php namespace model;
use view\Statistics as ST;
class Statistics
{
    //Сбор Статистики
    public static function getStat(&$db,array $user_ids) : ST
    {
      //Получение кол-ва заметок
        $where=[];
        foreach($user_ids as $u_id){
            $where[]="n.`author_id`=$u_id";
        }
        $where = count($where)>0?implode(' OR ',$where):'1';
        ST::i()->addNoteCount($db->exec("SELECT count(*) as 'note_count' FROM `note` n WHERE $where")[0]['note_count']);
      //Получение кол-ва тестов
      ST::i()->addTestCount( $db->exec("SELECT count(*) as 'test_count' FROM `test` t WHERE t.author_id=?",$user_ids[0])[0]['test_count']);
      
      //Получение кол-ва коментариев
        
        ST::i()->addComCount( $db->exec("SELECT count(*) as 'com_count' FROM `comment` c INNER JOIN `note` n ON c.`note_id`=n.`id` WHERE $where")[0]['com_count'] );

      //Получение кол-ва заметок за последний месяц
        $cur_date=date("Y-m-d");
        $Date = new \DateTime($cur_date);
        $shift = -1;
      // сохраним день
        $day = $Date->format('d');
      // первый день целевого месяца  
        $Date->modify('first day of this month')->modify(($shift > 0 ? '+':'') . $shift . ' months');
      // если наш день больше числа дней в месяце, возьмем последний
        $day = $day > $Date->format('t') ? $Date->format('t') : $day;
        $start_date=$Date->modify('+' . $day-1 . ' days')->format('Y-m-d');

        $q="SELECT count(*) as 'note_count_lm' FROM `note` n
        WHERE n.`created` BETWEEN STR_TO_DATE('$start_date', '%Y-%m-%d') 
        AND STR_TO_DATE('$cur_date', '%Y-%m-%d') AND ($where)";
        
        ST::i()->addNoteCountLm($db->exec($q)[0]['note_count_lm']);

      //Получение кол-ва комментов за последний месяц
        $q="SELECT count(*) as 'com_count_lm' FROM `comment` c
        INNER JOIN `note` n ON c.`note_id`=n.`id`
        WHERE n.`created` BETWEEN STR_TO_DATE('$start_date', '%Y-%m-%d') 
        AND STR_TO_DATE('$cur_date', '%Y-%m-%d') AND($where)";

        ST::i()->addComCountLm($db->exec($q)[0]['com_count_lm']);

      //Получение последней добавленой заметки
        $q="SELECT n.`title` from `note` n WHERE $where order by `created` DESC,`id` DESC LIMIT 0,1 ";
        $res=$db->exec($q);
        ST::i()->addNoteLast(!empty($res)?$res[0]['title']:'');

      //Получение обсуждаемой заметки 
        $q="SELECT n.`title` FROM `comment` c
        INNER JOIN `note` n on c.`note_id`=n.`id`
        WHERE $where
        ORDER BY COUNT(c.`note_id`) DESC LIMIT 0,1";
        $res=$db->exec($q);
        ST::i()->addNoteMc(!empty($res)?$res[0]['title']:'');
        
      //Получение общего кол-ва просмотров 
        $q="SELECT SUM(`views`) as 'sum' FROM `note` n WHERE $where";
        $res=$db->exec($q);
        ST::i()->addNoteSv($res[0]['sum']);

      //Получение кол-ва просмотров за последний месяц 
        $q="SELECT SUM(`views`) as 'sum' FROM `note` n
         WHERE n.`created` BETWEEN STR_TO_DATE('$start_date', '%Y-%m-%d') 
         AND STR_TO_DATE('$cur_date', '%Y-%m-%d') AND($where)";
        ST::i()->addNoteSvLm($db->exec($q)[0]['sum']);

      //Самая просматриваемая заметка 
        $q="SELECT n.`title` FROM `note` n
        WHERE $where
        ORDER BY n.`views` DESC LIMIT 0,1";
        $res=$db->exec($q);
        ST::i()->addNoteMv( !empty($res)?$res[0]['title']:'' );


      //Самая просматриваемая заметка за последний месяц
        $q="SELECT n.`title` FROM `note` n
        WHERE n.`created` BETWEEN STR_TO_DATE('$start_date', '%Y-%m-%d') 
         AND STR_TO_DATE('$cur_date', '%Y-%m-%d') AND($where)
        ORDER BY n.`views` DESC, n.`id` DESC LIMIT 0,1";
        $res=$db->exec($q);
        ST::i()->addNoteMvLm(!empty($res)?$res[0]['title']:'' );

        return ST::i();
    }
    //Расширеная версия статистики
    public static function getAdminStat(&$db,array $user_ids) : ST
    {
        $q="SELECT count(*) as 'u_c' FROM `profile`";
        ST::i()->addUserCount($db->exec($q)[0]['u_c']);

        $q="SELECT count(*) as 'u_a_c' FROM `author`";
        ST::i()->addUserAuthorCount($db->exec($q)[0]['u_a_c']);

        return static::getStat( $db, $user_ids );
    }
}
?>