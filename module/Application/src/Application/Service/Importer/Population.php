<?php

namespace Application\Service\Importer;

class Population extends AbstractImporter
{

    /**
     * Import populaion data from three files (urban, rural, total)
     * @param string $urbanFilename
     * @param string $ruralFilename
     * @param string $totalFilename
     * @return string
     */
    public function import($urbanFilename, $ruralFilename, $totalFilename)
    {
        echo "Reading files..." . PHP_EOL;

        $urbanSheet = $this->loadSheet($urbanFilename);
        $ruralSheet = $this->loadSheet($ruralFilename);
        $totalSheet = $this->loadSheet($totalFilename);

        $countryRepository = $this->getEntityManager()->getRepository('Application\Model\Country');

        $colIso = 3;
        $rowYear = 13;
        $importedValueCount = 0;
        $row = $rowYear + 1;
        while ($countryIsoNumeric = $totalSheet->getCellByColumnAndRow($colIso, $row)->getValue()) {

            $country = $countryRepository->findOneBy(array('isoNumeric' => $countryIsoNumeric));
            if ($country) {
                echo 'Country: ' . $country->getName() . PHP_EOL;
                $col = $colIso + 1;
                while ($year = $totalSheet->getCellByColumnAndRow($col, $rowYear)->getCalculatedValue()) {
                    $urban = $urbanSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                    $rural = $ruralSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                    $total = $totalSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();

                    $population = $this->getPopulation($year, $country);
                    $population->setUrban((int)($urban * 1000));
                    $population->setRural((int)($rural * 1000));
                    $population->setTotal((int)($total * 1000));

                    $col++;
                    $importedValueCount++;
                }
            }

            $row++;
        }

        echo "Flushing $importedValueCount population data in database..." . PHP_EOL;

        $this->getEntityManager()->flush();

        return "$importedValueCount population data imported" . PHP_EOL;
    }

    /**
     * Load the first sheet
     * @param string $filename
     * @return \PHPExcel_Worksheet
     */
    protected function loadSheet($filename)
    {
        $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($filename);

        return $workbook->getSheet(0);
    }

    /**
     * Returns a Population either from database, or newly created
     * @param integer $year
     * @param \Application\Model\Country $country
     * @return \Application\Model\Population
     */
    protected function getPopulation($year, \Application\Model\Country $country)
    {
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        $population = $populationRepository->findOneBy(array('year' => $year, 'country' => $country));

        if (!$population) {

            $population = new \Application\Model\Population();
            $this->getEntityManager()->persist($population);
            $population->setYear($year);
            $population->setCountry($country);
        }

        return $population;
    }

}
