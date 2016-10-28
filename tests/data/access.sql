-- Give access to everything to test user
INSERT INTO user_survey (user_id, role_id, survey_id) SELECT 1, 5, survey.id FROM survey;
INSERT INTO user_survey (user_id, role_id, survey_id) SELECT 1, 7, survey.id FROM survey;
INSERT INTO user_questionnaire (user_id, role_id, questionnaire_id) SELECT 1, 3, questionnaire.id FROM questionnaire;
INSERT INTO user_filter_set (role_id, user_id, filter_set_id) SELECT 6, 1, filter_set.id from filter_set;
UPDATE filter SET creator_id = 1 WHERE creator_id IS NULL;
UPDATE questionnaire SET status = 'published';
UPDATE filter_set SET is_published = TRUE;
