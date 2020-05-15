<?php
namespace Modules;

abstract class AbstractModule
{
    protected $application;
    

    public function __construct(\Core\Application $application)
    {
        $this->application = $application;
    }
    
    public function getApplication()
    {
        return $this->application;
    }

    public function getRouter()
    {
        return $this->getApplication()->getRouter();
    }

    public function getOutput()
    {
        return $this->getApplication()->getOutput();
    }

    public function getRequestData()
    {
        return $this->getApplication()->getRouter()->getRequestData();
    }
    
    public function getEntityManager()
    {
        return $this->getApplication()->getEntityManager();
    }
    
    public function getAuth()
    {
        return $this->getApplication()->getAuth();
    }
    
    public function getLogger()
    {
        return $this->getApplication()->getLogger();
    }
    
    public function getRender()
    {
        return $this->getApplication()->getRender();
    }
    
    public function getLand()
    {
        return $this->getApplication()->getLand();
    }
    
    public function getQueryBuilder()
    {
        return $this->getApplication()->getQueryBuilder();
    }

    public function getMenu()
    {
        return $this->getApplication()->getMenu();
    }
}
