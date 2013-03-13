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
        $this->assertMatchedRouteName('angularjs_layout');
    }

    public function testMatchingRoutes()
    {
        // Homepage should return AngularJS layout, so AngularJS can do its own routing
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('angularjs_layout');
        $this->assertQuery('html > head');

        // Idem for any URL 
        $this->dispatch('/anything/foo/bar');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('angularjs_layout');
        $this->assertQuery('html > head');

        // Idem for any URL with params
        $this->dispatch('/anything/foo/bar?param=value');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('angularjs_layout');
        $this->assertQuery('html > head');

        // Cannot hit application/default route, everything is always catched by angularjs_layout
        $this->dispatch('/application/index/about');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('angularjs_layout');
        $this->assertQuery('html > head');

        // Template URL should return partial HTML fragment for AngularJS template system via ajax
        $this->dispatch('/template/application/index/about');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('template/default');
        $this->assertNotQuery('html > head');
    }

    public function testAssemblingRoutes()
    {
        $router = $this->getApplicationServiceLocator()->get('Router');

        // Home URL
        $this->assertEquals('/', $router->assemble(array(), array('name' => 'home')), 'should return homepage url');
        $this->assertEquals('/', $router->assemble(array('p' => 'v'), array('name' => 'home')), 'should return homepage url without params');

        // Standard URL
        $this->assertEquals('/application', $router->assemble(array(), array('name' => 'application')), 'should return standard URL');
        $this->assertEquals('/application/', $router->assemble(array(), array('name' => 'application/default')), 'should return standard URL');
        $this->assertEquals('/application/index/about', $router->assemble(array('controller' => 'index', 'action' => 'about'), array('name' => 'application/default')), 'should return standard URL to specified controller/action');

        // Template URL
        $this->assertEquals('/template/application', $router->assemble(array(), array('name' => 'template')), 'should return template URL');
        $this->assertEquals('/template/application/', $router->assemble(array(), array('name' => 'template/default')), 'should return template URL');
        $this->assertEquals('/template/application/index/about', $router->assemble(array('controller' => 'index', 'action' => 'about'), array('name' => 'template/default')), 'should return template URL to specified controller/action');
    }

}
