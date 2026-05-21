<?php namespace model;
final class Comment
{
    public static function showCurComments($db,$noteId)
    {
        return $db->exec(
            "SELECT c.`id`,c.`created`,c.`author_id`,c.`text`,p.`name`,p.`ava_url`
            FROM `comment` as c 
            INNER JOIN `profile` as p on c.`author_id`=p.`id`
            WHERE c.`note_id`=:noteid
            ORDER BY c.`created`,c.`id`
            ",
            [':noteid'=>$noteId]
        );
    }
    public static function newComment($db, $noteId, $authorId, $text)
    {
        return $db->exec(
            "INSERT INTO `comment`(`note_id`,`author_id`,`text`)VALUES(:noteId,:authorId,:txt)",
            [
                ':noteId'=>$noteId,
                ':authorId'=>$authorId,
                ':txt'=>$text
            ]
        );
    }
    public static function getNoteAuthorId($db, $commentId)
    {
        $out=$db->exec(
        "SELECT n.`author_id` as 'note_author_id', c.`author_id` as 'comment_author_id'
        FROM `comment` c 
        INNER JOIN `note` n ON c.`note_id`=n.`id` 
        WHERE c.`id`=:commentId",
        [
            ':commentId'=>$commentId
        ]
        );
        if(count($out)>0){
            return $out[0];
        }
        return null;
    }
    public static function delete($db, $commentId)
    {
        return $db->exec(
            "DELETE FROM `comment` WHERE `id`=:commentId",
            [':commentId'=>$commentId]
        );
    }
}
?>