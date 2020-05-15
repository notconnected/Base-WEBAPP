<?php
namespace Modules;

class Menu extends AbstractModule
{
    protected $menu = [];
    
    public function getMenuForCurrentUser()
    {
        $menu = [];
        foreach ($this->menu as $menu_item) {
            if ($this->checkPrivileges($menu_item['action'])) {
                array_push($menu, $menu_item);
            }
        }
        return $menu;
    }
    
    public function addMenuItem($item_title, $item_url, $action_name)
    {
        if (empty($item_title)) {
            throw new \Exception('Menu item title required');
        }
        if (empty($item_url)) {
            throw new \Exception('Menu item url required');
        }
        if (empty($action_name)) {
            throw new \Exception('Menu item action required');
        }
        array_push($this->menu, [
            'title' => strip_tags($item_title),
            'url' => $item_url,
            'action' => $action_name
        ]);
        return true;
    }

    private function checkPrivileges($action_name)
    {
        if (!$this->getAuth()->isLogged()) {
            return false;
        }
        if (!$this->getAuth()->checkPrivilegesByActionName($action_name)) {
            return false;
        }
        return true;
    }
}
