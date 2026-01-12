--
-- PostgreSQL database dump
--

-- Dumped from database version 17.0
-- Dumped by pg_dump version 17.0

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: answer; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.answer (
    id integer NOT NULL,
    question_id integer NOT NULL,
    text character varying NOT NULL,
    price smallint NOT NULL,
    fine smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public.answer OWNER TO db_course;

--
-- Name: answer_id_seq; Type: SEQUENCE; Schema: public; Owner: db_course
--

CREATE SEQUENCE public.answer_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.answer_id_seq OWNER TO db_course;

--
-- Name: answer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_course
--

ALTER SEQUENCE public.answer_id_seq OWNED BY public.answer.id;


--
-- Name: question; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.question (
    id integer NOT NULL,
    title character varying NOT NULL,
    text character varying NOT NULL,
    is_open boolean DEFAULT false NOT NULL,
    is_vid_hidden boolean DEFAULT false NOT NULL
);


ALTER TABLE public.question OWNER TO db_course;

--
-- Name: question_file; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.question_file (
    q_id integer NOT NULL,
    file_name character varying NOT NULL,
    mime character varying NOT NULL
);


ALTER TABLE public.question_file OWNER TO db_course;

--
-- Name: question_id_seq; Type: SEQUENCE; Schema: public; Owner: db_course
--

CREATE SEQUENCE public.question_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.question_id_seq OWNER TO db_course;

--
-- Name: question_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_course
--

ALTER SEQUENCE public.question_id_seq OWNED BY public.question.id;


--
-- Name: result; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.result (
    id integer NOT NULL,
    s_a_id integer NOT NULL,
    variant_id integer NOT NULL,
    date timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    status boolean DEFAULT false NOT NULL,
    sum integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.result OWNER TO db_course;

--
-- Name: result_id_seq; Type: SEQUENCE; Schema: public; Owner: db_course
--

CREATE SEQUENCE public.result_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.result_id_seq OWNER TO db_course;

--
-- Name: result_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_course
--

ALTER SEQUENCE public.result_id_seq OWNED BY public.result.id;


--
-- Name: s_a; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.s_a (
    id integer NOT NULL,
    login character varying NOT NULL,
    pass character(32) DEFAULT ''::bpchar NOT NULL,
    name character varying NOT NULL,
    access integer DEFAULT 0 NOT NULL,
    created date DEFAULT CURRENT_DATE NOT NULL
);


ALTER TABLE public.s_a OWNER TO db_course;

--
-- Name: s_a_id_seq; Type: SEQUENCE; Schema: public; Owner: db_course
--

CREATE SEQUENCE public.s_a_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.s_a_id_seq OWNER TO db_course;

--
-- Name: s_a_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_course
--

ALTER SEQUENCE public.s_a_id_seq OWNED BY public.s_a.id;


--
-- Name: saved_answer; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.saved_answer (
    id integer NOT NULL,
    res_id integer NOT NULL,
    question_id integer NOT NULL,
    answer_id integer NOT NULL,
    descriptor character varying
);


ALTER TABLE public.saved_answer OWNER TO db_course;

--
-- Name: saved_answer_id_seq; Type: SEQUENCE; Schema: public; Owner: db_course
--

CREATE SEQUENCE public.saved_answer_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.saved_answer_id_seq OWNER TO db_course;

--
-- Name: saved_answer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_course
--

ALTER SEQUENCE public.saved_answer_id_seq OWNED BY public.saved_answer.id;


--
-- Name: test; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.test (
    id integer NOT NULL,
    s_a_id integer NOT NULL,
    title character varying NOT NULL,
    description character varying NOT NULL,
    "limit" smallint NOT NULL,
    start timestamp without time zone NOT NULL,
    "end" timestamp without time zone NOT NULL
);


ALTER TABLE public.test OWNER TO db_course;

--
-- Name: test_id_seq; Type: SEQUENCE; Schema: public; Owner: db_course
--

CREATE SEQUENCE public.test_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.test_id_seq OWNER TO db_course;

--
-- Name: test_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_course
--

ALTER SEQUENCE public.test_id_seq OWNED BY public.test.id;


--
-- Name: variant; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.variant (
    id integer NOT NULL,
    test_id integer NOT NULL,
    title character varying NOT NULL,
    link character(32)
);


ALTER TABLE public.variant OWNER TO db_course;

--
-- Name: variant_id_seq; Type: SEQUENCE; Schema: public; Owner: db_course
--

CREATE SEQUENCE public.variant_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.variant_id_seq OWNER TO db_course;

--
-- Name: variant_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_course
--

ALTER SEQUENCE public.variant_id_seq OWNED BY public.variant.id;


--
-- Name: variant_question; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.variant_question (
    variant_id integer,
    question_id integer
);


ALTER TABLE public.variant_question OWNER TO db_course;

--
-- Name: answer id; Type: DEFAULT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.answer ALTER COLUMN id SET DEFAULT nextval('public.answer_id_seq'::regclass);


--
-- Name: question id; Type: DEFAULT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.question ALTER COLUMN id SET DEFAULT nextval('public.question_id_seq'::regclass);


--
-- Name: result id; Type: DEFAULT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.result ALTER COLUMN id SET DEFAULT nextval('public.result_id_seq'::regclass);


--
-- Name: s_a id; Type: DEFAULT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.s_a ALTER COLUMN id SET DEFAULT nextval('public.s_a_id_seq'::regclass);


--
-- Name: saved_answer id; Type: DEFAULT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.saved_answer ALTER COLUMN id SET DEFAULT nextval('public.saved_answer_id_seq'::regclass);


--
-- Name: test id; Type: DEFAULT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.test ALTER COLUMN id SET DEFAULT nextval('public.test_id_seq'::regclass);


--
-- Name: variant id; Type: DEFAULT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.variant ALTER COLUMN id SET DEFAULT nextval('public.variant_id_seq'::regclass);


--
-- Data for Name: answer; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.answer (id, question_id, text, price, fine) FROM stdin;
1	1	Э. Тайлор	1	0
2	1	А. Радклифф-Браун	-1	0
3	1	Л. Уайт	-1	0
4	2	специфическими приемами исследования	-1	0
5	2	особенностью класса изучаемых явлений	-1	0
6	2	контекстом, в который помещаются явления при изучении	1	0
7	3	в XIX	-1	0
8	3	в XVIII	-1	0
9	3	в XX	1	0
10	4	Социология К. Маркса	-1	0
11	4	Социология культуры Г. Зиммеля	-1	0
12	4	Критическая теория Т. Адорно	1	0
13	5	Г. Зиммель	1	0
14	5	К. Манхейм	-1	0
15	5	К. Маркс	-1	0
16	6	рассмотрением лишь практического аспекта культуры	-1	0
17	6	объяснением явлений культуры принципами социальной жизни	1	0
18	6	отождествлением культуры и общества	-1	0
19	7	Символ есть разновидность знаков	1	0
20	7	Являются независимыми разновидностями язык	-1	0
21	7	Слова &laquo;символ&raquo; и &laquo;знак&raquo; выражают одно и то же	-1	0
22	8	Р. Барт	-1	0
23	8	Ю. Лотман	1	0
24	8	Ф. де Соссюр	-1	0
25	9	является случаем мирового культурного процесса	-1	0
26	9	сама является интерпретативной системой	1	0
27	9	делится на материальную и духовную	-1	0
28	10	самообучение и самосовершенствование	1	-1
29	11	Ничего из перечисленного	-5	0
30	11	Путешествия	1	0
31	11	Онлайн обучение	1	0
32	11	Общение с экспертами	1	0
33	11	Чтение литературы	1	0
34	11	Игра в видеоигру	10	0
35	12	Да	1	0
36	12	Нет	1	0
37	12	Не могу ответить	0	0
\.


--
-- Data for Name: question; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.question (id, title, text, is_open, is_vid_hidden) FROM stdin;
1	Вопрос	Кто предложил первое антропологическое определение культуры	f	f
2	Вопрос	По мнению Л. Уайта культурология отличается от других наук	f	f
3	Вопрос	В каком веке культурология возникает как самостоятельная область знания?	f	f
4	Вопрос	В какой концепции впервые дается описание капиталистического и классового характера современной культуры?	f	f
5	Вопрос	Кто из социологов стал пионером в исследовании массовой культуры?	f	f
6	Вопрос	Социологическое изучение культуры отличается:	f	f
7	Вопрос	Как соотносятся символ и знак?	f	f
8	Вопрос	Концепцию &laquo;эволюции&raquo; и &laquo;взрыва&raquo; как основных динамических процессов в семиотических системах культуры представил	f	f
9	Вопрос	Культура как символическая система	f	f
10	Продолжите фразу	Саморазвитие - это непрерывное	t	f
11	Выберете все позиции	В которых обозначены подходы к самообразованию	f	f
12	Оценка	Вы удовлетворены тестом?	f	f
\.


--
-- Data for Name: question_file; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.question_file (q_id, file_name, mime) FROM stdin;
12	photo_2024-12-21_14-11-04.jpg	image
\.


--
-- Data for Name: result; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.result (id, s_a_id, variant_id, date, status, sum) FROM stdin;
4	1	4	2024-12-25 11:29:36	t	3
5	1	4	2024-12-25 20:16:35	f	-6
\.


--
-- Data for Name: s_a; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.s_a (id, login, pass, name, access, created) FROM stdin;
1	test@test	098f6bcd4621d373cade4e832627b4f6	Создатель теста	2	2024-06-17
2	testm@test	098f6bcd4621d373cade4e832627b4f6	Михаил	1	2024-07-09
3	testi@test	098f6bcd4621d373cade4e832627b4f6	Иванов	1	2024-07-09
4	testp@test	098f6bcd4621d373cade4e832627b4f6	Петров	1	2024-07-11
5	tests@test	098f6bcd4621d373cade4e832627b4f6	Сидоров	1	2024-07-11
6	test_manager@test	098f6bcd4621d373cade4e832627b4f6	Менеджер	3	2024-11-17
8	testnm@test	098f6bcd4621d373cade4e832627b4f6	Новый Менеджер	3	2024-12-25
9	test@test.test	098f6bcd4621d373cade4e832627b4f6	test	1	2024-12-25
10	test1@test.test	098f6bcd4621d373cade4e832627b4f6	test1	1	2024-12-25
11	fykakoi@yandex.ru	02c425157ecd32f259548b33402ff6d3	Диана	1	2024-12-25
\.


--
-- Data for Name: saved_answer; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.saved_answer (id, res_id, question_id, answer_id, descriptor) FROM stdin;
7	4	10	28	самосовершенствование
8	4	11	30	\N
9	4	11	31	\N
10	4	11	32	\N
11	4	11	33	\N
12	4	12	37	\N
13	5	10	28	верно
14	5	11	29	\N
15	5	12	37	\N
\.


--
-- Data for Name: test; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.test (id, s_a_id, title, description, "limit", start, "end") FROM stdin;
1	1	Тестирование по теме 1: 'Возникновение науки о культуре'	Необходимо ответить верно на 3 вопроса	3	2024-12-25 09:43:00	2025-01-01 09:43:00
2	1	Тестирование на тему Саморазвитие	Вам необходимо правильно ответить на 2 из 3 вопросов	1	2024-12-25 11:23:00	2025-01-01 11:23:00
\.


--
-- Data for Name: variant; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.variant (id, test_id, title, link) FROM stdin;
1	1	Вариант 1	06cdbca6092d611158b330226eb019b0
2	1	Вариант 2	7109209f311a45ef23d710fc18b47572
3	1	Вариант 3	2c0a09b8678b3b16abae902e2b7cb927
4	2	Вариант 1	b3971dec69f2319cd8a98d4397b3a116
\.


--
-- Data for Name: variant_question; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.variant_question (variant_id, question_id) FROM stdin;
1	1
1	2
1	3
2	4
2	5
2	6
3	7
3	8
3	9
4	10
4	11
4	12
\.


--
-- Name: answer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.answer_id_seq', 1, false);


--
-- Name: question_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.question_id_seq', 1, false);


--
-- Name: result_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.result_id_seq', 5, true);


--
-- Name: s_a_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.s_a_id_seq', 11, true);


--
-- Name: saved_answer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.saved_answer_id_seq', 15, true);


--
-- Name: test_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.test_id_seq', 1, false);


--
-- Name: variant_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.variant_id_seq', 1, false);


--
-- Name: answer answer_pk; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.answer
    ADD CONSTRAINT answer_pk PRIMARY KEY (id);


--
-- Name: variant link_unique; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.variant
    ADD CONSTRAINT link_unique UNIQUE (link);


--
-- Name: s_a login_unq; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.s_a
    ADD CONSTRAINT login_unq UNIQUE (login);


--
-- Name: question_file question_file_pk; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.question_file
    ADD CONSTRAINT question_file_pk PRIMARY KEY (q_id, file_name);


--
-- Name: question question_pk; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.question
    ADD CONSTRAINT question_pk PRIMARY KEY (id);


--
-- Name: result result_pk; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.result
    ADD CONSTRAINT result_pk PRIMARY KEY (id);


--
-- Name: s_a s_a_pk; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.s_a
    ADD CONSTRAINT s_a_pk PRIMARY KEY (id);


--
-- Name: saved_answer saved_answer_pkey; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.saved_answer
    ADD CONSTRAINT saved_answer_pkey PRIMARY KEY (id);


--
-- Name: test test_pkey; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.test
    ADD CONSTRAINT test_pkey PRIMARY KEY (id);


--
-- Name: variant variant_pkey; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.variant
    ADD CONSTRAINT variant_pkey PRIMARY KEY (id);


--
-- Name: saved_answer ansver_saved; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.saved_answer
    ADD CONSTRAINT ansver_saved FOREIGN KEY (answer_id) REFERENCES public.answer(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: variant_question q_vq; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.variant_question
    ADD CONSTRAINT q_vq FOREIGN KEY (question_id) REFERENCES public.question(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: answer question_answer; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.answer
    ADD CONSTRAINT question_answer FOREIGN KEY (question_id) REFERENCES public.question(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: question_file question_q_files; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.question_file
    ADD CONSTRAINT question_q_files FOREIGN KEY (q_id) REFERENCES public.question(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: saved_answer question_saved; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.saved_answer
    ADD CONSTRAINT question_saved FOREIGN KEY (question_id) REFERENCES public.question(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: saved_answer result_saved; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.saved_answer
    ADD CONSTRAINT result_saved FOREIGN KEY (res_id) REFERENCES public.result(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: result s_a_result; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.result
    ADD CONSTRAINT s_a_result FOREIGN KEY (s_a_id) REFERENCES public.s_a(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: test s_a_test; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.test
    ADD CONSTRAINT s_a_test FOREIGN KEY (s_a_id) REFERENCES public.s_a(id);


--
-- Name: variant_question v_vq; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.variant_question
    ADD CONSTRAINT v_vq FOREIGN KEY (variant_id) REFERENCES public.variant(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: result variant_result; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.result
    ADD CONSTRAINT variant_result FOREIGN KEY (variant_id) REFERENCES public.variant(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: variant variant_test_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.variant
    ADD CONSTRAINT variant_test_id_fkey FOREIGN KEY (test_id) REFERENCES public.test(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO db_course;


--
-- PostgreSQL database dump complete
--

