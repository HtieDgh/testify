<?php namespace view;

use view\ViewAbstract;
use Stringable;

class Statistics extends ViewAbstract implements Stringable
{
    protected static $i;
    public $note_count='';
    public $com_count='';
    public $note_count_lm='';
    public $com_count_lm='';
    public $note_last='';
    public $note_mc='';
    public $note_sv='';
    public $note_sv_lm='';
    public $note_mv='';
    public $note_mv_lm='';
    public $user_author_count='';
    public $user_count='';
    public $test_count=0;

    public static function i(): static {
        if (!(static::$i instanceof static)) {
            static::$i = new static();
        }
        return static::$i;
    }
    
    //сеттеры для свойств
    public function addNoteCount($v):static
	{
		$this->note_count =$v; return $this;
	}
    public function addComCount($v):static
	{
		$this->com_count =$v; return $this;
	}
    public function addNoteCountLm($v):static
	{
		$this->note_count_lm =$v; return $this;
	}
    public function addComCountLm($v):static
	{
		$this->com_count_lm=$v; return $this;
	}
    public function addNoteLast($v):static
	{
		$this->note_last =$v; return $this;
	}
    public function addNoteMc($v):static
	{
		$this->note_mc =$v; return $this;
	}
    public function addNoteSvLm($v):static
	{
		$this->note_sv_lm =$v; return $this;
	}
    public function addNoteMv($v):static
	{
		$this->note_mv =$v; return $this;
	}
    public function addNoteSv($v):static
	{
		$this->note_sv =$v; return $this;
	}
    public function addNoteMvLm($v):static
	{
		$this->note_mv_lm =$v; return $this;
	}
    public function addUserAuthorCount($v):static
	{
		$this->user_author_count =$v; return $this;
	}
    public function addUserCount($v):static
	{
		$this->user_count =$v; return $this;
	}
    public function addTestCount($v):static
	{
		$this->test_count =$v; return $this;
	}

    public function __toString(): string
    {
        return '
            <p><span class="good_txt italyc">Статистика:</span><br><br>
            Вами сделано: записи: '.$this->note_count.', тесты: '.$this->test_count.'<br><br>
            Оставлено коментариев под вашими записями:'.$this->com_count.'<br><br>
            За последний месяц вами создано: записи ('.$this->note_count_lm.')<br><br>
            Кол-во комментариев за последний месяц: '.$this->com_count_lm.'<br><br>
            Последняя запись: <span class="italyc">'.$this->note_last.'</span><br><br>
            Ваша самая обсуждаемая запись: <span class="italyc">'.$this->note_mc.'</span><br><br>
            Общее кол-во ваших просмотров: '.$this->note_sv.'<br><br>
            Среди них за последний месяц: '.$this->note_sv_lm.'<br><br>
            Ваша самая просматриваемая запись: '.$this->note_mv.'<br><br>
            Ваша самая просматриваемая запись за последний месяц: '.$this->note_mv_lm.'
            '.($this->user_count!=''?'<br><br>Кол-во пользователей: '.$this->user_count.'<br><br>':'').'
            '.($this->user_author_count!=''?'Среди них авторов: '.$this->user_author_count:'').'
            </p>
        ';
    }
}
?>