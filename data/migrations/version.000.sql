--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.7
-- Dumped by pg_dump version 9.1.7
-- Started on 2013-02-12 17:37:45 KST

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 193 (class 3079 OID 11683)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 3129 (class 0 OID 0)
-- Dependencies: 193
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- TOC entry 194 (class 3079 OID 22643)
-- Dependencies: 6
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- TOC entry 3130 (class 0 OID 0)
-- Dependencies: 194
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


SET search_path = public, pg_catalog;

--
-- TOC entry 1485 (class 1247 OID 23759)
-- Dependencies: 6
-- Name: answerstatus; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE answerstatus AS ENUM (
    'new',
    'completed',
    'validated',
    'rejected'
);


ALTER TYPE public.answerstatus OWNER TO postgres;

--
-- TOC entry 1488 (class 1247 OID 23768)
-- Dependencies: 6
-- Name: questiontype; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE questiontype AS ENUM (
    'choice',
    'numeric',
    'text',
    'user',
    'organisation',
    'chapter'
);


ALTER TYPE public.questiontype OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 174 (class 1259 OID 23781)
-- Dependencies: 3038 3040 3041 3042 1485 6
-- Name: answer; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE answer (
    id integer NOT NULL,
    "valueChoice" smallint,
    "valuePercent" numeric(3,2),
    "valueAbsolute" double precision,
    "valueText" text,
    "valueUser" integer,
    quality numeric(3,2),
    relevance numeric(3,2),
    status answerstatus,
    questionnaire integer NOT NULL,
    question integer,
    "dateCreated" timestamp with time zone DEFAULT now(),
    creator integer,
    "dateModified" timestamp with time zone,
    modifier integer,
    CONSTRAINT answer_quality_check CHECK (((quality >= (0)::numeric) AND (quality <= (1)::numeric))),
    CONSTRAINT answer_relevance_check CHECK (((relevance >= (0)::numeric) AND (relevance <= (1)::numeric))),
    CONSTRAINT "answer_valuePercent_check" CHECK ((("valuePercent" >= (0)::numeric) AND ("valuePercent" <= (1)::numeric)))
);


ALTER TABLE public.answer OWNER TO postgres;

--
-- TOC entry 175 (class 1259 OID 23791)
-- Dependencies: 174 6
-- Name: answer_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE answer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.answer_id_seq OWNER TO postgres;

--
-- TOC entry 3131 (class 0 OID 0)
-- Dependencies: 175
-- Name: answer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE answer_id_seq OWNED BY answer.id;


--
-- TOC entry 176 (class 1259 OID 23793)
-- Dependencies: 3043 3044 6
-- Name: category; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE category (
    id integer NOT NULL,
    name text NOT NULL,
    official boolean DEFAULT false NOT NULL,
    parent integer,
    "officialCategory" integer,
    "dateCreated" timestamp with time zone DEFAULT now(),
    creator integer,
    "dateModified" timestamp with time zone,
    modifier integer
);


ALTER TABLE public.category OWNER TO postgres;

--
-- TOC entry 3132 (class 0 OID 0)
-- Dependencies: 176
-- Name: COLUMN category.official; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN category.official IS 'Wether it''s an official category';


--
-- TOC entry 177 (class 1259 OID 23801)
-- Dependencies: 3045 1413 6
-- Name: geoname; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE geoname (
    id integer NOT NULL,
    name character varying(200),
    asciiname character varying(200),
    alternatenames character varying(8000),
    latitude double precision,
    longitude double precision,
    fclass character(1),
    fcode character varying(10),
    country character varying(2),
    cc2 character varying(60),
    admin1 character varying(20),
    admin2 character varying(80),
    admin3 character varying(20),
    admin4 character varying(20),
    population numeric,
    elevation integer,
    gtopo30 integer,
    timezone character varying(40),
    moddate date,
    the_geom geometry,
    CONSTRAINT enforce_geotype_the_geom CHECK (((geometrytype(the_geom) = 'POINT'::text) OR (the_geom IS NULL)))
);


ALTER TABLE public.geoname OWNER TO postgres;

--
-- TOC entry 178 (class 1259 OID 23808)
-- Dependencies: 3046 3047 6 1488
-- Name: question; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE question (
    id integer NOT NULL,
    "order" smallint DEFAULT 0 NOT NULL,
    type questiontype NOT NULL,
    name text NOT NULL,
    parent integer,
    "officialQuestion" integer,
    questionnaire integer,
    category integer,
    "dateCreated" timestamp with time zone DEFAULT now(),
    creator integer,
    "dateModified" timestamp with time zone,
    modifier integer
);


ALTER TABLE public.question OWNER TO postgres;

--
-- TOC entry 3133 (class 0 OID 0)
-- Dependencies: 178
-- Name: COLUMN question."order"; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN question."order" IS 'Define the questions order within a given questionnaire';


--
-- TOC entry 3134 (class 0 OID 0)
-- Dependencies: 178
-- Name: COLUMN question.parent; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN question.parent IS 'Parent question of this question';


--
-- TOC entry 3135 (class 0 OID 0)
-- Dependencies: 178
-- Name: COLUMN question."officialQuestion"; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN question."officialQuestion" IS 'The official question in the official survey corresponding to this question';


--
-- TOC entry 179 (class 1259 OID 23816)
-- Dependencies: 6 178
-- Name: question_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE question_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.question_id_seq OWNER TO postgres;

--
-- TOC entry 3136 (class 0 OID 0)
-- Dependencies: 179
-- Name: question_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE question_id_seq OWNED BY question.id;


--
-- TOC entry 180 (class 1259 OID 23818)
-- Dependencies: 3049 6
-- Name: questionnaire; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE questionnaire (
    id integer NOT NULL,
    "dateObservationStart" timestamp with time zone NOT NULL,
    "dateObservationEnd" timestamp with time zone NOT NULL,
    geoname integer,
    survey integer,
    "dateCreated" timestamp with time zone DEFAULT now(),
    creator integer,
    "dateModified" timestamp with time zone,
    modifier integer
);


ALTER TABLE public.questionnaire OWNER TO postgres;

--
-- TOC entry 181 (class 1259 OID 23822)
-- Dependencies: 6 180
-- Name: questionnaire_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE questionnaire_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.questionnaire_id_seq OWNER TO postgres;

--
-- TOC entry 3137 (class 0 OID 0)
-- Dependencies: 181
-- Name: questionnaire_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE questionnaire_id_seq OWNED BY questionnaire.id;


--
-- TOC entry 182 (class 1259 OID 23824)
-- Dependencies: 3051 3052 3053 6
-- Name: role; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE role (
    id integer NOT NULL,
    name character varying(255),
    "canValidateQuestionnaire" boolean DEFAULT false NOT NULL,
    "canLinkOfficialQuestion" boolean DEFAULT false NOT NULL,
    "dateCreated" timestamp with time zone DEFAULT now(),
    creator integer,
    "dateModified" timestamp with time zone,
    modifier integer
);


ALTER TABLE public.role OWNER TO postgres;

--
-- TOC entry 183 (class 1259 OID 23830)
-- Dependencies: 6 182
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.role_id_seq OWNER TO postgres;

--
-- TOC entry 3138 (class 0 OID 0)
-- Dependencies: 183
-- Name: role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE role_id_seq OWNED BY role.id;


--
-- TOC entry 184 (class 1259 OID 23832)
-- Dependencies: 6
-- Name: setting; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE setting (
    id character varying(64) NOT NULL,
    value text
);


ALTER TABLE public.setting OWNER TO postgres;

--
-- TOC entry 185 (class 1259 OID 23838)
-- Dependencies: 3055 3056 6
-- Name: survey; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE survey (
    id integer NOT NULL,
    name text NOT NULL,
    active boolean DEFAULT false NOT NULL,
    year numeric(4,0),
    "dateCreated" timestamp with time zone DEFAULT now(),
    creator integer,
    "dateModified" timestamp with time zone,
    modifier integer
);


ALTER TABLE public.survey OWNER TO postgres;

--
-- TOC entry 186 (class 1259 OID 23846)
-- Dependencies: 6 185
-- Name: survey_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE survey_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.survey_id_seq OWNER TO postgres;

--
-- TOC entry 3139 (class 0 OID 0)
-- Dependencies: 186
-- Name: survey_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE survey_id_seq OWNED BY survey.id;


--
-- TOC entry 187 (class 1259 OID 23848)
-- Dependencies: 3058 3059 3060 6
-- Name: user; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "user" (
    id integer NOT NULL,
    name character varying(255) DEFAULT NULL::character varying,
    email character varying(255) DEFAULT NULL::character varying,
    password character varying(128) NOT NULL,
    state smallint,
    "dateCreated" timestamp with time zone DEFAULT now(),
    creator integer,
    "dateModified" timestamp with time zone,
    modifier integer
);


ALTER TABLE public."user" OWNER TO postgres;

--
-- TOC entry 192 (class 1259 OID 24030)
-- Dependencies: 6
-- Name: user-questionnaire; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "user-questionnaire" (
    id integer NOT NULL,
    "user" integer,
    role integer,
    questionnaire integer
);


ALTER TABLE public."user-questionnaire" OWNER TO postgres;

--
-- TOC entry 191 (class 1259 OID 24028)
-- Dependencies: 192 6
-- Name: user-questionnaire_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "user-questionnaire_id_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user-questionnaire_id_seq" OWNER TO postgres;

--
-- TOC entry 3140 (class 0 OID 0)
-- Dependencies: 191
-- Name: user-questionnaire_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "user-questionnaire_id_seq" OWNED BY "user-questionnaire".id;


--
-- TOC entry 190 (class 1259 OID 24000)
-- Dependencies: 6
-- Name: user-survey; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "user-survey" (
    id integer NOT NULL,
    "user" integer,
    role integer,
    survey integer
);


ALTER TABLE public."user-survey" OWNER TO postgres;

--
-- TOC entry 189 (class 1259 OID 23998)
-- Dependencies: 6 190
-- Name: user-survey_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE "user-survey_id_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."user-survey_id_seq" OWNER TO postgres;

--
-- TOC entry 3141 (class 0 OID 0)
-- Dependencies: 189
-- Name: user-survey_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE "user-survey_id_seq" OWNED BY "user-survey".id;


--
-- TOC entry 188 (class 1259 OID 23857)
-- Dependencies: 6 187
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_id_seq OWNER TO postgres;

--
-- TOC entry 3142 (class 0 OID 0)
-- Dependencies: 188
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE user_id_seq OWNED BY "user".id;


--
-- TOC entry 3039 (class 2604 OID 23859)
-- Dependencies: 175 174
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY answer ALTER COLUMN id SET DEFAULT nextval('answer_id_seq'::regclass);


--
-- TOC entry 3048 (class 2604 OID 23860)
-- Dependencies: 179 178
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY question ALTER COLUMN id SET DEFAULT nextval('question_id_seq'::regclass);


--
-- TOC entry 3050 (class 2604 OID 23861)
-- Dependencies: 181 180
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY questionnaire ALTER COLUMN id SET DEFAULT nextval('questionnaire_id_seq'::regclass);


--
-- TOC entry 3054 (class 2604 OID 23862)
-- Dependencies: 183 182
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY role ALTER COLUMN id SET DEFAULT nextval('role_id_seq'::regclass);


--
-- TOC entry 3057 (class 2604 OID 23863)
-- Dependencies: 186 185
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY survey ALTER COLUMN id SET DEFAULT nextval('survey_id_seq'::regclass);


--
-- TOC entry 3061 (class 2604 OID 23864)
-- Dependencies: 188 187
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user" ALTER COLUMN id SET DEFAULT nextval('user_id_seq'::regclass);


--
-- TOC entry 3063 (class 2604 OID 24033)
-- Dependencies: 192 191 192
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-questionnaire" ALTER COLUMN id SET DEFAULT nextval('"user-questionnaire_id_seq"'::regclass);


--
-- TOC entry 3062 (class 2604 OID 24003)
-- Dependencies: 190 189 190
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-survey" ALTER COLUMN id SET DEFAULT nextval('"user-survey_id_seq"'::regclass);


--
-- TOC entry 3065 (class 2606 OID 23866)
-- Dependencies: 174 174 3123
-- Name: answer_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_pkey PRIMARY KEY (id);


--
-- TOC entry 3067 (class 2606 OID 23868)
-- Dependencies: 176 176 3123
-- Name: category_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY category
    ADD CONSTRAINT category_pkey PRIMARY KEY (id);


--
-- TOC entry 3069 (class 2606 OID 23870)
-- Dependencies: 177 177 3123
-- Name: geoname_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY geoname
    ADD CONSTRAINT geoname_pkey PRIMARY KEY (id);


--
-- TOC entry 3073 (class 2606 OID 23872)
-- Dependencies: 178 178 3123
-- Name: question_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY question
    ADD CONSTRAINT question_pkey PRIMARY KEY (id);


--
-- TOC entry 3075 (class 2606 OID 23874)
-- Dependencies: 180 180 3123
-- Name: questionnaire_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY questionnaire
    ADD CONSTRAINT questionnaire_pkey PRIMARY KEY (id);


--
-- TOC entry 3077 (class 2606 OID 23876)
-- Dependencies: 182 182 3123
-- Name: role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id);


--
-- TOC entry 3079 (class 2606 OID 23878)
-- Dependencies: 184 184 3123
-- Name: setting_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY setting
    ADD CONSTRAINT setting_pkey PRIMARY KEY (id);


--
-- TOC entry 3081 (class 2606 OID 23880)
-- Dependencies: 185 185 3123
-- Name: survey_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY survey
    ADD CONSTRAINT survey_pkey PRIMARY KEY (id);


--
-- TOC entry 3093 (class 2606 OID 24035)
-- Dependencies: 192 192 3123
-- Name: user-questionnaire_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user-questionnaire"
    ADD CONSTRAINT "user-questionnaire_pkey" PRIMARY KEY (id);


--
-- TOC entry 3095 (class 2606 OID 24037)
-- Dependencies: 192 192 192 192 3123
-- Name: user-questionnaire_user_role_questionnaire_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user-questionnaire"
    ADD CONSTRAINT "user-questionnaire_user_role_questionnaire_key" UNIQUE ("user", role, questionnaire);


--
-- TOC entry 3089 (class 2606 OID 24005)
-- Dependencies: 190 190 3123
-- Name: user-survey_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user-survey"
    ADD CONSTRAINT "user-survey_pkey" PRIMARY KEY (id);


--
-- TOC entry 3091 (class 2606 OID 24007)
-- Dependencies: 190 190 190 190 3123
-- Name: user-survey_user_role_survey_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user-survey"
    ADD CONSTRAINT "user-survey_user_role_survey_key" UNIQUE ("user", role, survey);


--
-- TOC entry 3083 (class 2606 OID 23882)
-- Dependencies: 187 187 3123
-- Name: user_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_email_key UNIQUE (email);


--
-- TOC entry 3085 (class 2606 OID 23884)
-- Dependencies: 187 187 3123
-- Name: user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- TOC entry 3087 (class 2606 OID 23886)
-- Dependencies: 187 187 3123
-- Name: user_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_username_key UNIQUE (name);


--
-- TOC entry 3070 (class 1259 OID 23887)
-- Dependencies: 177 3123
-- Name: idx_geoname_fcode; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX idx_geoname_fcode ON geoname USING btree (fcode);


--
-- TOC entry 3071 (class 1259 OID 23888)
-- Dependencies: 2417 177 3123
-- Name: idx_geoname_the_geom; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX idx_geoname_the_geom ON geoname USING gist (the_geom);


--
-- TOC entry 3099 (class 2606 OID 23939)
-- Dependencies: 3080 174 185 3123
-- Name: answer_creator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_creator_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3100 (class 2606 OID 23944)
-- Dependencies: 174 3080 185 3123
-- Name: answer_modifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_modifier_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3096 (class 2606 OID 23889)
-- Dependencies: 174 178 3072 3123
-- Name: answer_question_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_question_fkey FOREIGN KEY (question) REFERENCES question(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3097 (class 2606 OID 23894)
-- Dependencies: 174 180 3074 3123
-- Name: answer_questionnaire_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_questionnaire_fkey FOREIGN KEY (questionnaire) REFERENCES questionnaire(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3098 (class 2606 OID 23899)
-- Dependencies: 174 187 3084 3123
-- Name: answer_valueUser_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT "answer_valueUser_fkey" FOREIGN KEY ("valueUser") REFERENCES "user"(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3103 (class 2606 OID 23949)
-- Dependencies: 3080 185 176 3123
-- Name: category_creator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY category
    ADD CONSTRAINT category_creator_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3104 (class 2606 OID 23954)
-- Dependencies: 176 3080 185 3123
-- Name: category_modifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY category
    ADD CONSTRAINT category_modifier_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3101 (class 2606 OID 23904)
-- Dependencies: 3066 176 176 3123
-- Name: category_officialCategory_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY category
    ADD CONSTRAINT "category_officialCategory_fkey" FOREIGN KEY ("officialCategory") REFERENCES category(id);


--
-- TOC entry 3102 (class 2606 OID 23909)
-- Dependencies: 176 176 3066 3123
-- Name: category_parent_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY category
    ADD CONSTRAINT category_parent_fkey FOREIGN KEY (parent) REFERENCES category(id);


--
-- TOC entry 3105 (class 2606 OID 23914)
-- Dependencies: 178 176 3066 3123
-- Name: question_category_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY question
    ADD CONSTRAINT question_category_fkey FOREIGN KEY (category) REFERENCES category(id);


--
-- TOC entry 3108 (class 2606 OID 23959)
-- Dependencies: 185 178 3080 3123
-- Name: question_creator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY question
    ADD CONSTRAINT question_creator_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3109 (class 2606 OID 23964)
-- Dependencies: 3080 178 185 3123
-- Name: question_modifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY question
    ADD CONSTRAINT question_modifier_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3106 (class 2606 OID 23919)
-- Dependencies: 178 178 3072 3123
-- Name: question_officialQuestion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY question
    ADD CONSTRAINT "question_officialQuestion_fkey" FOREIGN KEY ("officialQuestion") REFERENCES question(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3107 (class 2606 OID 23924)
-- Dependencies: 178 178 3072 3123
-- Name: question_parent_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY question
    ADD CONSTRAINT question_parent_fkey FOREIGN KEY (parent) REFERENCES question(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3110 (class 2606 OID 23969)
-- Dependencies: 180 3080 185 3123
-- Name: questionnaire_creator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY questionnaire
    ADD CONSTRAINT questionnaire_creator_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3111 (class 2606 OID 23974)
-- Dependencies: 3080 185 180 3123
-- Name: questionnaire_modifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY questionnaire
    ADD CONSTRAINT questionnaire_modifier_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3112 (class 2606 OID 23929)
-- Dependencies: 185 182 3080 3123
-- Name: role_creator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_creator_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3113 (class 2606 OID 23934)
-- Dependencies: 185 182 3080 3123
-- Name: role_modifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_modifier_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3114 (class 2606 OID 23979)
-- Dependencies: 185 185 3080 3123
-- Name: survey_creator_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY survey
    ADD CONSTRAINT survey_creator_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3115 (class 2606 OID 23984)
-- Dependencies: 185 185 3080 3123
-- Name: survey_modifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY survey
    ADD CONSTRAINT survey_modifier_fkey FOREIGN KEY (creator) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3120 (class 2606 OID 24043)
-- Dependencies: 180 192 3074 3123
-- Name: user-questionnaire_questionnaire_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-questionnaire"
    ADD CONSTRAINT "user-questionnaire_questionnaire_fkey" FOREIGN KEY (questionnaire) REFERENCES questionnaire(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3119 (class 2606 OID 24038)
-- Dependencies: 182 3076 192 3123
-- Name: user-questionnaire_role_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-questionnaire"
    ADD CONSTRAINT "user-questionnaire_role_fkey" FOREIGN KEY (role) REFERENCES role(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3121 (class 2606 OID 24048)
-- Dependencies: 3084 192 187 3123
-- Name: user-questionnaire_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-questionnaire"
    ADD CONSTRAINT "user-questionnaire_user_fkey" FOREIGN KEY ("user") REFERENCES "user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3116 (class 2606 OID 24013)
-- Dependencies: 3076 190 182 3123
-- Name: user-survey_role_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-survey"
    ADD CONSTRAINT "user-survey_role_fkey" FOREIGN KEY (role) REFERENCES role(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3117 (class 2606 OID 24018)
-- Dependencies: 190 3080 185 3123
-- Name: user-survey_survey_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-survey"
    ADD CONSTRAINT "user-survey_survey_fkey" FOREIGN KEY (survey) REFERENCES survey(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3118 (class 2606 OID 24023)
-- Dependencies: 190 187 3084 3123
-- Name: user-survey_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "user-survey"
    ADD CONSTRAINT "user-survey_user_fkey" FOREIGN KEY ("user") REFERENCES "user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


ALTER TABLE ONLY "questionnaire"
    ADD CONSTRAINT "questionnaire_geoname_fkey" FOREIGN KEY ("geoname") REFERENCES "geoname"(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3128 (class 0 OID 0)
-- Dependencies: 6
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2013-02-12 17:37:45 KST

--
-- PostgreSQL database dump complete
--

