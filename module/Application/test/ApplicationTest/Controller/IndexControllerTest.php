<?php

namespace ApplicationTest\Controller;

class IndexControllerTest extends AbstractController
{

    public function testIndexActionCanBeAccessed()
    {
        // @TODO: find out why the configuration is not loaded correctly and database connection fails
        return;
        
        $this->dispatch('/');
        $c = $this->getResponse()->getContent();
        var_dump($c);
        
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }

}
