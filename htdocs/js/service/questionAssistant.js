angular.module('myApp.services').factory('QuestionAssistant', function ()
    {
        'use strict';

        var indexQuestion = function (QuestionAssistant, question, firstExecution, index)
        {
            switch (question.type) {
                case 'Text' :
                    return indexOneAnswerPerPartQuestion(QuestionAssistant, question, firstExecution, index, 'valueText');
                    break;
                case 'Numeric' :
                    return indexOneAnswerPerPartQuestion(QuestionAssistant, question, firstExecution, index, 'valueAbsolute');
                    break;
                case 'User' :
                    return indexOneAnswerPerPartQuestion(QuestionAssistant, question, firstExecution, index, 'valueUser');
                    break;
                case 'Choice' :
                    return indexOneOrMultipleAnswersPerPartQuestion(QuestionAssistant, question, firstExecution, index);
                    break;
            }

            return 4;
        }

        /**
         * Numeric and Text questions are similar and when they're compulsory, they need one answer per part
         *
         * This function set index and return status for those both types.
         */
        var indexOneAnswerPerPartQuestion = function (QuestionAssistant, question, firstExecution, index, valueField)
        {
            var status = 3;
            angular.forEach(question.parts, function (part)
            {
                var id = question.id + '-' + part.id;
                var answer = findOneAnswerPerPartAnswer(QuestionAssistant, question, index, firstExecution, valueField, part.id);
                if (!answer) {
                    if (question.isCompulsory) {
                        status = 1;
                    } // if compulsory, all parts have to have answer
                    else if (!question.isCompulsory) {
                        status = 2;
                    } // if not compulsory, just notify that there are unanswered fields.
                    if (firstExecution) {
                        index[id] = QuestionAssistant.getEmptyTextAnswer(question, part.id);
                    }
                } else {
                    if (firstExecution) {
                        index[id] = answer;
                    }
                }
            });

            return question.statusCode = status;
        }


        var findOneAnswerPerPartAnswer = function (QuestionAssistant, question, index, firstExecution, valueField, pid)
        {
            if (!firstExecution) {
                var id = question.id + '-' + pid;
                if (index[id] && index[id][valueField]) {
                    delete index[id].valueChoice;
                    return index[id];
                } else {
                    return false;
                }
            } else {

                for (var key in question.answers) {
                    var testedAnswer = question.answers[key];
                    if (testedAnswer.part && testedAnswer.part.id == pid) {
                        delete testedAnswer.valueChoice;
                        return testedAnswer;
                    }
                }
            }
            return false;
        };


        // multiple + compulsory    -> 1 if no answer found, 3 if (at least) one answer found
        // multiple + !compulsory   -> 2 if no answer found, 3 if (at least) one answer found
        // !multiple + compulsory   -> 1 if no answer found, 3 if (at least) one answer found
        // !multiple + !compulsory  -> 2 if no answer found, 3 if (at least) one answer found
        var indexOneOrMultipleAnswersPerPartQuestion = function (QuestionAssistant, question, firstExecution, index)
        {

            var statusPerPart = {};
            angular.forEach(question.parts, function (part)
            {

                if (question.isCompulsory) {
                    statusPerPart[part.id] = 1
                } else if (!question.isCompulsory) {
                    statusPerPart[part.id] = 2
                }

                if (question.isMultiple) {
                    angular.forEach(question.choices, function (choice)
                    {
                        var identifier = question.id + "-" + choice.id + "-" + part.id;
                        var answer = findOneOrMultipleAnswersPerPartAnswer(QuestionAssistant, question, firstExecution, index, part, choice);
                        if (!answer) {
                            if (firstExecution) {
                                index[identifier] = QuestionAssistant.getEmptyChoiceAnswer(question, part, choice);
                            }
                        } else {
                            statusPerPart[part.id] = 3;
                            if (firstExecution) {
                                index[identifier] = answer;
                            }
                        }
                    });
                } else {
                    var identifier = question.id + "-" + part.id;
                    var answer = findOneOrMultipleAnswersPerPartAnswer(QuestionAssistant, question, firstExecution, index, part, null);
                    if (!answer) {
                        if (firstExecution) {
                            index[identifier] = QuestionAssistant.getEmptyChoiceAnswer(question, part);
                        }
                    } else {
                        statusPerPart[part.id] = 3;
                        if (firstExecution) {
                            index[identifier] = answer;
                        }
                    }
                }
            });

            var status = 4;
            for (var i in statusPerPart) {
                status = (statusPerPart[i] < status) ? statusPerPart[i] : status;
            }

            return question.statusCode = status;
        }

        var findOneOrMultipleAnswersPerPartAnswer = function (QuestionAssistant, question, firstExecution, index, part, choice)
        {

            if (!firstExecution) {
                if (question.isMultiple) {
                    var id = question.id + "-" + choice.id + "-" + part.id;
                    if (index[id] && index[id].isCheckboxChecked) {
                        return index[id];
                    }
                } else {
                    var id = question.id + "-" + part.id;
                    if (index[id] && index[id].valueChoice && index[id].valueChoice.id) {
                        return index[id];
                    }
                }

            } else {
                for (var key in question.answers) {
                    var testedAnswer = question.answers[key];

                    if (testedAnswer.part && testedAnswer.part.id == part.id) {
                        if (!question.isMultiple) {

                            return testedAnswer;
                        } else if (question.isMultiple && testedAnswer.valueChoice.id == choice.id) {
                            testedAnswer.isCheckboxChecked = true;

                            return testedAnswer;
                        }
                    }
                }
            }

            return false;
        }

        var getChildrenStatus = function (QuestionAssistant, question, index, firstExecution)
        {
            var childrenStatus = 4;
            if (question.children) {
                angular.forEach(question.children, function (child)
                {
                    if (!child.statusCode) {
                        child.statusCode = QuestionAssistant.updateQuestion(child, index, firstExecution, false);
                    }
                    childrenStatus = (child.statusCode < childrenStatus) ? child.statusCode : childrenStatus;
                });
            }

            return childrenStatus;

        }

        return {

            /**
             * This bilateral recursive function has two tasks : generate the answer index and set the question status.
             *
             * On the first execution, this function call recursively children and create the answers index. On the fly its computes the status and return it.
             *
             * Once the answers index is generated, we can ignore answers returned by server question.answers cause they are no more up to date. Only index is.
             *
             * This function do not compute status if they already exist, except if "updateStatus" parameter is set to true.
             * It happens on save() function call in directives <gims-question-text>, <gims-question-numeric>, <gims-question-choi>
             *
             * In the other side, this function call recursively all the parents questions (chapters) to update their status including children's status.
             * When a chapter is called recursively,this function don't compute recursively its children status.
             * As the status already have been defined by first execution and by "updateStatus" parameter on save() call, this function just calls the immediate children to get their status.
             *
             * One the status of all children have been retrieved, we get the smaller to have the more restrictive error to display.
             *
             * @param question The question from which the crawl will be executed.
             * @param index An array where all the indexed Answers are. This array match the part + choice + answer in a same place to allow ng-model usage on <inputs> in the directives <gims-question-text>, <gims-question-numeric>, <gims-question-choi>
             * @param firstExecution Create the index (previous param). Index is only generated the first time. Then the save() function in the question directives keep it up to date.
             * @param updateStatus Force to recompute the current (only) question status even if this question already has a status defined. Allow to update a question's status when an answer is changed by visitor.
             * @returns a number corresponding to status :
             *      1 : isError      -> Many required fields are empty
             *      2 : isValidated  -> Is valid but many optional fields are empty
             *      3 : isComplete   -> All fields even the optional ones have been completed
             */
            updateQuestion: function (question, index, firstExecution, updateStatus)
            {

                if (question) {
                    if (!question.statusCode || updateStatus) {
                        question.statusCode = indexQuestion(this, question, firstExecution, index);
                    }

                    var childrenStatus = getChildrenStatus(this, question, index, firstExecution);
                    if (childrenStatus < question.statusCode) {
                        question.statusCode = childrenStatus;
                    }
                    this.updateQuestion(question.parent, index, firstExecution, updateStatus);

                    return question.statusCode;
                }
            },


            /**
             * Used to create empty answer for Numeric and Text questions when loading a question that has no answer or after removing an answer

             */
            getEmptyTextAnswer: function (question, pid)
            {
                return {
                    questionnaire: question.parentResource.id,
                    part: pid,
                    question: question.id
                };
            },

            /**
             * Used to create empty answer for QuestionChoices when loading a question that has no answer or after removing an answer
             */
            getEmptyChoiceAnswer: function (question, part, choice)
            {
                var answer = {
                    questionnaire: question.parentResource.id,
                    part: part.id,
                    question: question.id,
                    isCheckboxChecked: null
                }
                if (choice) {
                    answer.valueChoice = choice;
                }
                return answer;
            }
        };
    });