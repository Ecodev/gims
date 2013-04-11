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
        $this->assertMatchedRouteName('template_application/default');
        $this->assertNotQuery('html > head');

        // Template URL should return partial HTML fragment for AngularJS template system via ajax for Contribute module
        $this->dispatch('/template/contribute');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('contribute');
        $this->assertControllerName('contribute\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('template_contribute');
        $this->assertNotQuery('html > head');

        // Template URL should return partial HTML fragment for AngularJS template system via ajax for Browse module
        $this->dispatch('/template/browse');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('browse');
        $this->assertControllerName('browse\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('template_browse');
        $this->assertNotQuery('html > head');
    }

    /**
     * @test
     * @dataProvider routeProvider
     */
    public function testRouteFromRouteProvider($module, $route, $template)
    {
        // Template URL should return partial HTML fragment for AngularJS template system via ajax for Contribute module
        $this->dispatch('/template/' . $route);
        $this->assertResponseStatusCode(200);
        $this->assertModuleName($module);
        $this->assertControllerName($module . '\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName($template);
        $this->assertNotQuery('html > head');
    }

    /**
     * Provider
     */
    public function routeProvider()
    {
        return array(
            //    module    route    template_admin
            array('admin', 'admin', 'template_admin'),
            array('admin', 'admin/survey', 'template_admin/default'),
        );
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
        $this->assertEquals('/template/application', $router->assemble(array(), array('name' => 'template_application')), 'should return template URL');
        $this->assertEquals('/template/application/', $router->assemble(array(), array('name' => 'template_application/default')), 'should return template URL');
        $this->assertEquals('/template/application/index/about', $router->assemble(array('controller' => 'index', 'action' => 'about'), array('name' => 'template_application/default')), 'should return template URL to specified controller/action');

        // Template URL for Contribute module
        $this->assertEquals('/template/contribute', $router->assemble(array(), array('name' => 'template_contribute')), 'should return template URL');
        $this->assertEquals('/template/contribute/', $router->assemble(array(), array('name' => 'template_contribute/default')), 'should return template URL');
        $this->assertEquals('/template/contribute/about', $router->assemble(array('controller' => 'index', 'action' => 'about'), array('name' => 'template_contribute/default')), 'should return template URL to specified controller/action');

        // Template URL for Browse module
        $this->assertEquals('/template/browse', $router->assemble(array(), array('name' => 'template_browse')), 'should return template URL');
        $this->assertEquals('/template/browse/', $router->assemble(array(), array('name' => 'template_browse/default')), 'should return template URL');
        $this->assertEquals('/template/browse/about', $router->assemble(array('controller' => 'index', 'action' => 'about'), array('name' => 'template_browse/default')), 'should return template URL to specified controller/action');
    }

}
