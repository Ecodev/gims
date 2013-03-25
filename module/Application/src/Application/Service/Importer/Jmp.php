<?php

namespace Application\Service\Importer;

class Jmp extends AbstractImporter
{

    private $cacheCategories = array();
    private $cacheQuestions = array();

    /**
     * @var \Application\Model\Part
     */
    private $partUrban;

    /**
     * @var \Application\Model\Part
     */
    private $partRural;

    /**
     * Import data from file
     */
    public function import($filename)
    {
        $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);

        $sheeNamesToImport = array('Tables_W', 'Tables_S');
        $reader->setLoadSheetsOnly($sheeNamesToImport);
        $workbook = $reader->load($filename);

        $this->partUrban = $this->getPart('Urban');
        $this->partRural = $this->getPart('Rural');

        $questionnaireCount = 0;
        foreach ($sheeNamesToImport as $i => $sheetName) {
            $workbook->setActiveSheetIndex($i);
            $sheet = $workbook->getSheet($i);

            // Import all questionnaire found, until no questionnaire code found
            $col = 0;
            while ($this->importQuestionnaire($sheet, $col)) {
                $col += 6;
                $questionnaireCount++;
                echo PHP_EOL;
            }
        }

        return "Total questionnaire: " . $questionnaireCount . PHP_EOL;
    }

    /**
     * Import a questionnaire from the given column offset.
     * Questionnaire and Answers will always be created new. All other objects will be retrieved from database if available.
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @return boolean whether it imported something
     */
    protected function importQuestionnaire(\PHPExcel_Worksheet $sheet, $col)
    {
        $code = $sheet->getCellByColumnAndRow($col + 2, 1)->getCalculatedValue();

        // If no code found, we assume no survey at all
        if (!$code) {
            return false;
        }

        // Load or create survey
        $surveyRepository = $this->getEntityManager()->getRepository('Application\Model\Survey');
        $survey = $surveyRepository->findOneBy(array('code' => $code));
        if (!$survey) {
            $survey = new \Application\Model\Survey();
            $this->getEntityManager()->persist($survey);

            $survey->setActive(true);
            $survey->setCode($code);
            $survey->setName($sheet->getCellByColumnAndRow($col + 0, 2)->getCalculatedValue());
            $survey->setYear($sheet->getCellByColumnAndRow($col + 3, 3)->getCalculatedValue());

            if (!$survey->getName()) {
                $survey->setName($survey->getCode());
            }
        }

        // Create questionnaire
        $questionnaire = new \Application\Model\Questionnaire();
        $this->getEntityManager()->persist($questionnaire);
        $questionnaire->setSurvey($survey);
        $questionnaire->setDateObservationStart(new \DateTime($survey->getYear() . '-01-01'));
        $questionnaire->setDateObservationEnd(new \DateTime($survey->getYear() . '-12-31T23:59:59'));

        $countryName = $sheet->getCellByColumnAndRow($col + 3, 1)->getCalculatedValue();
        $countryRepository = $this->getEntityManager()->getRepository('Application\Model\Country');
        $country = $countryRepository->findOneBy(array('name' => $countryName));
        $questionnaire->setGeoname($country->getGeoname());

        echo 'Survey: ' . $survey->getCode() . PHP_EOL;
        echo 'Country: ' . $country->getName() . PHP_EOL;

        $this->importAnswers($sheet, $col, $survey, $questionnaire);


        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * Import all answers found at given column offset.
     * Questions will only be created if an answer exists.
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param \Application\Model\Questionnaire $questionnaire
     * @return void
     */
    protected function importAnswers(\PHPExcel_Worksheet $sheet, $col, \Application\Model\Survey $survey, \Application\Model\Questionnaire $questionnaire)
    {
        // Define the categories where we actually have answer data
        $parts = array(
            $col + 3 => $this->partUrban,
            $col + 4 => $this->partRural,
            $col + 5 => null, // total is not a part
        );

        // The survey category, the parent category of all other categories
        $surveyCategoryName = $sheet->getCellByColumnAndRow($col, 1)->getCalculatedValue();
        $surveyCategory = $this->getCategory($surveyCategoryName);

        $answerCount = 0;
        $officialParentCategory = null; // Tap water, Ground Water, etc...
        $officialCategory = null; // House connection, piped water into dwelling, piped water to yard, etc...

        for ($row = 5; $row < 77; $row++) {
            $officialParentCategoryName = $sheet->getCellByColumnAndRow($col + 1, $row)->getCalculatedValue();
            if ($officialParentCategoryName) {
                $officialParentCategory = $this->getCategory($officialParentCategoryName, $surveyCategory);
                $officialCategory = null;
            }

            $officialCategoryName = $sheet->getCellByColumnAndRow($col + 2, $row)->getCalculatedValue();
            if ($officialCategoryName) {
                $officialCategory = $this->getCategory($officialCategoryName, $officialParentCategory);
            }

            // If there is an alternate category, linked it to official
            $alternateCategory = null;
            $alternateCategoryName = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            if ($alternateCategoryName) {
                if ($officialCategory) {
                    $alternateCategory = $this->getCategory($alternateCategoryName, $officialParentCategory, $officialCategory);
                } else {
                    $alternateCategory = $this->getCategory($alternateCategoryName, $surveyCategory, $officialParentCategory);
                }
            }

            // Use alternate instead of official, if any
            $officialCategory = $officialCategory ?: $officialParentCategory;
            $category = $alternateCategory ?: $officialCategory;

            // Import answers
            foreach ($parts as $c => $part) {
                $answerCell = $sheet->getCellByColumnAndRow($c, $row);

                // Only import value which are numeric, and NOT formula
                if ($answerCell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {

                    $question = $this->getQuestion($survey, $category, $answerCount);

                    $answer = new \Application\Model\Answer();
                    $this->getEntityManager()->persist($answer);
                    $answer->setQuestionnaire($questionnaire);
                    $answer->setQuestion($question);
                    $answer->setValuePercent($answerCell->getValue() / 100);
                    $answer->setPart($part);

                    $answerCount++;
                }
            }
        }

        echo "Answers: " . $answerCount . PHP_EOL;
    }

    /**
     * Returns a category either from database, or newly created
     * @param string $name
     * @param \Application\Model\Category $parent
     * @param \Application\Model\Category $officialCategory
     * @return \Application\Model\Category
     */
    protected function getCategory($name, \Application\Model\Category $parent = null, \Application\Model\Category $officialCategory = null)
    {
        $categoryRepository = $this->getEntityManager()->getRepository('Application\Model\Category');
        $criteria = array('name' => $name);
        if ($parent)
            $criteria['parent'] = $parent;

        $key = $name . '::' . ($parent ? $parent->getName() : '[no parent]');
        if (array_key_exists($key, $this->cacheCategories)) {
            $category = $this->cacheCategories[$key];
        } else {
            $this->getEntityManager()->flush();
            $category = $categoryRepository->findOneBy($criteria);
        }

        if (!$category) {
            $category = new \Application\Model\Category();
            $this->getEntityManager()->persist($category);
            $category->setName($name);
            $category->setOfficial(is_null($officialCategory));
            $category->setOfficialCategory($officialCategory);
            $category->setParent($parent);
        }

        $this->cacheCategories[$key] = $category;

        return $category;
    }

    /**
     * Returns a question either from database, or newly created
     * @param \Application\Model\Questionnaire $survey
     * @param \Application\Model\Category $category
     * @param integer $sorting Sorting of the question
     * @return \Application\Model\Question
     */
    protected function getQuestion(\Application\Model\Survey $survey, \Application\Model\Category $category, $sorting)
    {
        $questionRepository = $this->getEntityManager()->getRepository('Application\Model\Question');

        $key = $survey->getCode() . '::' . $category->getName() . '::' . $sorting;

        if (array_key_exists($key, $this->cacheQuestions)) {
            $question = $this->cacheQuestions[$key];
        } else {
            $this->getEntityManager()->flush();
            $question = $questionRepository->findOneBy(array('survey' => $survey, 'category' => $category));
        }

        if (!$question) {
            $question = new \Application\Model\Question();
            $this->getEntityManager()->persist($question);

            $question->setSurvey($survey);
            $question->setCategory($category);
            $question->setSorting($sorting);
            $question->setName('Percentage of population?');
            $question->setHasParts(true);
            $question->setType('foo'); // @TODO: find out better value
            $this->getEntityManager()->persist($question);
        }

        $this->cacheQuestions[$key] = $question;

        return $question;
    }

}
