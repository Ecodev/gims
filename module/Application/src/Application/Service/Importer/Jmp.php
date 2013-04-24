<?php

namespace Application\Service\Importer;

class Jmp extends AbstractImporter
{

    /**
     * sheet name =>
     *      defintions =>
     *          row => name, parent row, computing type, summands rows
     * @var array
     */
    private $officialCategoriesDefinition = array(
        'Tables_W' => array(
            'definitions' => array(
                4 => array("Access to drinking water sources", null, 0, null),
                5 => array("Tap water", 4, 0, null),
                6 => array("House connections", 5, 0, null),
                7 => array("Piped water into dwelling", 6, 0, null),
                8 => array("Piped water to yard/plot", 6, 0, null),
                9 => array("Public tap, standpipe", 5, 0, null),
                10 => array("Other", 5, 0, null),
                11 => array("Ground water", 4, 0, null),
                12 => array("Protected ground water", 11, 1, array(14, 26, 46, 34)),
                13 => array("Unprotected ground water", 11, 1, array(18, 38, 50)),
                14 => array("Protected wells or springs", 11, 2, array(26, 34, 46)),
                15 => array("Private", 14, 1, array(27, 35, 47)),
                16 => array("Public", 14, 1, array(28, 36, 48)),
                17 => array("Other", 14, 1, array(29, 37, 49)),
                18 => array("Unprotected wells or springs", 11, 2, array(38, 50)),
                19 => array("Private", 18, 1, array(39, 51)),
                20 => array("Public", 18, 1, array(40, 52)),
                21 => array("Other", 18, 1, array(41, 53)),
                22 => array("All wells", 11, 0, null),
                23 => array("Private", 22, 1, array(27, 31)),
                24 => array("Public", 22, 1, array(28, 32)),
                25 => array("Other", 22, 1, array(29, 33)),
                26 => array("Tubewell, borehole", 22, 0, null),
                27 => array("Private", 26, 0, null),
                28 => array("Public", 26, 0, null),
                29 => array("Other", 26, 0, null),
                30 => array("Traditional wells", 22, 0, null),
                31 => array("Private", 30, 1, array(35, 39)),
                32 => array("Public", 30, 1, array(36, 40)),
                33 => array("Other", 30, 1, array(37, 41)),
                34 => array("Protected well", 30, 0, null),
                35 => array("Private", 34, 0, null),
                36 => array("Public", 34, 0, null),
                37 => array("Other", 34, 0, null),
                38 => array("Unprotected well", 30, 0, null),
                39 => array("Private", 38, 0, null),
                40 => array("Public", 38, 0, null),
                41 => array("Other", 38, 0, null),
                42 => array("All springs", 11, 0, null),
                43 => array("Private", 42, 1, array(47, 51)),
                44 => array("Public", 42, 1, array(48, 52)),
                45 => array("Other", 42, 1, array(49, 53)),
                46 => array("Protected spring", 42, 0, null),
                47 => array("Private", 46, 0, null),
                48 => array("Public", 46, 0, null),
                49 => array("Other", 46, 0, null),
                50 => array("Unprotected spring", 42, 0, null),
                51 => array("Private", 50, 0, null),
                52 => array("Public", 50, 0, null),
                53 => array("Other", 50, 0, null),
                54 => array("Rainwater", 4, 0, null),
                55 => array("Covered cistern/tank", 54, 0, null),
                56 => array("Uncovered cistern/tank", 54, 0, null),
                57 => array("Bottled water", 4, 0, null),
                58 => array("with other improved", 57, 0, null),
                59 => array("without other improved", 57, 0, null),
                60 => array("Surface water", 4, 0, null),
                61 => array("River", 60, 0, null),
                62 => array("Lake", 60, 0, null),
                63 => array("Dam", 60, 0, null),
                64 => array("Pond", 60, 0, null),
                65 => array("Stream", 60, 0, null),
                66 => array("Irrigation channel", 60, 0, null),
                67 => array("Other", 60, 0, null),
                68 => array("Other improved sources", 4, 0, null),
                69 => array("Other 1", 68, 0, null),
                70 => array("Other 2", 68, 0, null),
                71 => array("Other non-improved", 4, 0, null),
                72 => array("Cart with small tank/drum", 71, 0, null),
                73 => array("Tanker truck provided", 71, 0, null),
                74 => array("Other 1", 71, 0, null),
                75 => array("Other 2", 71, 0, null),
                76 => array("DK/missing", 4, 0, null),
            ),
            'replacements' => array(
            // No replacements to make for water
            ),
            'estimations' => array(
                'min' => 83,
                'max' => 88,
            ),
            'excludes' => array(
                91 => "Total improved",
                92 => "Piped onto premises",
                93 => "Surface water",
            ),
            'filterComponents' => array(
                "Total improved" => array(5, 12, 55, 58, 68),
                "Piped onto premises" => array(6),
                "Surface water" => array(60),
                "Other Improved" => array(9, 10, 12, 55, 58, 68),
                "Other Unimproved" => array(56, 59, 71),
            ),
        ),
        'Tables_S' => array(
            'definitions' => array(
                4 => array("Use of sanitation facilities", null, 0, null),
                5 => array("Flush and pour flush", 4, 2, array(11, 30)),
                6 => array("to piped sewer system", 5, 1, array(12, 31)),
                7 => array("to septic tank", 5, 1, array(13, 32)),
                8 => array("to pit", 5, 1, array(14, 33)),
                9 => array("to unknown place/ not sure/DK", 5, 1, array(15, 34)),
                10 => array("to elsewhere", 5, 1, array(16, 35)),
                11 => array("Flush/toilets", 4, 0, null),
                12 => array("to piped sewer system", 11, 1, array(18, 24)),
                13 => array("to septic tank", 11, 1, array(19, 25)),
                14 => array("to pit", 11, 1, array(20, 26)),
                15 => array("to unknown place/ not sure/DK", 11, 1, array(21, 27)),
                16 => array("to elsewhere", 11, 1, array(22, 28)),
                17 => array("Private flush/toilet", 11, 0, null),
                18 => array("to piped sewer system", 17, 0, null),
                19 => array("to septic tank", 17, 0, null),
                20 => array("to pit", 17, 0, null),
                21 => array("to unknown place/ not sure/DK", 17, 0, null),
                22 => array("to elsewhere", 17, 0, null),
                23 => array("Public/shared flush/toilet", 11, 0, null),
                24 => array("to piped sewer system", 23, 0, null),
                25 => array("to septic tank", 23, 0, null),
                26 => array("to pit", 23, 0, null),
                27 => array("to unknown place/ not sure/DK", 23, 0, null),
                28 => array("to elsewhere", 23, 0, null),
                29 => array("Latrines", 4, 0, null),
                30 => array("Pour flush latrines", 29, 0, null),
                31 => array("to piped sewer system", 30, 1, array(37, 43)),
                32 => array("to septic tank", 30, 1, array(38, 44)),
                33 => array("to pit", 30, 1, array(39, 45)),
                34 => array("to unknown place/ not sure/DK", 30, 1, array(40, 46)),
                35 => array("to elsewhere", 30, 1, array(41, 47)),
                36 => array("Private pour flush latrine", 30, 0, null),
                37 => array("to piped sewer system", 36, 0, null),
                38 => array("to septic tank", 36, 0, null),
                39 => array("to pit", 36, 0, null),
                40 => array("to unknown place/ not sure/DK", 36, 0, null),
                41 => array("to elsewhere", 36, 0, null),
                42 => array("Public/shared pour flush latrine", 30, 0, null),
                43 => array("to piped sewer system", 42, 0, null),
                44 => array("to septic tank", 42, 0, null),
                45 => array("to pit", 42, 0, null),
                46 => array("to unknown place/ not sure/DK", 42, 0, null),
                47 => array("to elsewhere", 42, 0, null),
                48 => array("Dry latrines", 29, 0, null),
                49 => array("Improved latrines", 48, 0, null),
                50 => array("Ventilated Improved Pit latrine", 49, 1, array(58, 66)),
                51 => array("Pit latrine with slab/covered latrine", 49, 1, array(59, 67)),
                52 => array("Traditional latrine", 48, 1, array(60, 68)),
                53 => array("Pit latrine without slab/open pit", 48, 1, array(61, 69)),
                54 => array("Hanging toilet/hanging latrine", 48, 1, array(62, 70)),
                55 => array("Bucket latrine", 48, 1, array(63, 71)),
                56 => array("Other", 48, 1, array(64, 72)),
                57 => array("Private Latrines", 48, 0, null),
                58 => array("Ventilated Improved Pit latrine", 57, 0, null),
                59 => array("Pit latrine with slab/covered latrine", 57, 0, null),
                60 => array("Traditional latrine", 57, 0, null),
                61 => array("Pit latrine without slab/open pit", 57, 0, null),
                62 => array("Hanging toilet/hanging latrine", 57, 0, null),
                63 => array("Bucket latrine", 57, 0, null),
                64 => array("Other", 57, 0, null),
                65 => array("Public/shared Latrines", 48, 0, null),
                66 => array("Ventilated Improved Pit latrine", 65, 0, null),
                67 => array("Pit latrine with slab/covered latrine", 65, 0, null),
                68 => array("Traditional latrine", 65, 0, null),
                69 => array("Pit latrine without slab/open pit", 65, 0, null),
                70 => array("Hanging toilet/hanging latrine", 65, 0, null),
                71 => array("Bucket latrine", 65, 0, null),
                72 => array("Other", 65, 0, null),
                73 => array("Composting toilets", 4, 0, null),
                74 => array("Composting toilet (private)", 73, 0, null),
                75 => array("Composting toilet (shared)", 73, 0, null),
                76 => array("Other improved", 4, 0, null),
                77 => array("Other 1", 76, 0, null),
                78 => array("Other 2", 76, 0, null),
                79 => array("No facility, bush, field ", 4, 0, null),
                80 => array("Other unimproved", 4, 0, null),
                81 => array("Other 1", 80, 0, null),
                82 => array("Other 2", 80, 0, null),
                83 => array("DK/missing information", 4, 0, null),
            ),
            'replacements' => array(
                // For sanitation, we need to modify the category tree to introduce a new branch
                999 => array("Other flush and pour flush", 5, 0, null),
                6 => array("to piped sewer system", -1, 0, null),
                7 => array("to septic tank", -1, 0, null),
                8 => array("to pit", -1, 0, null),
                9 => array("to unknown place/ not sure/DK", -1, 0, null),
                10 => array("to elsewhere", -1, 0, null),
            ),
            'estimations' => array(
                'min' => 88,
                'max' => 93,
            ),
            'excludes' => array(
                96 => "Improved + shared",
                97 => "Sewerage connections",
                98 => "Open defecation",
                99 => "Shared",
            ),
            'filterComponents' => array(
                "Improved + shared" => array(-6, -7, -8, -9, 49, 73, 76),
                "Sewerage connections" => array(6),
                "Improved" => array(), // based on ratio
                "Shared" => array(), // based on ratio
                "Other unimproved" => array(-10, 30, 52, 53, 54, 55, 56, 80),
                "Open defecation" => array(79),
            ),
        ),
    );
    private $cacheAlternateCategories = array();
    private $cacheCategories = array();
    private $cacheQuestions = array();
    private $cacheFilterComponents = array();
    private $questionnaireCount = 0;
    private $answerCount = 0;
    private $excludesCount = 0;

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

        $sheeNamesToImport = array_keys($this->officialCategoriesDefinition);
        $reader->setLoadSheetsOnly($sheeNamesToImport);
        $workbook = $reader->load($filename);

        $this->excludeRule = $this->getEntityManager()->getRepository('Application\Model\Rule\AbstractRule')->getSingletonExclude();
        $this->partUrban = $this->getPart('Urban');
        $this->partRural = $this->getPart('Rural');

        foreach ($sheeNamesToImport as $i => $sheetName) {

            $this->importCategories($this->officialCategoriesDefinition[$sheetName]);
            $this->importFilterComponents($sheetName == 'Tables_W' ? 'Water' : 'Sanitation', $this->officialCategoriesDefinition[$sheetName]['filterComponents']);

            $workbook->setActiveSheetIndex($i);
            $sheet = $workbook->getSheet($i);

            // Import all questionnaire found, until no questionnaire code found
            $col = 0;
            while ($this->importQuestionnaire($sheet, $col)) {
                $col += 6;
            }
        }

        return "Total imported: $this->questionnaireCount questionnaires, $this->answerCount answers, $this->excludesCount exclude rules" . PHP_EOL;
    }

    protected function getCategory($definition)
    {
        $name = $definition[0];

        $parent = @$this->cacheCategories[$definition[1]];
        $parentName = $parent ? $parent->getName() : null;

        $categoryRepository = $this->getEntityManager()->getRepository('Application\Model\Category');
        $category = $categoryRepository->getOneByNames($name, $parentName);
        if (!$category) {

            $category = new \Application\Model\Category();
            $this->getEntityManager()->persist($category);
            $category->setName($name);
            $category->setOfficial(true);
            $category->setParent($parent);
        }

        return $category;
    }

    /**
     *
     * @param array $officialCategories
     */
    protected function importCategories(array $officialCategories)
    {
        // Import categories
        $this->cacheCategories = array();
        foreach ($officialCategories['definitions'] as $row => $definition) {
            $category = $this->getCategory($definition);
            $this->cacheCategories[$row] = $category;
        }

        // Add all summands to categories
        foreach ($officialCategories['definitions'] as $row => $definition) {
            $category = $this->cacheCategories[$row];
            $summands = $definition[3];
            if ($summands) {
                foreach ($summands as $summand) {
                    $s = $this->cacheCategories[$summand];
                    $category->addSummand($s);
                }
            }
        }

        // Replace categories with their replacements, if any defined
        // This is a dirty trick to solve inconsistency in first category of sanitation
        foreach ($officialCategories['replacements'] as $row => $definition) {
            $replacementCategory = $this->getCategory($definition);
            $originalCategory = @$this->cacheCategories[$row];

            // If original category actually exists, add the replacement as a summand, and replace it
            if ($originalCategory) {
                $originalCategory->addSummand($replacementCategory);
            }
            $this->cacheCategories[$row] = $replacementCategory;

            // Keep original category available on negative indexes
            $this->cacheCategories[-$row] = $originalCategory;
        }

        $this->getEntityManager()->flush();
        echo count($this->cacheCategories) . ' categories imported' . PHP_EOL;
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

            $survey->setActive(true);
            $survey->setCode($code);
            $survey->setName($sheet->getCellByColumnAndRow($col + 0, 2)->getCalculatedValue());
            $survey->setYear($sheet->getCellByColumnAndRow($col + 3, 3)->getCalculatedValue());

            if (!$survey->getName()) {
                $survey->setName($survey->getCode());
            }

            if (!$survey->getYear()) {
                echo 'WARNING: skipped survey because there is no year. On sheet "' . $sheet->getTitle() . '" cell ' . $sheet->getCellByColumnAndRow($col + 3, 3)->getCoordinate();
                return true;
            }
            $this->getEntityManager()->persist($survey);
            $this->getEntityManager()->flush();
        }

        // Create questionnaire
        $countryCell = $sheet->getCellByColumnAndRow($col + 3, 1);
        $questionnaire = $this->getQuestionnaire($survey, $countryCell);
        if (!$questionnaire) {
            echo 'WARNING: skipped questionnaire because there is no country name. On sheet "' . $sheet->getTitle() . '" cell ' . $countryCell->getCoordinate();
            return true;
        }

        echo 'Survey: ' . $survey->getCode() . PHP_EOL;
        echo 'Country: ' . $questionnaire->getGeoname()->getName() . PHP_EOL;

        $this->importAnswers($sheet, $col, $survey, $questionnaire);

//        $this->importRules($sheet, $col);
        $this->importExcludes($sheet, $col, $questionnaire);

        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * Import all answers found at given column offset.
     * Questions will only be created if an answer exists.
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param \Application\Model\Questionnaire $questionnaire
     * @return integer imported answer count
     */
    protected function importAnswers(\PHPExcel_Worksheet $sheet, $col, \Application\Model\Survey $survey, \Application\Model\Questionnaire $questionnaire)
    {
        // Define the categories where we actually have answer data
        $parts = array(
            $col + 3 => $this->partUrban,
            $col + 4 => $this->partRural,
            $col + 5 => null, // total is not a part
        );

        $repository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        $knownRows = array_keys($this->cacheCategories);
        array_shift($knownRows); // Skip first categorie, since it's not an actual row, but the sheet topic (eg: "Access to drinking water sources")

        $answerCount = 0;
        foreach ($knownRows as $row) {

            $category = $this->cacheCategories[$row];

            // Use alternate instead of official, if any
            $alternateCategoryName = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            if ($alternateCategoryName) {
                $category = $this->getAlternateCategory($alternateCategoryName, $category);
            }

            // Import answers
            foreach ($parts as $c => $part) {
                $answerCell = $sheet->getCellByColumnAndRow($c, $row);

                // Only import value which are numeric, and NOT formula,
                // unless an alternate category is defined, in this case we will import the formula result
                if ($alternateCategoryName || $answerCell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {

                    // If there is actually no value, skip it (need to be done after previous if to avoid formula exception within PHPExcel)
                    $value = $this->getCalculatedValueSafely($answerCell);
                    if (is_null($value)) {
                        continue;
                    }

                    $question = $this->getQuestion($survey, $category, $answerCount);

                    // If question already exists, maybe the answer also already exists, in that case we will overwrite its value
                    $answer = null;
                    if ($question->getId()) {
                        $answer = $repository->findOneBy(array(
                            'question' => $question,
                            'questionnaire' => $questionnaire,
                            'part' => $part,
                        ));
                    }

                    if (!$answer) {
                        $answer = new \Application\Model\Answer();
                        $this->getEntityManager()->persist($answer);
                        $answer->setQuestionnaire($questionnaire);
                        $answer->setQuestion($question);
                        $answer->setPart($part);
                    }

                    $answer->setValuePercent($value / 100);

                    $answerCount++;
                }
            }
        }

        $this->answerCount += $answerCount;
        echo "Answers: " . $answerCount . PHP_EOL . PHP_EOL;
    }

    protected function getQuestionnaire(\Application\Model\Survey $survey, \PHPExcel_Cell $countryCell)
    {
        // Mapping JMP country names to Geoname country names
        $countryNameMapping = array(
            'Syrian Arab Republic' => 'Syria',
            'Occupied Palestinian Territory' => 'Palestinian Territory',
            'Palestine' => 'Palestinian Territory',
            "Dem. People's Republic of Korea" => 'North Korea',
            'Republic of Korea' => 'South Korea',
            'Iran (Islamic Republic of)' => 'Iran',
            "Lao People's Democratic Republic" => 'Laos',
            'Viet Nam' => 'Vietnam',
            'Timor-Leste' => 'East Timor',
            'United States Virgin Islands' => 'U.S. Virgin Islands',
            'St. Vincent and Grenadines' => 'Saint Vincent and the Grenadines',
            'St Vincent & grenadines' => 'Saint Vincent and the Grenadines',
            'Bolivia (Plurinational State of)' => 'Bolivia',
            'Venezuela (Bolivarian Republic of)' => 'Venezuela',
            "Côte d'Ivoire" => 'Ivory Coast',
            'United Republic of Tanzania' => 'Tanzania',
            'Libyan Arab Jamahiriya' => 'Libya',
            'Congo' => 'Republic of the Congo',
            'Russian Federation' => 'Russia',
            'Republic of Moldova' => 'Moldova',
            'TFYR Macedonia' => 'Macedonia',
            'United States of America' => 'United States',
            // Unusual spelling
            'Antigua & Barbuda' => 'Antigua and Barbuda',
            'Afganistan' => 'Afghanistan',
            'Dominican Rep' => 'Dominican Republic',
            'Micronesia (Fed. States of)' => 'Micronesia',
            'Guinée' => 'Guinea',
            'Senagal' => 'Senegal',
            'Cap Verde' => 'Cape Verde',
            'Congo DR' => 'Democratic Republic of the Congo',
            'Bosnia' => 'Bosnia and Herzegovina',
            // Case mistake
            'ANGOLA' => 'Angola',
            'Saint lucia' => 'Saint Lucia',
        );

        // Some files have a buggy self-referencing formula, so we need to fallback on cached result of formula
        $countryName = $this->getCalculatedValueSafely($countryCell);

        // Apply mapping if any
        $countryName = trim(@$countryNameMapping[$countryName] ? : $countryName);

        // Skip questionnaire if there is no country name
        if (!$countryName) {
            return null;
        }


        $countryRepository = $this->getEntityManager()->getRepository('Application\Model\Country');
        $country = $countryRepository->findOneBy(array('name' => $countryName));
        if (!$country) {
            throw new \Exception('No country found for name "' . $countryName . '"');
        }
        $geoname = $country->getGeoname();


        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        $questionnaire = $questionnaireRepository->findOneBy(array(
            'survey' => $survey,
            'geoname' => $geoname,
        ));

        if (!$questionnaire) {
            $questionnaire = new \Application\Model\Questionnaire();
            $questionnaire->setSurvey($survey);
            $questionnaire->setDateObservationStart(new \DateTime($survey->getYear() . '-01-01'));
            $questionnaire->setDateObservationEnd(new \DateTime($survey->getYear() . '-12-31T23:59:59'));
            $questionnaire->setGeoname($geoname);

            $this->getEntityManager()->persist($questionnaire);
            $this->questionnaireCount++;
        }

        return $questionnaire;
    }

    /**
     * Some files have a buggy self-referencing formula, so we need to fallback on cached result of formula
     * @param \PHPExcel_Cell $cell
     * @return type
     * @throws \Application\Service\Importer\PHPExcel_Exception
     */
    private function getCalculatedValueSafely(\PHPExcel_Cell $cell)
    {
        try {
            return $cell->getCalculatedValue();
        } catch (\PHPExcel_Exception $exception) {

            // Fallback on cached result
            if (strstr($exception->getMessage(), 'Formula Error') !== false) {
                return $cell->getOldCalculatedValue();
            } else {
                var_dump($cell->getValue());
                // Forward exception
                throw $exception;
            }
        }
    }

    /**
     * Returns an alternate category linked to the official either from database, or newly created
     * @param string $name
     * @param \Application\Model\Category $officialCategory
     * @return \Application\Model\Category
     */
    protected function getAlternateCategory($name, \Application\Model\Category $officialCategory)
    {
        if ($name == $officialCategory->getName())
            return $officialCategory;

        $key = $name . '::' . $officialCategory->getName();
        if (array_key_exists($key, $this->cacheAlternateCategories)) {
            $category = $this->cacheAlternateCategories[$key];
        } else {
            $this->getEntityManager()->flush();
            $categoryRepository = $this->getEntityManager()->getRepository('Application\Model\Category');
            $criteria = array(
                'name' => $name,
                'officialCategory' => $officialCategory,
            );
            $category = $categoryRepository->findOneBy($criteria);
        }

        if (!$category) {
            $category = new \Application\Model\Category();
            $this->getEntityManager()->persist($category);
            $category->setName($name);
            $category->setOfficialCategory($officialCategory);
        }

        $this->cacheAlternateCategories[$key] = $category;

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

    private $cacheRules = array();

    public function importRules(\PHPExcel_Worksheet $sheet, $col)
    {
        $range = $this->officialCategoriesDefinition[$sheet->getTitle()]['estimations'];

        for ($row = $range['min']; $row <= $range['max']; $row++) {
            $ruleName = $this->getCalculatedValueSafely($sheet->getCellByColumnAndRow($col + 1, $row));
            $ruleFormula = $sheet->getCellByColumnAndRow($col + 3, $row)->getValue();
            if ($ruleName) {
                if (!array_key_exists($ruleName, $this->cacheRules)) {
                    $this->cacheRules[$ruleName] = $ruleFormula;
//                    var_dump(array($ruleName, $ruleFormula));
                }
            }
        }
    }

    public function importFilterComponents($name, array $filterComponents)
    {
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        $filter = $filterRepository->getOrCreate($name);
        $this->getEntityManager()->flush();

        // Get or create all filterComponent
        foreach ($filterComponents as $name => $categories) {

            $filterComponent = null;
            foreach ($filter->getCategoryFilterComponents() as $f) {
                if ($f->getName() == $name) {
                    $filterComponent = $f;
                    break;
                }
            }

            if (!$filterComponent) {
                $filterComponent = new \Application\Model\CategoryFilterComponent($name);
                $filter->addCategoryFilterComponent($filterComponent);
                $this->getEntityManager()->persist($filterComponent);
            }

            // Affect categories
            foreach ($categories as $category) {
                $filterComponent->addCategory($this->cacheCategories[$category]);
            }

            $this->cacheFilterComponents[$name] = $filterComponent;
        }
    }

    /**
     * Import exclude rules based on Yes/No content on cells
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param \Application\Model\Questionnaire $questionnaire
     */
    public function importExcludes(\PHPExcel_Worksheet $sheet, $col, \Application\Model\Questionnaire $questionnaire)
    {
        $parts = array(
            $col + 3 => $this->partUrban,
            $col + 4 => $this->partRural,
            $col + 5 => null, // total is not a part
        );

        $repository = $this->getEntityManager()->getRepository('Application\Model\Rule\CategoryFilterComponentRule');

        foreach ($this->officialCategoriesDefinition[$sheet->getTitle()]['excludes'] as $row => $filterComponentName) {
            $categoryFilterComponent = $this->cacheFilterComponents[$filterComponentName];

            foreach ($parts as $c => $part) {
                $includedValue = $this->getCalculatedValueSafely($sheet->getCellByColumnAndRow($c, $row));

                // If it is not included, then it means we need an exclude rule
                if ($includedValue == 'No') {
                    $assoc = $repository->findOneBy(array(
                        'questionnaire' => $questionnaire,
                        'rule' => $this->excludeRule,
                        'part' => $part,
                        'categoryFilterComponent' => $categoryFilterComponent,
                    ));

                    // If doesn't exist yet, create it
                    if (!$assoc) {
                        $assoc = new \Application\Model\Rule\CategoryFilterComponentRule();
                        $assoc->setQuestionnaire($questionnaire)
                                ->setRule($this->excludeRule)
                                ->setPart($part)
                                ->setCategoryFilterComponent($categoryFilterComponent);

                        $this->getEntityManager()->persist($assoc);
                        $this->excludesCount++;
                    }
                }
            }
        }
    }

}
