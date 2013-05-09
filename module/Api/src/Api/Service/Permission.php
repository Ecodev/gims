<?php

namespace Api\Service;


class Permission
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var MetaModel
     */
    protected $metaModel;

    /**
     * Constructor
     */
    public function __construct($model){
        $this->model = $model;
        $this->metaModel = new MetaModel();
    }

    /**
     * Returns whether a User can access a field
     */
    public function isFieldAllowed($fieldName)
    {
        // @todo create a mechanism
        // class Permission would need to implement ServiceLocatorAwareInterface
        #$rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');

        return true;
    }
}
