<?php
namespace Modules;

class Render extends AbstractModule
{    
    protected $twig;
    
    public function __construct(\Core\Application $application)
    {
        parent::__construct($application);
        
        $loader = new \Twig\Loader\FilesystemLoader(APP_PATH.'/templates');
        $loader->addPath(APP_PATH.'/templates/admin', 'admin');
        $loader->addPath(APP_PATH.'/templates/admin/forms', 'admin_forms');
        $loader->addPath(APP_PATH.'/templates/checkout', 'checkout');
            $this->twig = new \Twig\Environment($loader, [
                'cache' => APP_PATH.'/cache',
            ]);
    }
    
    public function render($template, $vars = [])
    {
        $template = $this->twig->load($template);
        return $template->render(array_merge($this->getTemplateVars(), $vars));
    }
      
    public function getTemplateVars()
    {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return [
                'base_url' => $this->getApplication()->getRouter()->getBaseUrl(),
                '__authorized' => $_SESSION['__authorized'],
                '__user_name' => $_SESSION['__user_name'],
                'message' => $message,
                'menu' => $this->getMenu()->getMenuForCurrentUser()
            ];
    }

}
