<?php
namespace Core;

class Application
{
    protected $router;
    protected $entityManager;
    protected $queryBuilder;
    protected $output;
    protected $logger;
    
    //modules
    protected $auth, $menu, $twig, $land;

    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->initRouter();
        $this->initOutput();
        $this->initLogger();
        $this->entityManager = $entityManager;
    }
    
    public function run()
    {
        if (empty($this->getRouter()->getControllerName())) {
            $this->getOutput()->send('index'); //Для мониторинга
            
            /**
       *  Logging
       */
            $this->getLogger()->log('info', 'Index requested');
            exit(1);
        }
        
        $controllerName = "\Controllers\\".ucfirst($this->getRouter()->getControllerName());
        $method = $this->getRouter()->getMethod();
        
        if (class_exists($controllerName) && $controllerName != "\Controllers\AbstractController") {
            $controller = new $controllerName($this);
            
            $actionName = $this->getRouter()->getSecondParameter();

            switch ($method) {
                case 'GET': # /resource/action/id
                    if (!$actionName) {
                        $actionName = 'getActionIndex';
                    } else {
                        $actionName = 'getAction'.ucfirst($actionName); // getAction...
                    }
                    break;

                case 'POST': # /resource/action/id
                    if (!$actionName) {
                        $actionName = 'postActionIndex';
                    } else {
                        $actionName = 'postAction'.ucfirst($actionName); // postAction...
                    }
                    break;
                
                case 'PUT': # /resource/action/id
                    if (!$actionName) {
                        $actionName = 'putActionIndex';
                    } else {
                        $actionName = 'putAction'.ucfirst($actionName); // putAction...
                    }
                    break;
                
                case 'DELETE': #/resource/action/id
                    if (!$actionName) {
                        $actionName = 'deleteActionIndex';
                    } else {
                        $actionName = 'deleteAction'.ucfirst($actionName); // deleteAction...
                    }
                    break;
                    
                default:
                    
                         /**
             *  Logging
             */
                        $this->getLogger()->log('error', 'HTTP Method error',
                                'Requested HTTP method: '.$method
                                .PHP_EOL
                                .'Requested controller: '.$controllerName
                                .PHP_EOL
                                .'Requested method: '.strtolower($method).'Action'.ucfirst($actionName)
                        );
                    
                        throw new \Exception('HTTP Method error', 404);
                    break;
            }

            if (method_exists($controller, $actionName)) {
                
                /**
         *  Logging
         */
                $this->getLogger()->log('debug', 'Executing APP',
                        'Requested HTTP method: '.$method
                        .PHP_EOL
                        .'Requested controller: '.$controllerName
                        .PHP_EOL
                        .'Requested method: '.$actionName
                        .PHP_EOL
                        .'Data: '. var_export($this->getRouter()->getRequestData(), true)
                );
                
                $controller->$actionName();
            } else {
                
                /**
         *  Logging
         */
                $this->getLogger()->log('error', 'Controller method(action) not found',
                        'Requested controller: '.$controllerName
                        .PHP_EOL
                        .'Requested method: '.$actionName
                );
                
                throw new \Exception('Controller method error', 404);
            }
        } else {
            
            /**
       *  Logging
       */
            $this->getLogger()->log('error', 'Controller not found',
                    'Requested controller: '.$controllerName
            );
            
            throw new \Exception('Controller error', 404);
        }
    }

    private function initRouter()
    {
        $this->router = new Router();
    }
    
    private function initOutput()
    {
        $this->output = new Output($this);
    }
    
    private function initLogger()
    {
        $this->logger = new Logger();
    }
    
    public function getRouter()
    {
        return $this->router;
    }
    
    public function getOutput()
    {
        return $this->output;
    }
    
    public function getEntityManager()
    {
        return $this->entityManager;
    }
      
    public function getAuth()
    {
        if (!is_a($this->auth, '\Modules\Auth')) {
            $this->auth = new \Modules\Auth($this);
        }
        return $this->auth;
    }

    public function getLogger()
    {
        return $this->logger;
    }
    
    public function getRender()
    {
        if (!is_a($this->render, '\Modules\Render')) {
            $this->render = new \Modules\Render($this);
        }
        return $this->render;
    }
    
    public function getLand()
    {
        if (!is_a($this->land, '\Modules\Land')) {
            $this->land = new \Modules\Land($this);
        }
        return $this->land;
    }
    
    public function getMenu()
    {
        if (!is_a($this->menu, '\Modules\Menu')) {
            $this->menu = new \Modules\Menu($this);
        }
        return $this->menu;
    }
    
    public function getQueryBuilder()
    {
        if (!is_a($this->queryBuilder, '\Doctrine\ORM\QueryBuilder')) {
            $this->queryBuilder = $this->getEntityManager()->createQueryBuilder();
        }
        return $this->queryBuilder;
    }
}

