<?php

namespace Api;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        // attach the JSON view strategy
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        $view = $locator->get('ZendViewView');
        $strategy = $locator->get('ViewJsonStrategy');
        $view->getEventManager()->attach($strategy, 100);

        // attach a listener to check for errors
        $events = $e->getTarget()->getEventManager();
        $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderError'));
    }

    public function onRenderError($e)
    {
        // must be an error
        if (!$e->isError()) {
            return;
        }

        // Only do something for HTTP
        $request = $e->getRequest();
        if (!$request instanceof HttpRequest) {
            return;
        }

        // if we have a JsonModel in the result, then do nothing
        $currentModel = $e->getResult();
        if ($currentModel instanceof JsonModel) {
            return;
        }

        // create a new JsonModel - use application/api-problem+json fields.
        $response = $e->getResponse();
        $json = new JsonModel(array(
            'status' => $response->getStatusCode(),
            'title' => $response->getReasonPhrase(),
        ));

        // Detect common errors
        if ($currentModel instanceof ModelInterface && $currentModel->reason) {
            switch ($currentModel->reason) {
                case \Zend\Mvc\Application::ERROR_CONTROLLER_CANNOT_DISPATCH:
                    $json->detail = 'The requested controller was unable to dispatch the request.';
                    break;
                case \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND:
                    $json->detail = 'The requested controller could not be mapped to an existing controller class.';
                    break;
                case \Zend\Mvc\Application::ERROR_CONTROLLER_INVALID:
                    $json->detail = 'The requested controller was not dispatchable.';
                    break;
                case \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH:
                    $json->detail = 'The requested URL could not be matched by routing.';
                    break;
                default:
                    $json->detail = $currentModel->message;
                    break;
            }
        }

        // Find out what the exception is
        $exception = $currentModel->getVariable('exception');
        if ($exception) {
            if ($exception->getCode()) {
                $e->getResponse()->setStatusCode($exception->getCode());
            }
            $json->detail = $exception->getMessage();

            if ($exception instanceof \Application\Validator\Exception) {
                $e->getResponse()->setStatusCode(403);
                $json->status = 403;
                $json->title = 'Object is not valid';
                $json->messages = $exception->getMessages();
            }
        }

        // set our new view model
        $json->setTerminal(true);
        $e->setResult($json);
        $e->setViewModel($json);
    }

}
