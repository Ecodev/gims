<?php

namespace ApplicationTest\Controller;

class IndexControllerTest extends AbstractController
{

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }

}
