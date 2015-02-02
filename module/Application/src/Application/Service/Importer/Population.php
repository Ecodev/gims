<?php

namespace Application\Service\Importer;

class Population extends AbstractImporter
{

    /**
     * @var \Application\Model\Part
     */
    private $partUrban;

    /**
     * @var \Application\Model\Part
     */
    private $partRural;

    /**
     * @var \Application\Model\Part
     */
    private $partTotal;

    /**
     * Import populaion data from three files (urban, rural, total)
     * @param string $filename
     * @return string
     */
    public function import($filename)
    {
        echo "Reading files..." . PHP_EOL;

        $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($filename);

        $urbanSheet = $workbook->getSheet(1);
        $ruralSheet = $workbook->getSheet(2);
        $totalSheet = $workbook->getSheet(0);

        $this->partUrban = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Urban');
        $this->partRural = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Rural');
        $this->partTotal = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Total');
        $this->getEntityManager()->flush(); // Flush to be sure that parts have ID

        $geonameRepository = $this->getEntityManager()->getRepository('Application\Model\Geoname');
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');

        $colIso3 = 2;
        $rowYear = 1;
        $importedValueCount = 0;
        $row = $rowYear + 1;
        while ($countryIso3 = $totalSheet->getCellByColumnAndRow($colIso3, $row)->getValue()) {
            if (($totalSheet->getCellByColumnAndRow($colIso3, $row)->getValue() != $urbanSheet->getCellByColumnAndRow($colIso3, $row)->getValue()) ||
                    ($urbanSheet->getCellByColumnAndRow($colIso3, $row)->getValue() != $ruralSheet->getCellByColumnAndRow($colIso3, $row)->getValue())) {
                throw new \Exception("Country ISO3 is different in one on the three file at [$colIso3, $row]");
            }

            $geoname = $geonameRepository->findOneBy(['iso3' => $countryIso3]);
            if ($geoname) {
                echo $geoname->getName() . PHP_EOL;
                $col = $colIso3 + 1;
                while ($year = $totalSheet->getCellByColumnAndRow($col, $rowYear)->getCalculatedValue()) {
                    if (($totalSheet->getCellByColumnAndRow($col, $rowYear)->getValue() != $urbanSheet->getCellByColumnAndRow($col, $rowYear)->getValue()) ||
                            ($urbanSheet->getCellByColumnAndRow($col, $rowYear)->getValue() != $ruralSheet->getCellByColumnAndRow($col, $rowYear)->getValue())) {
                        throw new \Exception("Year is different in one on the three file at [$col, $rowYear]");
                    }

                    // Only import years that are useful for us
                    if ($year >= 1980 && $year <= 2020) {
                        $urban = $urbanSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                        $rural = $ruralSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                        $total = $totalSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();

                        $populationRepository->updateOrCreate($geoname, $this->partUrban, $year, (int) ($urban * 1000));
                        $populationRepository->updateOrCreate($geoname, $this->partRural, $year, (int) ($rural * 1000));
                        $populationRepository->updateOrCreate($geoname, $this->partTotal, $year, (int) ($total * 1000));
                        $importedValueCount += 3;
                    }
                    $col++;
                }
            } else {
                echo PHP_EOL . "WARNING: no country found in database for ISO3: $countryIso3" . PHP_EOL . PHP_EOL;
            }

            $row++;
        }

        echo "Flushing $importedValueCount population data in database..." . PHP_EOL;

        $this->getEntityManager()->flush();

        echo "Compute population for regions..." . PHP_EOL;
        $geonameRepository->computeAllPopulation();

        echo "Compute absolute value, based on population..." . PHP_EOL;
        $answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        $answerRepository->completePopulationAnswer();

        return "$importedValueCount population data imported" . PHP_EOL;
    }
}
