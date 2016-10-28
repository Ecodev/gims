INSERT INTO user (id, date_created, name, email, password, state) VALUES
(1, '2013-08-22 11:58:37+09', 'Test user', 'gims@gims.pro', '$2y$14$freAj94ZDP60tRQyVTOyW.2Yh6IMuqJ.a1WrxRgAeWESMCSO62rKe', 1);

INSERT INTO survey (id, creator_id, date_created, name, code, is_active, year, date_start, date_end, type) VALUES
(1, 1, '2013-08-22 15:45:28+09', 'Test Survey for GLASS', 'TST-GLASS', TRUE, 2013, '2013-01-01 00:00:00+09', '2013-12-31 00:00:00+09', 'glaas');

INSERT INTO questionnaire (id, geoname_id, survey_id, date_observation_start, date_observation_end, status) VALUES
(1, 2658434, 1, '2013-01-01 00:00:00+09', '2013-12-31 00:00:00+09', 'new');

INSERT INTO question (id, creator_id, modifier_id, chapter_id, survey_id, filter_id, date_created, date_modified, sorting, name, dtype, is_compulsory, is_multiple, is_final, is_population, is_absolute, alternate_names) VALUES
(1, 1, NULL, NULL, 1, NULL, '2013-08-22 12:04:05+09', NULL, 1, 'Introduction', 'chapter', FALSE, FALSE, TRUE, FALSE, FALSE, '[]'),
(2, 1, NULL, NULL, 1, NULL, '2013-08-22 12:04:54+09', NULL, 2, 'Section 1.0.0', 'chapter', FALSE, FALSE, TRUE, FALSE, FALSE, '[]'),
(3, 1, NULL, 2, 1, NULL, '2013-08-22 12:05:27+09', NULL, 3, 'Chapter 1.1.0 (folder)', 'chapter', FALSE, FALSE, FALSE, FALSE, FALSE, '[]'),
(4, 1, 1, 3, 1, NULL, '2013-08-22 12:07:17+09', NULL, 4, 'Question 1.1.1', 'choicequestion', FALSE, FALSE, FALSE, FALSE, FALSE, '[]'),
(5, 1, NULL, 3, 1, NULL, '2013-08-22 12:08:41+09', NULL, 5, 'Question 1.1.2', 'textquestion', TRUE, FALSE, FALSE, FALSE, FALSE, '[]'),
(6, 1, NULL, 3, 1, NULL, '2013-08-22 12:10:37+09', NULL, 6, 'Question 1.1.3', 'numericquestion', TRUE, FALSE, FALSE, FALSE, FALSE, '[]'),
(7, 1, NULL, 2, 1, NULL, '2013-08-22 12:11:18+09', NULL, 7, 'Chapter 1.2 (final)', 'chapter', FALSE, FALSE, TRUE, FALSE, FALSE, '[]'),
(8, 1, NULL, 7, 1, NULL, '2013-08-22 12:15:01+09', NULL, 8, 'Question 1.2.1', 'numericquestion', TRUE, FALSE, FALSE, FALSE, FALSE, '[]'),
(9, 1, NULL, 7, 1, NULL, '2013-08-22 12:15:45+09', NULL, 9, 'Question 1.2.2', 'numericquestion', FALSE, FALSE, FALSE, FALSE, FALSE, '[]'),
(10, 1, NULL, 7, 1, NULL, '2013-08-22 12:16:19+09', NULL, 19, 'Question 1.2.3', 'textquestion', TRUE, FALSE, FALSE, FALSE, FALSE, '[]'),
(11, 1, NULL, NULL, 1, NULL, '2013-08-22 12:12:16+09', NULL, 11, 'Section 2.0.0', 'chapter', FALSE, FALSE, FALSE, FALSE, FALSE, '[]'),
(12, 1, NULL, 11, 1, NULL, '2013-08-22 12:12:41+09', NULL, 12, 'Chapter 2.1', 'chapter', FALSE, FALSE, FALSE, FALSE, FALSE, '[]'),
(13, 1, NULL, 12, 1, NULL, '2013-08-22 12:13:30+09', NULL, 13, 'Question 2.1.1', 'numericquestion', TRUE, FALSE, FALSE, FALSE, FALSE, '[]'),
(14, 1, NULL, 12, 1, NULL, '2013-08-22 12:14:07+09', NULL, 14, 'Question 2.1.2', 'textquestion', FALSE, FALSE, FALSE, FALSE, FALSE, '[]');

INSERT INTO choice (id, creator_id, question_id, date_created, sorting, value, name) VALUES
(1, 1, 4, '2013-08-22 15:52:58+09', 0, 0.000, 'choice 1'),
(2, 1, 4, '2013-08-22 15:52:58+09', 1, 0.500, 'choice 2'),
(3, 1, 4, '2013-08-22 15:52:58+09', 2, 1.000, 'choice 3');
