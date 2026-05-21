<?php namespace model;

final class CourseProcessor
{
    public $count=0;
    public $pageCount=0;
    public $list=[];
    public $n_ids=[];
    public static array $data=[
        'id'=>'',
        'author_id'=>'',
        'title'=>'',
        'description'=>'',
        'ava_url'=>'course_avas/default_ava.png',
        'is_private'=>'',
        'created'=>''
    ];
        
    

    public static function create($db,$authorId,$title,$tdesc,$ava_url,$is_private)
    {
        return $db->exec(
            "INSERT INTO `course` (`author_id`,`title`, `description`, `ava_url`,`is_private`) 
            VALUES(:authorId,:title,:tdesc,:ava_url,:is_private)",
            [
                ':authorId'=>$authorId,
                ':title'=>$title,
                ':tdesc'=>$tdesc,
                ':ava_url'=>$ava_url,
                ':is_private'=>$is_private
            ]
        );
    }

    public static function get($db,$courseId)
    {
        $out=$db->exec(
            "SELECT 
            *
            FROM `course` 
            WHERE `id`=?
            ",
            [$courseId]
        );
        if(count($out)>0){
            return $out[0];
        }
        return [];
    }
    public static function update($db,$authorId,$title,$tdesc,$ava_url,$is_private,$courseId)
    {
        return $db->exec(
            "UPDATE `course` SET 
            `author_id`=:authorId, 
            `title`=:title, 
            `description`=:tdesc, 
            `ava_url`=:ava_url,
            `is_private`=:is_private 
            WHERE `id`=:courseId",
            [
                ':authorId'=>$authorId,
                ':title'=>$title,
                ':tdesc'=>$tdesc,
                ':ava_url'=>$ava_url,
                ':is_private'=>$is_private,
                ':courseId'=>$courseId
            ]
        );
    }
    public static function delete($db,$courseId)
    {
        return $db->exec(
            "DELETE FROM `course` 
            WHERE `id`=:courseId", 
            [
                ':courseId'=>$courseId,
            ]
        );
    }
}
?>