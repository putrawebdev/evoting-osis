--
-- PostgreSQL database dump
--

\restrict rlLnkbWcMJLDLuSBmcMtHkJO2bYhZ6ZnxChTYki0ZD9ZnCkvuQCoIAims1MYDMZ

-- Dumped from database version 18.6
-- Dumped by pg_dump version 18.6

-- Started on 2026-10-11 18:37:23

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
-- TOC entry 229 (class 1259 OID 17133)
-- Name: kandidat; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.kandidat (
    id bigint NOT NULL,
    no_paslon integer NOT NULL,
    nama_ketua character varying(255) NOT NULL,
    nama_wakil character varying(255) NOT NULL,
    foto character varying(255) NOT NULL,
    visi text NOT NULL,
    misi text NOT NULL,
    proggram text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    poster character varying(255)
);


ALTER TABLE public.kandidat OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 17132)
-- Name: kandidat_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.kandidat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.kandidat_id_seq OWNER TO postgres;

--
-- TOC entry 5051 (class 0 OID 0)
-- Dependencies: 228
-- Name: kandidat_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.kandidat_id_seq OWNED BY public.kandidat.id;


--
-- TOC entry 4892 (class 2604 OID 17136)
-- Name: kandidat id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kandidat ALTER COLUMN id SET DEFAULT nextval('public.kandidat_id_seq'::regclass);


--
-- TOC entry 5045 (class 0 OID 17133)
-- Dependencies: 229
-- Data for Name: kandidat; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.kandidat (id, no_paslon, nama_ketua, nama_wakil, foto, visi, misi, proggram, created_at, updated_at, poster) FROM stdin;
1	1	Alfian Nadya Dewanti (Kelas 8.7)	Vanessa tridiyani (Kelas 8.1)	kandidat/lWJmqwdGxbmsRIct0H2g2rO55moIWstvUYJ02luA.png	Mewujudkan OSIS SMPN 5 Cibitung sebagai organisasi yang unggul dan wadah bagi siswa untuk menjadi pribadi yang SMART (Sopan, Mandiri, Aktif, Rajin, dan Terampil) Berprestasi, peduli lingkungan, serta berwawasan luas.	Menjadi wadah bagi siswa untuk menyampaikan aspirasi, ide, dan pendapat secara terbuka dan bertanggung jawab.\r\nMengadakan program kerja yang kreatif, relevan, dan berdampak nyata.\r\nMendorong siswa untuk mengembangkan potensi, kreativitas, dan rasa percaya diri melalui kegiatan yang positif dan inspiratif. \r\nMembuka wawasan siswa terhadap budaya, teknologi, dan perkembangan global tanpa meninggalkan budaya Indonesia.	null	2026-10-11 08:32:18	2026-10-11 11:33:44	\N
2	2	Zahra Nur Rohmah (Kelas 8.4)	Nur Aisyah Mulyani (Kelas 8.4)	kandidat/MBZI8zwRM8V1kflZVx3lntHJbZBBtRPHyX3aV5k6.png	Menjadikan OSIS SMPN 5 Cibitung sebagai wadah yang aktif dan inklusif untuk mewujudkan siswa yang beriman, cerdas, peduli lingkungan, serta bebas dari bullying.	Meningkatkan keimanan, ketakwaan, dan akhlak siswa.   Mengembangkan kreativitas dan bakat akademik maupun non-akademik.\r\nMeningkatkan kepedulian terhadap kebersihan dan lingkungan sekolah.\r\nMenciptakan lingkungan sekolah yang aman, harmonis, dan bebas bullying.	null	2026-10-11 08:36:11	2026-10-11 11:35:58	\N
\.


--
-- TOC entry 5052 (class 0 OID 0)
-- Dependencies: 228
-- Name: kandidat_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.kandidat_id_seq', 2, true);


--
-- TOC entry 4894 (class 2606 OID 17150)
-- Name: kandidat kandidat_no_paslon_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kandidat
    ADD CONSTRAINT kandidat_no_paslon_unique UNIQUE (no_paslon);


--
-- TOC entry 4896 (class 2606 OID 17148)
-- Name: kandidat kandidat_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kandidat
    ADD CONSTRAINT kandidat_pkey PRIMARY KEY (id);


-- Completed on 2026-10-11 18:37:23

--
-- PostgreSQL database dump complete
--

\unrestrict rlLnkbWcMJLDLuSBmcMtHkJO2bYhZ6ZnxChTYki0ZD9ZnCkvuQCoIAims1MYDMZ

