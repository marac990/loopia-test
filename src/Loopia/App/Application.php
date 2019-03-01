<?php
/**
 * Created by PhpStorm.
 * User: marko
 * Date: 1.3.19.
 * Time: 11.09
 */

namespace Loopia\App;

use Auryn\InjectionException;
use Auryn\Injector;
use Http\HttpRequest;
use Http\HttpResponse;
use Http\MissingRequestMetaVariableException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareTrait;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Application
{

    use LoggerAwareTrait;

    /**
     * @var $_injector Injector
     */
    protected $_injector;
    /**
     * @var array
     */
    protected $_settings;
    /**
     * @var $_request HttpRequest
     */
    protected $_request;
    /**
     * @var $_response HttpResponse
     */
    protected $_response;
    /**
     * @var Logger $_logger
     */
    protected $_logger;

    public function __construct(array $settings)
    {
        $this->_settings = $settings;
    }

    public function run(): void
    {
        $this->loadDependencies();
        $this->initRequestAndResponse();
        $this->initLogger();
        $routeDefinitionCallback = $this->initRoutes();

        $dispatcher = simpleDispatcher($routeDefinitionCallback);

        $routeInfo = $dispatcher->dispatch($this->_request->getMethod(), $this->_request->getPath());
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $this->_response->setContent('404 - Page not found');
                $this->_response->setStatusCode(404);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $this->_response->setContent('405 - Method not allowed');
                $this->_response->setStatusCode(405);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $className = $routeInfo[1][0];
                $method = $routeInfo[1][1];
                $vars = $routeInfo[2];
                $parsedVars = [];
                //add ":" so that auryn DI will know that we are passing param, not a class name
                foreach ($vars as $key => $val)
                    $parsedVars[":" . $key] = $val;
                try {
                    $class = $this->_injector->make($className);
                    $this->_injector->execute([$class, $method], $parsedVars);
                } catch (InjectionException $e) {
                    $this->_logger->error($e->getMessage() . ' on line: ' . $e->getLine());
                }
                break;
        }
        $this->sendResponse();
    }

    private function loadDependencies(): void
    {
        $this->_injector = include($this->_settings['paths.root'] . '/src/Dependencies.php');
    }

    private function initRequestAndResponse()
    {
        $this->_request = $this->_injector->make('Http\Request');
        $this->_response = $this->_injector->make('Http\Response');
    }

    private function initRoutes()
    {
        return function (RouteCollector $r) {
            $routes = include($this->_settings['paths.root'] . '/src/routes.php');
            foreach ($routes as $route) {
                $r->addRoute($route[0], $route[1], $route[2]);
            }
        };
    }

    private function initLogger()
    {
        if (!file_exists($this->_settings['paths.root'] . '/var')) {
            mkdir($this->_settings['paths.root'] . '/var', 0777);
        }

        if (!file_exists($this->_settings['paths.root'] . '/var/log')) {
            mkdir($this->_settings['paths.root'] . '/var/log', 0777);
        }


        $handler = $this->_injector->make(StreamHandler::class, [$this->_settings['paths.root'] . '/var/log/application.log', $this->_settings['logger.min_level']]);
        $logger = $this->_injector->make(Logger::class, ['Application']);
        $logger = $logger->pushHandler($handler);

        $this->_logger = $logger;
        $this->_injector->share($logger);

    }

    private function sendResponse()
    {
        foreach ($this->_response->getHeaders() as $header) {
            header($header);
        }
        echo $this->_response->getContent();
    }
}