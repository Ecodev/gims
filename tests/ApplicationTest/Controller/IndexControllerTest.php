<?php

namespace ApplicationTest\Controller;

/**
 * @group Service
 */
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

        // Template URL should return partial HTML fragment for AngularJS template system via ajax for Browse module
        $this->dispatch('/api/user');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('api');
        $this->assertControllerName('api\controller\user');
        $this->assertControllerClass('UserController');
        $this->assertMatchedRouteName('api/users');
        $this->assertNotQuery('html > head');

        // Template URL should return partial HTML fragment for AngularJS template system via ajax for Browse module
        $this->identityProvider->setIdentity(null);
        $this->dispatch('/api/user');
        $this->assertResponseStatusCode(401);
        $this->assertModuleName('api');
        $this->assertControllerName('api\controller\user');
        $this->assertControllerClass('UserController');
        $this->assertMatchedRouteName('api/users');
        $this->assertNotQuery('html > head');
    }

    /**
     * @test
     * @dataProvider moduleProvider
     */
    public function testAngularTemplateCanBeRetrieved($module, $controller, $route, $template)
    {
        // Template URL should return partial HTML fragment for AngularJS template system via ajax for Contribute module
        $this->dispatch($route);
        $this->assertResponseStatusCode(200);
        $this->assertModuleName($module);
        $this->assertControllerName($module . '\controller\\' . $controller);
        $this->assertControllerClass(ucfirst($controller) . 'Controller');
        $this->assertMatchedRouteName($template);
        $this->assertNotQuery('html > head');
    }

    /**
     * moduleProvider
     */
    public function moduleProvider()
    {
        return array(
            //    $module  controller  route            template_admin
            array(
                'application',
                'index',
                '/template/application/index/home',
                'template_application/default'
            ),
            array(
                'admin',
                'survey',
                '/template/admin/survey',
                'template_admin/default'
            ),
            array(
                'admin',
                'survey',
                '/template/admin/survey/crud',
                'template_admin/default'
            ),
            array(
                'admin',
                'user',
                '/template/admin/user',
                'template_admin/default'
            ),
            array(
                'admin',
                'user',
                '/template/admin/user/crud',
                'template_admin/default'
            ),
            array(
                'contribute',
                'index',
                '/template/contribute/glaas',
                'template_contribute/default'
            ),
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
        $this->assertEquals('/application/index/about', $router->assemble(array(
                    'controller' => 'index',
                    'action' => 'about'
                ), array('name' => 'application/default')), 'should return standard URL to specified controller/action');

        // Template URL
        $this->assertEquals('/template/application', $router->assemble(array(), array('name' => 'template_application')), 'should return template URL');
        $this->assertEquals('/template/application/', $router->assemble(array(), array('name' => 'template_application/default')), 'should return template URL');
        $this->assertEquals('/template/application/index/about', $router->assemble(array(
                    'controller' => 'index',
                    'action' => 'about'
                ), array('name' => 'template_application/default')), 'should return template URL to specified controller/action');

        // Template URL for Contribute module
        $this->assertEquals('/template/contribute', $router->assemble(array(), array('name' => 'template_contribute')), 'should return template URL');
        $this->assertEquals('/template/contribute/', $router->assemble(array(), array('name' => 'template_contribute/default')), 'should return template URL');
        $this->assertEquals('/template/contribute/about', $router->assemble(array(
                    'controller' => 'index',
                    'action' => 'about'
                ), array('name' => 'template_contribute/default')), 'should return template URL to specified controller/action');

        // Template URL for Browse module
        $this->assertEquals('/template/browse', $router->assemble(array(), array('name' => 'template_browse')), 'should return template URL');
        $this->assertEquals('/template/browse', $router->assemble(array(), array('name' => 'template_browse/default')), 'should return template URL');
        $this->assertEquals('/template/browse/table/filter', $router->assemble(array(
                    'controller' => 'table',
                    'action' => 'filter'
                ), array('name' => 'template_browse/default')), 'should return template URL to specified controller/action');
    }

}
