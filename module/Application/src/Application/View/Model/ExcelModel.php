<?php

namespace Application\View\Model;

use Zend\View\Model\ViewModel;

class ExcelModel extends ViewModel
{

    private $filename;

    public function __construct($filename, $variables = null, $options = null)
    {
        $this->filename = $filename;
        parent::__construct($variables, $options);
    }

    public function getFilename()
    {
        return $this->filename;
    }

}
