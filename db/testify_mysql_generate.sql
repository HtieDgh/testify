-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 21 2026 г., 13:14
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `testify`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin`
--

CREATE TABLE `admin` (
  `id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `admin`
--

INSERT INTO `admin` (`id`) VALUES
(1),
(15);

-- --------------------------------------------------------

--
-- Структура таблицы `answer`
--

CREATE TABLE `answer` (
  `id` int(11) UNSIGNED NOT NULL,
  `question_id` int(11) UNSIGNED NOT NULL,
  `text` varchar(256) NOT NULL,
  `price` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `answer`
--

INSERT INTO `answer` (`id`, `question_id`, `text`, `price`) VALUES
(1, 1, 'первый', -1),
(2, 1, 'второйй', 1),
(3, 2, 'Первый', -1),
(4, 2, 'Второй', -1),
(5, 2, 'Третий', 1),
(6, 3, 'unset', 1),
(7, 4, 'script language=\\\"php\\\"', -1),
(8, 4, '?php ...?', 1),
(9, 4, '& php ... &', -1),
(10, 4, '{% php ... %}', -1),
(11, 5, 'print_line(\'Привет, мир!\');', -1),
(12, 5, 'echo \'Привет, мир!\';', 1),
(13, 5, 'console.log(\'Привет, мир!\');', 0),
(14, 5, 'System.out.println(\'Привет, мир!\');', -1),
(15, 6, '+', -1),
(16, 6, '*', -1),
(17, 6, '.', 1),
(18, 6, ',', -1),
(19, 7, '15', 1),
(20, 8, '==', -1),
(21, 8, '=', -1),
(22, 8, '===', 1),
(23, 8, '!=', -1),
(24, 9, 'Сортирует массив', -1),
(25, 9, 'Возвращает количество элементов в массиве', 1),
(26, 9, 'Удаляет последний элемент массива', -1),
(27, 9, 'Переворачивает массив задом наперёд', -1),
(28, 10, 'A больше B', 1),
(29, 10, 'B больше или равно A', -1),
(30, 10, 'Будет ошибка синтаксиса', -1),
(31, 10, 'Ничего не выведется', -1),
(32, 11, 'B', 1),
(33, 11, 'A', -1),
(34, 11, 'C', -1),
(35, 11, 'D', -1),
(36, 13, 'энцелад', 0),
(37, 14, 'Храм Василия Блаженного', 1),
(38, 14, 'Эрмитаж', -1),
(39, 14, 'Исаакиевский собор', -1),
(40, 14, 'Храм Христа Спасителя', 1),
(41, 15, 'ванесса', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `author`
--

CREATE TABLE `author` (
  `id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `author`
--

INSERT INTO `author` (`id`) VALUES
(1),
(3),
(15),
(16);

-- --------------------------------------------------------

--
-- Структура таблицы `comment`
--

CREATE TABLE `comment` (
  `id` int(11) UNSIGNED NOT NULL,
  `note_id` int(11) UNSIGNED NOT NULL,
  `author_id` int(11) UNSIGNED NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `text` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `comment`
--

INSERT INTO `comment` (`id`, `note_id`, `author_id`, `created`, `text`) VALUES
(8, 20, 6, '2026-05-20 13:13:20', 'Очень понравился урок'),
(9, 20, 9, '2026-05-20 13:15:10', 'А как это по \"спирали\"?'),
(10, 22, 2, '2026-05-20 18:35:00', 'Спасибо, возьму'),
(11, 22, 6, '2026-05-20 18:34:36', 'Готово');

-- --------------------------------------------------------

--
-- Структура таблицы `course`
--

CREATE TABLE `course` (
  `id` int(11) UNSIGNED NOT NULL,
  `author_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `ava_url` varchar(250) NOT NULL DEFAULT 'course_avas/default_ava.png',
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `course`
--

INSERT INTO `course` (`id`, `author_id`, `title`, `description`, `ava_url`, `is_private`, `created`) VALUES
(2, 1, 'Первый', 'gg', 'course_avas/c_id_b26468e311abd863ff9ceca187e34997.png', 1, '2026-05-06 18:05:35'),
(3, 1, 'Второй', 'Администраторский курс', 'course_avas/c_id_.jpg', 1, '2026-05-08 14:58:48'),
(4, 1, 'ww', 'ww', 'course_avas/default_ava.png', 0, '2026-05-08 15:03:59'),
(6, 1, 'gg', 'weqewweq<br>weed<br>weed<br>weeed<br>weed<br>weeed<br>weeed', 'course_avas/default_ava.png', 0, '2026-05-08 15:07:27'),
(9, 3, 'Поделки из ниток', 'На этом курсе ваш ребёнок учится делать 4 поделки своими руками. Это развивает мелкую моторику, воображение, терпение и умение доводить дело до конца. Пожалуйста, помогите ему подготовить материалы и, если получится, выделите 30-40 минут дома для доделки или повторения', 'course_avas/c_id_eac37ab0baa3714f7427f0698c725771.jpg', 0, '2026-05-20 11:52:44'),
(10, 3, 'Пейзаж', 'В данном курсе будет проведен ряд практических занятий (пленэр) для тех кто подаст заявку на данный курс', 'course_avas/c_id_4fb913c9e30648d1ddc027a67b7ffd1d.jpg', 1, '2026-05-20 12:43:07');

-- --------------------------------------------------------

--
-- Структура таблицы `course_subscriber`
--

CREATE TABLE `course_subscriber` (
  `subscriber_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `course_subscriber`
--

INSERT INTO `course_subscriber` (`subscriber_id`, `course_id`, `is_confirmed`, `created`) VALUES
(2, 3, 0, '2026-05-09 00:00:00'),
(2, 10, 1, '2026-05-20 17:05:38'),
(6, 3, 0, '2026-05-16 00:00:00'),
(6, 9, 1, '2026-05-20 14:54:03'),
(6, 10, 1, '2026-05-20 12:54:14'),
(7, 3, 0, '2026-05-16 00:00:00'),
(7, 10, 0, '2026-05-20 12:54:33'),
(8, 10, 1, '2026-05-20 12:54:46'),
(9, 9, 1, '2026-05-20 12:55:00'),
(9, 10, 0, '2026-05-20 12:55:01'),
(10, 10, 1, '2026-05-20 12:55:16'),
(11, 9, 1, '2026-05-20 12:55:35'),
(11, 10, 1, '2026-05-20 12:55:34');

-- --------------------------------------------------------

--
-- Структура таблицы `note`
--

CREATE TABLE `note` (
  `id` int(11) UNSIGNED NOT NULL,
  `author_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `views` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(100) DEFAULT NULL,
  `article` varchar(16000) DEFAULT NULL,
  `tags` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `note`
--

INSERT INTO `note` (`id`, `author_id`, `course_id`, `created`, `views`, `title`, `article`, `tags`) VALUES
(1, 1, NULL, '2026-02-02 12:39:56', 2, 'Начальная тестовая запись!', '[b]Lorem ipsum dolor[/b] sit amet, consectetur adipiscing elit. Vestibulum varius lorem ut ante consequat semper. Mauris laoreet tortor sit amet lorem hendrerit aliquet. Aliquam justo lectus, rhoncus sit amet justo a, consectetur vestibulum erat. Maecenas faucibus tincidunt justo, sed venenatis tortor ullamcorper sed. Nunc non consequat libero, sit amet efficitur sem. Duis aliquam eget dui a aliquam. In hac habitasse platea dictumst. Maecenas sed turpis velit.<br><br>Integer dapibus enim eu tellus euismod consequat. Cras eget quam quis nibh efficitur blandit sed et mauris. Etiam at nunc lorem. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ultricies dolor id ex fringilla porttitor. Praesent viverra mollis erat. Integer finibus mi metus, at maximus tellus mattis pretium.<br><br>Suspendisse efficitur purus nisl, eget suscipit est varius vel. Morbi eu ante hendrerit, egestas nulla ac, finibus libero. Donec id commodo urna, placerat ultricies erat. Fusce lobortis facilisis blandit. Integer eros quam, malesuada ut diam eget, ultricies finibus dui. Praesent mollis diam non metus tempus, non vehicula mi feugiat. Vestibulum rhoncus ligula in mattis euismod. Suspendisse imperdiet erat ac turpis rutrum rhoncus.<br><br>Nunc convallis iaculis risus eget congue. Duis ut lobortis ligula. Integer vitae augue nec massa pretium dapibus. Pellentesque laoreet massa non odio placerat pellentesque. Phasellus dictum, lectus eu ullamcorper condimentum, felis enim blandit mauris, at rutrum diam risus a justo. Fusce dapibus sapien tortor, nec pulvinar diam tempor nec. Nam id leo mattis mi suscipit ultricies. Donec placerat maximus tellus, sit amet facilisis felis aliquet eget. Donec leo tellus, accumsan et posuere non, vestibulum id ex. Vivamus sit amet nibh sit amet nibh ullamcorper pellentesque. Proin lacinia neque augue, non iaculis ex vehicula ac. Donec in est in felis tristique ornare eget ut dolor. Nulla non est pulvinar, feugiat felis sed, volutpat ex.<br><br>Ut ut purus vitae dui ultricies sodales quis ut elit. Maecenas eleifend ligula sit amet est aliquet, nec cursus mi cursus. Aliquam sit amet metus congue, egestas nunc id, efficitur nisl. Proin ullamcorper neque ipsum, a laoreet felis tristique at. Fusce ultricies dapibus erat, quis condimentum lorem blandit quis. Etiam finibus eros tortor, a facilisis sapien fringilla sed. Integer porta dignissim magna vel lobortis. Integer sit amet turpis sit amet nulla luctus placerat. Praesent vel viverra ligula, ac aliquam nibh.', 'deletethis'),
(3, 1, 2, '2026-04-30 00:00:00', 7, 'Тест: Встреча философского клуба', 'Тестирование вставки изображения, которое уже было удалено<br>[img]/course-project-2024-4243/user_data/f3283b2982a98cf7825c5230459725f0/imgs/xHBE-KlEvUq75_etkjfu3_geH_aATBwA4ODUlWDlohsGWroEOSllyKmupnEB2mar_tAStYa66TanSFC4gfmdygCK.jpg[/img]', 'deletethis кикики'),
(7, 1, 2, '2026-05-06 00:00:00', 9, 'проверка', 'следующая по счету заметка<br>[img]/course-project-2024-4243/user_data/f3283b2982a98cf7825c5230459725f0/imgs/wpTildacvprofile.jpg[/img]', 'deletethis'),
(8, 1, NULL, '2026-05-15 00:00:00', 9, 'Проверка (1)', 'одна из кучи записей', 'deletethis'),
(9, 1, NULL, '2026-05-15 00:00:00', 9, 'Проверка (2)', 'Одна из кучи записей', 'deletethis'),
(10, 1, NULL, '2026-05-15 00:00:00', 12, 'Проверка (3)', 'Одна из кучи записей', 'deletethis'),
(11, 1, NULL, '2026-05-15 00:00:00', 5, 'Проверка (4)', 'Одна из кучи записей', 'deletethis'),
(12, 1, NULL, '2026-05-15 00:00:00', 11, 'Проверка (5)', 'Одна из кучи записей', 'deletethis'),
(13, 1, NULL, '2026-05-15 00:00:00', 11, 'Проверка (6)', 'Одна из кучи записей', 'deletethis'),
(14, 1, NULL, '2026-05-15 00:00:00', 11, 'Проверка (7)', 'Одна из кучи записей', 'deletethis'),
(15, 1, NULL, '2026-05-15 00:00:00', 11, 'Проверка (8)', 'Одна из кучи записей', 'deletethis'),
(17, 1, NULL, '2026-05-16 00:00:00', 11, 'Проверка (9)', 'Одна из кучи заметок, даже есть картинка:<br>[img]/course-project-2024-4243/user_data/f3283b2982a98cf7825c5230459725f0/imgs/wpTildacvprofile.jpg[/img]', 'deletethis'),
(18, 3, 9, '2026-04-20 00:00:00', 2, 'Урок 1. &quot;Весёлые нитяные истории&quot;', '[b]Цель:[/b] Научиться наматывать, резать и связывать нитки, получая объёмные формы.<br><br>[b]Материалы:[/b] Пряжа разных цветов, ножницы, картон (для шаблона), вилка или специальное приспособление для помпонов, клей ПВА.<br><br>Для этого нам понадобится:<br><br>1. Вырезать из картона два одинаковых кольца (диаметр 5-7 см).<br>2. Наложить кольца друг на друга, плотно обмотать ниткой в несколько слоёв.<br>3. Разрезать нитки по внешнему краю между кольцами, стянуть и завязать прочную нить между картонками.<br>4. Снять картон, распушить помпон.<br>5. На вилку намотать нитки (20-30 витков)<br>6. Перевязать посередине, края разрезать и подровнять.<br><br>[img]/course-project-2024-4243/user_data/8e6c03efc4adc7c41e7e865763410355/imgs/поделки из ниток ур1jpg.jpg[/img]', 'нитки дпи своими_руками'),
(19, 3, 9, '2026-04-20 00:00:00', 5, 'Урок 2. \"Лоскутная мозаика\"', '[b]Цель:[/b] Освоить технику аппликации из ткани на картонной основе, научиться аккуратно вырезать и приклеивать детали.<br><br>[b]Материалы:[/b] Лоскуты хлопка, фетра, джинсы, ножницы, клей-карандаш или ПВА с кисточкой, картон, простой карандаш, пуговицы, тесьма.<br><br>1. На картоне нарисовать простую фигуру (котик, домик, рыбка). Разрезать эскиз на крупные части (туловище, голова, крыша, стены).<br><br>2. Приложить картонные детали к изнанке ткани, обвести с припуском 0,5 см.<br><br>3. Вырезать тканевые детали.<br><br>4. Приклеить каждую деталь на картон (мажем картон, а не ткань!).<br><br>[img]/course-project-2024-4243/user_data/8e6c03efc4adc7c41e7e865763410355/imgs/поделки из ниток ур2.jpg[/img]', 'нити сделай_сам дпи'),
(20, 3, 9, '2026-05-20 00:00:00', 8, 'Урок 3. &quot;Вторая жизнь коробки и бутылки&quot;', '[b]Цель:[/b] Научиться комбинировать бросовые материалы с нитками и тканью, придавая им полезную и красивую форму.<br><br>[b]Материалы:[/b] Пластиковая бутылка (0,5 л) или картонная коробка (например, из-под чая), клей &quot;Титан&quot;/горячий клей (под контролем учителя), шпагат/джут, лоскуты мешковины или яркой ткани, ножницы, декор (пуговицы, ракушки, кусочки CD-дисков).<br><br>1. У бутылки отрезать верхушку с горлышком (получится стаканчик для ручек).<br>2. У коробки вырезать одну широкую грань (органайзер с ячейками).<br>3. Нанести клей полосками по спирали на основу.<br>4. Плотно прижимать шпагат, виток к витку. Для углов - использовать клей-карандаш как временную фиксацию.<br>5. Если шпагат надоел - обклеить лоскутами ткани в стиле пэчворк.<br>6. Приклеить внутри дно из фетра. Снаружи - пуговицы или нашить бусины (через готовые дырочки продевать нитку).<br><br>[img]/course-project-2024-4243/user_data/8e6c03efc4adc7c41e7e865763410355/imgs/поделки из ниток ур3.jpg[/img]', 'нитки пди своими_руками'),
(21, 3, 9, '2026-05-20 00:00:00', 8, 'Урок 4. &quot;Мягкая игрушка-подвеска&quot;', '[b]Цель:[/b] Сшить простейшую объёмную игрушку швом &quot;через край&quot;, добавив нитяные детали.<br><br>[b]Материалы:[/b] Два одинаковых круга из фетра (или любой несыпучей ткани), синтепон или вата, толстая игла с большим ушком, контрастные нитки мулине, ножницы, ленточка для подвеса.<br><br>1. Вдеть нитку (мулине в 2 сложения), завязать узелок.<br>2. Сложить два круга фетра изнанкой внутрь.<br>3. Начать обмёточный шов от метки &quot;12 часов&quot; до &quot;6 часов&quot;, оставив отверстие.<br>4. Через отверстие набить синтепон (не слишком плотно, чтобы игла проходила).<br>5. Зашить отверстие тем же швом, в верхней точке закрепить ленточку-петлю.<br>6. Отдельной ниткой вышить личико (глаза-точки, рот-галочка).<br>7. Из остатков пряжи сделать чёлку или хвостик (протянуть пучок ниток через край шва и завязать).<br><br>Игрушка превращается в брелок для рюкзака или ёлочную игрушку.<br><br>[img]/course-project-2024-4243/user_data/8e6c03efc4adc7c41e7e865763410355/imgs/поделки из ниток ур4.jpg[/img]', 'нитки пди своими_руками'),
(22, 3, 10, '2026-05-20 00:00:00', 9, 'Пленэр', 'Уважаемые художники!<br>Пожалуйста, убедитесь, что Вы собрали всё необходимое. Проверьте каждый пункт - это сэкономит Вам нервы и время на месте.<br><br>1. Акварель: краски, кисти (белка / синтетика №6, 12, 18), планшет для бумаги, малярный скотч<br>2. Предусмотрите ёмкость для воды (лучше две: одна для чистки, вторая для набора)<br>3. Этюдник или складной мольберт (если работаете стоя)<br>4. Складной стул / туристическое сиденье (опционально)<br>5. Тряпки / бумажные полотенца (много!) - вытирать кисти, руки, случайные кляксы. Положите отдельный пакет для грязных тряпок<br>6. Вода питьевая (не менее 1,5 литра)<br>7. Головной убор (кепка, панама, бандана)<br>8. Средство от комаров и клещей', 'сборы пленэр'),
(23, 3, 10, '2026-05-20 00:00:00', 9, 'Свет, воздух и состояние: от наброска к законченной работе', '[b]Аудитория:[/b] Взрослые художники (масло, акрил, пастель, акварель &amp;mdash; любой материал).<br>[b]Длительность:[/b]  5 часов (с 10:00 до 15:00) с двумя перерывами.<br>[b]Место:[/b]  Парк, берег водоёма (разные точки в радиусе 200 м).<br>[b]Цель:[/b]  Перестать копировать натуру, начать видеть тональные и цветовые отношения, сохранить свежесть первого впечатления.<br><br>Примеры создаваемых работ:<br><br>[img]/course-project-2024-4243/user_data/8e6c03efc4adc7c41e7e865763410355/imgs/пейзаж пример3.jpg[/img][img]/course-project-2024-4243/user_data/8e6c03efc4adc7c41e7e865763410355/imgs/пейзаж пример2.jpg[/img][img]/course-project-2024-4243/user_data/8e6c03efc4adc7c41e7e865763410355/imgs/пейзаж пример1.jpg[/img]', 'сборы пленэр акварель'),
(24, 16, NULL, '2026-05-20 00:00:00', 12, 'PHP: путешествие от &quot;Hello World&quot; до фреймворков', '[img]https://i.pinimg.com/originals/21/f5/7f/21f57f07d1e1e9aa99cba8534de24bd7.jpg[/img]<br><br>PHP - это довольно популярный язык программирования. Много лет в интернете можно услышать утверждение, что PHP умирает. Однако язык до сих пор жив и активно используется. Если вы занимаетесь выбором языка для изучения в 2024 году, возможно, вам стоит обратить внимание на другие языки программирования, у языка появилось много достойных и более популярных конкурентов. Но если вы, всё-таки, решились и начали осваивать PHP, то этот материал для вас.<br><br>PHP преимущественно используется для разработки веб-приложений. Если быть точным, то серверной их части, которую обычно называют бэкендом. Это означает, что ваш код будет работать на сервере. Это несколько упрощает вам задачу, поскольку вам нужно заботиться только о том, чтобы ваше приложение работало только с определёнными версиями PHP, которые доступны на вашем сервере, а не адаптировать ваш код под множество версий браузера, чем вынуждены заниматься фронтенд разработчики.<br><br>Но даже не смотря на узкую специализацию языка, его всё-таки можно использовать и в других сферах, хоть и не так эффективно. Например, вполне можно писать приложения для десктопа или просто писать скрипты для командной строки.<br><br>PHP всегда привлекал к себе внимание начинающих за счёт низкого порога входа. Код на PHP прощает многие ошибки, которые не прощают другие языки. Вы можете часами разбираться почему ваше первое приложение не хочет запускаться, если будете писать на Java или Python. В PHP, обычно, проблем у начинающих возникает намного меньше. Это и достоинство и недостаток языка. Вы очень быстро сможете начать разрабатывать свои приложения, но позже, вы можете столкнуться с множеством ошибок в коде по мере роста вашего проекта и вам всё равно придётся углубиться в изучение основ, которые, в тех же Java и Python просто обязательны с первого дня.<br><br>[b]Объектно ориентированный подход[/b]<br><br>Итак. Вы уже написали свой первый &quot;Hello, World&quot; на PHP и, возможно, даже пишите какие-то длинные скрипты, которые проводят некие магические вычисления. Чем больше данных, тем более запутанных становится ваш код.<br><br>Чтобы ваш код был менее запутанным предлагаю пройти эти тесты:<br><br>[url=http://192.168.0.100/course-project-2024-4243/test/c79666d330e3c3b901563114cc93edc1]Вариант 1[/url]<br>[url=http://192.168.0.100/course-project-2024-4243/test/e895a4916b65a5209679a03b13683a3d]Вариант 2[/url]', 'php');

-- --------------------------------------------------------

--
-- Структура таблицы `profile`
--

CREATE TABLE `profile` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(24) NOT NULL DEFAULT '',
  `status` varchar(500) NOT NULL DEFAULT '',
  `ava_url` varchar(250) NOT NULL DEFAULT 'user_avas/default_ava.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `profile`
--

INSERT INTO `profile` (`id`, `name`, `status`, `ava_url`) VALUES
(1, 'Администратор', 'Учетная запись администратора', 'user_avas/u_id_1_20260506160334.jpg'),
(2, 'Тимохин Михаил', 'Учетная запись простого Читателя', 'user_avas/default_ava.png'),
(3, 'Юлия Вячеславовна', 'Художник, педагог ДПИ', 'user_avas/u_id_3_20260520105934.png'),
(6, 'Дмитрий', 'Учетная запись пользователя добавленого из интерфейса', 'user_avas/default_ava.png'),
(7, 'Алексей', 'Учетная запись пользователя добавленного из интерфейса', 'user_avas/default_ava.png'),
(8, 'Константин', 'Учетная запись пользователя добавленного из интерфейса', 'user_avas/default_ava.png'),
(9, 'Егор', 'Учетная запись пользователя добавленного из интерфейса', 'user_avas/default_ava.png'),
(10, 'Антон', 'Учетная запись пользователя добавленного из интерфейса', 'user_avas/default_ava.png'),
(11, 'Илья', 'Учетная запись пользователя добавленого из интерфейса', 'user_avas/default_ava.png'),
(12, 'Влад', 'Учетная запись пользователя добавленного из интерфейса', 'user_avas/default_ava.png'),
(13, 'Екатерина', 'Учетная запись пользователя добавленого из интерфейса', 'user_avas/default_ava.png'),
(15, 'Мария', 'Учетная запись пользователя добавленого из интерфейса', 'user_avas/default_ava.png'),
(16, 'Алексеев Иван', 'Разработчик в сфере баз данных и php проектов', 'user_avas/u_id_16_20260520132333.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `question`
--

CREATE TABLE `question` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(256) NOT NULL,
  `text` varchar(256) NOT NULL,
  `is_vid_hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `question`
--

INSERT INTO `question` (`id`, `title`, `text`, `is_vid_hidden`) VALUES
(1, 'Вопрос', 'Правильный второй', 0),
(2, 'Вопрос', 'Правильный третий', 0),
(3, 'Вопрос', 'Языковая конструкция unset() уничтожает заданные переменные.nnПоведение языковой конструкции unset() внутри функции зависит от типа переменной, которую пытаются удалить.nnПри удалении глобальной переменной внутри функции удалится только локальная переменна', 0),
(4, 'Какой тег используется для встраивания PHP-кода в HTML-файл?', 'Выберете 1 вариант', 0),
(5, 'Как вывести строку \'Привет, мир!\' на экран?', 'Выберете 1 вариант', 0),
(6, 'Каким символом обозначается конкатенация (склеивание) строк в PHP?', 'Выберете 1 вариант', 0),
(7, 'Что выведет следующий код?', 'В качестве ответа принимается \'Ошибка\' (без кавычек) или число. Версия php: 8.2.0+', 0),
(8, 'Какой оператор используется для строгой проверки равенства (сравнение значения И типа)?', '', 0),
(9, 'Что делает функция', '', 0),
(10, 'Каким будет результат работы кода?', '', 0),
(11, 'Какой суперглобальный массив содержит данные, отправленные через HTTP POST?', '', 0),
(13, 'Что изображено на фото?', 'Впишите ответ строчными буквами без пробелов', 0),
(14, 'Каких достопримечательностей не было показано в видео?', '', 0),
(15, 'Послушайте и ответьте на вопрос', 'С кем разговаривает Бабра? Ответ напишите с маленькой буквы без пробелов.', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `question_file`
--

CREATE TABLE `question_file` (
  `question_id` int(11) UNSIGNED NOT NULL,
  `file_name` varchar(256) NOT NULL,
  `mime` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `question_file`
--

INSERT INTO `question_file` (`question_id`, `file_name`, `mime`) VALUES
(7, 'primer_test.png', 'image'),
(9, 'primer_test2.png', 'image'),
(10, 'primer_test3.png', 'image'),
(11, 'primer_test4.png', 'image'),
(1, 'wp2.jpg', 'image'),
(13, 'wp2.jpg', 'image'),
(15, 'проверка_тест_аудио_англ_аудирование_ванеса.mp3', 'audio'),
(14, 'Санкт Петербург с высоты птичьего полета - SUBARU CLUB Saint-Petersburg (720p, h264).mp4', 'video');

-- --------------------------------------------------------

--
-- Структура таблицы `question_open`
--

CREATE TABLE `question_open` (
  `id` int(11) UNSIGNED NOT NULL,
  `fine` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `question_open`
--

INSERT INTO `question_open` (`id`, `fine`) VALUES
(3, -1),
(7, -1),
(13, 0),
(15, -1);

-- --------------------------------------------------------

--
-- Структура таблицы `saved_answer`
--

CREATE TABLE `saved_answer` (
  `try_id` int(11) UNSIGNED NOT NULL,
  `question_id` int(11) UNSIGNED NOT NULL,
  `answer_id` int(11) UNSIGNED NOT NULL,
  `user_input` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `saved_answer`
--

INSERT INTO `saved_answer` (`try_id`, `question_id`, `answer_id`, `user_input`) VALUES
(0, 4, 8, NULL),
(0, 5, 12, NULL),
(0, 6, 17, NULL),
(0, 7, 19, '15'),
(2, 8, 21, NULL),
(2, 9, 24, NULL),
(2, 10, 30, NULL),
(2, 11, 32, NULL),
(3, 4, 9, NULL),
(3, 5, 11, NULL),
(3, 6, 16, NULL),
(3, 7, 19, '15'),
(4, 8, 22, NULL),
(4, 9, 25, NULL),
(4, 10, 28, NULL),
(4, 11, 32, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `secured_account`
--

CREATE TABLE `secured_account` (
  `id` int(11) UNSIGNED NOT NULL,
  `login` varchar(64) DEFAULT NULL,
  `pass` char(64) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `secured_account`
--

INSERT INTO `secured_account` (`id`, `login`, `pass`, `created`) VALUES
(1, 'testa@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-04-30 10:24:53'),
(2, 'testr@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-18 11:23:19'),
(3, 'author@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-04 11:23:42'),
(6, 'testr2@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 13:47:07'),
(7, 'testr3@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 13:48:41'),
(8, 'testr4@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 14:00:40'),
(9, 'testr5@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 14:01:30'),
(10, 'testr6@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 14:07:34'),
(11, 'testr7@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 14:07:57'),
(12, 'testr8@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 14:09:10'),
(13, 'testr9@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 14:09:37'),
(15, 'testr10@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-15 16:03:55'),
(16, 'author2@test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', '2026-05-20 13:22:27');

-- --------------------------------------------------------

--
-- Структура таблицы `subscriber`
--

CREATE TABLE `subscriber` (
  `subscriber_id` int(11) UNSIGNED NOT NULL,
  `author_id` int(11) UNSIGNED NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `subscriber`
--

INSERT INTO `subscriber` (`subscriber_id`, `author_id`, `created`) VALUES
(2, 3, '2026-05-20 16:35:11'),
(3, 1, '2026-05-02 12:32:22'),
(6, 3, '2026-05-20 14:54:03'),
(6, 16, '2026-05-20 14:43:52'),
(9, 16, '2026-05-20 14:57:36'),
(10, 16, '2026-05-20 14:57:22'),
(11, 16, '2026-05-20 14:57:08'),
(12, 16, '2026-05-20 14:56:55'),
(13, 16, '2026-05-20 14:56:39'),
(15, 16, '2026-05-20 14:56:24');

-- --------------------------------------------------------

--
-- Структура таблицы `test`
--

CREATE TABLE `test` (
  `id` int(11) UNSIGNED NOT NULL,
  `author_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(256) NOT NULL,
  `description` varchar(512) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `minimum` smallint(6) UNSIGNED NOT NULL,
  `datetime_start` datetime NOT NULL,
  `datetime_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `test`
--

INSERT INTO `test` (`id`, `author_id`, `title`, `description`, `created`, `minimum`, `datetime_start`, `datetime_end`) VALUES
(2, 3, 'Тест на тему Саморазвитие', 'Необходимо набрать минимум 2 балла', '2026-05-12 12:04:46', 2, '2026-05-12 11:57:00', '2026-05-19 11:57:00'),
(4, 1, 'Тестирование:проверка вставки id', 'нет описания?', '2026-05-19 13:26:14', 1, '2026-05-19 13:25:00', '2026-05-26 13:25:00'),
(5, 16, 'Тест на тему Программирования', 'Необходимо набрать минимум 2 балла', '2026-05-20 13:31:19', 2, '2026-05-20 13:29:00', '2026-05-27 13:29:00'),
(6, 3, 'Проверка создания тестирования', 'Необходимо набрать минимум 2 балла', '2026-05-20 19:46:09', 2, '2026-05-21 19:42:00', '2026-05-27 19:42:00');

-- --------------------------------------------------------

--
-- Структура таблицы `try`
--

CREATE TABLE `try` (
  `id` int(11) UNSIGNED NOT NULL,
  `member_id` int(11) UNSIGNED NOT NULL,
  `variant_id` int(11) UNSIGNED DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `sum` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `try`
--

INSERT INTO `try` (`id`, `member_id`, `variant_id`, `created`, `status`, `sum`) VALUES
(1, 2, 7, '2026-05-20 17:37:37', 1, 4),
(2, 2, 8, '2026-05-20 17:44:20', 0, -2),
(3, 2, 7, '2026-05-20 18:09:46', 0, -2),
(4, 6, 8, '2026-05-21 11:29:17', 1, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `variant`
--

CREATE TABLE `variant` (
  `id` int(11) UNSIGNED NOT NULL,
  `test_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(256) NOT NULL,
  `unique_url` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `variant`
--

INSERT INTO `variant` (`id`, `test_id`, `title`, `unique_url`) VALUES
(3, 2, 'Вариант 1', 'b3971dec69f2319cd8a98d4397b3a116'),
(6, 4, 'Вариант 1', 'aeae6f2e7e99942a7414a70f5f3a5d74'),
(7, 5, 'Вариант 1', 'c79666d330e3c3b901563114cc93edc1'),
(8, 5, 'Вариант 2', 'e895a4916b65a5209679a03b13683a3d'),
(9, 6, 'Простой', '0434770d7c4c49fb236e600ac9b942c6'),
(10, 6, 'Сложный', 'ab9b236cfebe1ce2afc8f82ec6bf872f');

-- --------------------------------------------------------

--
-- Структура таблицы `variant_question`
--

CREATE TABLE `variant_question` (
  `variant_id` int(11) UNSIGNED NOT NULL,
  `question_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `variant_question`
--

INSERT INTO `variant_question` (`variant_id`, `question_id`) VALUES
(3, 1),
(3, 2),
(3, 3),
(7, 4),
(7, 5),
(7, 6),
(7, 7),
(8, 8),
(8, 9),
(8, 10),
(8, 11),
(9, 13),
(9, 14),
(9, 15);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Индексы таблицы `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author` (`author_id`),
  ADD KEY `note_comment` (`note_id`);

--
-- Индексы таблицы `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Индексы таблицы `course_subscriber`
--
ALTER TABLE `course_subscriber`
  ADD PRIMARY KEY (`subscriber_id`,`course_id`),
  ADD KEY `subscriber_id` (`subscriber_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Индексы таблицы `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aut_id` (`author_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Индексы таблицы `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `question_file`
--
ALTER TABLE `question_file`
  ADD PRIMARY KEY (`file_name`,`question_id`) USING BTREE,
  ADD KEY `question_id` (`question_id`);

--
-- Индексы таблицы `question_open`
--
ALTER TABLE `question_open`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Индексы таблицы `saved_answer`
--
ALTER TABLE `saved_answer`
  ADD PRIMARY KEY (`try_id`,`question_id`,`answer_id`),
  ADD KEY `try_id` (`try_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `answer_id` (`answer_id`);

--
-- Индексы таблицы `secured_account`
--
ALTER TABLE `secured_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Индексы таблицы `subscriber`
--
ALTER TABLE `subscriber`
  ADD PRIMARY KEY (`subscriber_id`,`author_id`),
  ADD KEY `subscriber_id` (`subscriber_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Индексы таблицы `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Индексы таблицы `try`
--
ALTER TABLE `try`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Индексы таблицы `variant`
--
ALTER TABLE `variant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_url` (`unique_url`),
  ADD KEY `test_id` (`test_id`);

--
-- Индексы таблицы `variant_question`
--
ALTER TABLE `variant_question`
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `question_id` (`question_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `note`
--
ALTER TABLE `note`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `secured_account`
--
ALTER TABLE `secured_account`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `test`
--
ALTER TABLE `test`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `try`
--
ALTER TABLE `try`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `variant`
--
ALTER TABLE `variant`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `s_a_admin` FOREIGN KEY (`id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `question_answer` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `author`
--
ALTER TABLE `author`
  ADD CONSTRAINT `s_a_author` FOREIGN KEY (`id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `note_comment` FOREIGN KEY (`note_id`) REFERENCES `note` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `profile_comment` FOREIGN KEY (`author_id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `course_subscriber`
--
ALTER TABLE `course_subscriber`
  ADD CONSTRAINT `course_course_subscriber` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_subscriber_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `course_note` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `note_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `s_a_profile` FOREIGN KEY (`id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `question_file`
--
ALTER TABLE `question_file`
  ADD CONSTRAINT `question_question_file` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `question_open`
--
ALTER TABLE `question_open`
  ADD CONSTRAINT `question_question_open` FOREIGN KEY (`id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `saved_answer`
--
ALTER TABLE `saved_answer`
  ADD CONSTRAINT `answer_saved_answer` FOREIGN KEY (`answer_id`) REFERENCES `answer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `question_saved_answer` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `try_saved_answer` FOREIGN KEY (`try_id`) REFERENCES `try` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `subscriber`
--
ALTER TABLE `subscriber`
  ADD CONSTRAINT `subscriber_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subscriber_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `test_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `try`
--
ALTER TABLE `try`
  ADD CONSTRAINT `try_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `secured_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `variant_try` FOREIGN KEY (`variant_id`) REFERENCES `variant` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `variant`
--
ALTER TABLE `variant`
  ADD CONSTRAINT `test_variant` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `variant_question`
--
ALTER TABLE `variant_question`
  ADD CONSTRAINT `question_vq` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `variant_vq` FOREIGN KEY (`variant_id`) REFERENCES `variant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
