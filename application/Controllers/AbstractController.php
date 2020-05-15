<?php
namespace Controllers;

abstract class AbstractController
{
    protected $application;
    protected $request_data;
    

    public function __construct(\Core\Application $application)
    {
        $this->application = $application;
        
        //Post data
        $this->request_data = $this->getRequestData();
    }
    
    public function getActionIndex()
    {
        $this->getOutput()->send('default', true, 400);
    }
    
    public function postActionIndex()
    {
        $this->getOutput()->send('default', true, 400);
    }
    
    public function putActionIndex()
    {
        $this->getOutput()->send('default', true, 400);
    }
    
    public function deleteActionIndex()
    {
        $this->getOutput()->send('default', true, 400);
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
  
    public function getMenu()
    {
        return $this->getApplication()->getMenu();
    }
    
    public function getQueryBuilder()
    {
        return $this->getApplication()->getQueryBuilder();
    }
}
