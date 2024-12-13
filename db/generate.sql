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
    test_id integer NOT NULL,
    date timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
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
    start timestamp with time zone NOT NULL,
    "end" timestamp with time zone NOT NULL,
    link character(32) NOT NULL
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
-- Name: test_question; Type: TABLE; Schema: public; Owner: db_course
--

CREATE TABLE public.test_question (
    test_id integer,
    question_id integer
);


ALTER TABLE public.test_question OWNER TO db_course;

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
-- Data for Name: answer; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.answer (id, question_id, text, price, fine) FROM stdin;
\.


--
-- Data for Name: question; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.question (id, title, text, is_open, is_vid_hidden) FROM stdin;
\.


--
-- Data for Name: question_file; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.question_file (q_id, file_name, mime) FROM stdin;
\.


--
-- Data for Name: result; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.result (id, s_a_id, test_id, date, status, sum) FROM stdin;
\.


--
-- Data for Name: s_a; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.s_a (id, login, pass, name, access, created) FROM stdin;
\.


--
-- Data for Name: saved_answer; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.saved_answer (id, res_id, question_id, answer_id, descriptor) FROM stdin;
\.


--
-- Data for Name: test; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.test (id, s_a_id, title, description, "limit", start, "end", link) FROM stdin;
\.


--
-- Data for Name: test_question; Type: TABLE DATA; Schema: public; Owner: db_course
--

COPY public.test_question (test_id, question_id) FROM stdin;
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

SELECT pg_catalog.setval('public.result_id_seq', 1, false);


--
-- Name: s_a_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.s_a_id_seq', 1, false);


--
-- Name: saved_answer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.saved_answer_id_seq', 1, false);


--
-- Name: test_id_seq; Type: SEQUENCE SET; Schema: public; Owner: db_course
--

SELECT pg_catalog.setval('public.test_id_seq', 1, false);


--
-- Name: answer answer_pk; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.answer
    ADD CONSTRAINT answer_pk PRIMARY KEY (id);


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
-- Name: test test_link_key; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.test
    ADD CONSTRAINT test_link_key UNIQUE (link);


--
-- Name: test test_pkey; Type: CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.test
    ADD CONSTRAINT test_pkey PRIMARY KEY (id);


--
-- Name: saved_answer ansver_saved; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.saved_answer
    ADD CONSTRAINT ansver_saved FOREIGN KEY (answer_id) REFERENCES public.answer(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: test_question q_tq; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.test_question
    ADD CONSTRAINT q_tq FOREIGN KEY (question_id) REFERENCES public.question(id) ON UPDATE CASCADE ON DELETE SET NULL;


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
-- Name: test_question t_tq; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.test_question
    ADD CONSTRAINT t_tq FOREIGN KEY (test_id) REFERENCES public.test(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: result test_result; Type: FK CONSTRAINT; Schema: public; Owner: db_course
--

ALTER TABLE ONLY public.result
    ADD CONSTRAINT test_result FOREIGN KEY (test_id) REFERENCES public.test(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO db_course;


--
-- PostgreSQL database dump complete
--

