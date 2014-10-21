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

        $colIso3 = 2;
        $rowYear = 1;
        $importedValueCount = 0;
        $row = $rowYear + 1;
        while ($countryIso3 = $totalSheet->getCellByColumnAndRow($colIso3, $row)->getValue()) {
            if (($totalSheet->getCellByColumnAndRow($colIso3, $row)->getValue() != $urbanSheet->getCellByColumnAndRow($colIso3, $row)->getValue()) ||
                    ($urbanSheet->getCellByColumnAndRow($colIso3, $row)->getValue() != $ruralSheet->getCellByColumnAndRow($colIso3, $row)->getValue())) {
                throw new \Exception("Country ISO3 is different in one on the three file at [$colIso3, $row]");
            }

            $geoname = $geonameRepository->findOneBy(array('iso3' => $countryIso3));
            if ($geoname) {
                echo 'Country: ' . $geoname->getName() . PHP_EOL;
                $col = $colIso3 + 1;
                while ($year = $totalSheet->getCellByColumnAndRow($col, $rowYear)->getCalculatedValue()) {
                    if (($totalSheet->getCellByColumnAndRow($col, $rowYear)->getValue() != $urbanSheet->getCellByColumnAndRow($col, $rowYear)->getValue()) ||
                            ($urbanSheet->getCellByColumnAndRow($col, $rowYear)->getValue() != $ruralSheet->getCellByColumnAndRow($col, $rowYear)->getValue())) {
                        throw new \Exception("Year is different in one on the three file at [$col, $rowYear]");
                    }

                    $urban = $urbanSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                    $rural = $ruralSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                    $total = $totalSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();

                    $this->getPopulation($year, $geoname, $this->partUrban)->setPopulation((int) ($urban * 1000));
                    $this->getPopulation($year, $geoname, $this->partRural)->setPopulation((int) ($rural * 1000));
                    $this->getPopulation($year, $geoname, $this->partTotal)->setPopulation((int) ($total * 1000));

                    $col++;
                    $importedValueCount += 3;
                }
            }

            $row++;
        }

        echo "Flushing $importedValueCount population data in database..." . PHP_EOL;

        $this->getEntityManager()->flush();

        echo "Compute absolute value, based on population..." . PHP_EOL;
        $answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        $answerRepository->completePopulationAnswer();

        return "$importedValueCount population data imported" . PHP_EOL;
    }

    /**
     * Returns a Population either from database, or newly created
     * @param integer $year
     * @param \Application\Model\Geoname $geoname
     * @return \Application\Model\Population
     */
    protected function getPopulation($year, \Application\Model\Geoname $geoname, \Application\Model\Part $part)
    {
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        $population = $populationRepository->findOneBy(array(
            'year' => $year,
            'geoname' => $geoname,
            'part' => $part,
        ));

        if (!$population) {

            $population = new \Application\Model\Population();
            $this->getEntityManager()->persist($population);
            $population->setYear($year);
            $population->setGeoname($geoname);
            $population->setPart($part);
        }

        return $population;
    }

}
