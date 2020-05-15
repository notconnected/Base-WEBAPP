<?php
namespace Controllers;

class Admin extends AbstractController
{
    public function __construct(\Core\Application $application)
    {
        parent::__construct($application);
        //Слишком мало пунктов меню и редко будут меняться, 
        //чтобы создавать зависимости и хранить в базе
        $this->getMenu()->addMenuItem(_('Home'), '/admin/', 'Index');
        $this->getMenu()->addMenuItem(_('Users'), '/admin/users/', 'Users');
        $this->getMenu()->addMenuItem(_('Groups'), '/admin/groups/', 'Groups');
        $this->getMenu()->addMenuItem(_('Actions'), '/admin/actions/', 'Actions');
        $this->getMenu()->addMenuItem(_('Lands'), '/admin/lands/', 'Lands');
    }
    
    public function getActionIndex() // Главная административной части
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__, false, true);
        
        $output = $this->getRender()->render('@admin/index.html', [
            'the' => 'var',
            'go' => 'here'
            ]);
            $this->getOutput()->send($output, false);
    }
    
    /**
     * Группы
     */
    
    public function getActionGroups()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render(
                '@admin/groups.html',
                ['groups' => $this->getAuth()->getGroupsForTemplate()]
                );
        $this->getOutput()->send($output, false);
    }
    
    public function getActionAddGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render('@admin_forms/add_group.html');
        $this->getOutput()->send($output, false);
    }
    
    public function postActionAddGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->addGroup($this->request_data['name'], $this->request_data['description']);
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/groups/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/groups/');
        }
    }
    
    public function getActionEditGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $group_data = $this->getAuth()->getGroupForTemplate($this->getRouter()->getThirdParameter());
        if (is_array($group_data)) {
            $output = $this->getRender()->render('@admin_forms/add_group.html', [
                    'group' => $group_data
                ]);
            $this->getOutput()->send($output, false);
        } else {
            $_SESSION['message'] = $group_data;
            $_SESSION['redirect_url'] = '/admin/groups/';
            $this->getOutput()->sendRedirect('/admin/error/');
        }
    }
    
    public function postActionEditGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->editGroup(
                $this->getRouter()->getThirdParameter(),
                $this->request_data['name'], 
                $this->request_data['description']
            );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/groups/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/groups/');
        }
    }
    
    public function getActionDeleteGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->deleteGroup($this->getRouter()->getThirdParameter());
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/groups/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/groups/');
        }
    }
    
    /**
    * Привилегии для групп
    */
        
    public function getActionAddGroupPrivileges()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render('@admin_forms/add_group_privileges.html',
                ['groups' => $this->getAuth()->getGroupsForTemplate(),
                    'actions' => $this->getAuth()->getActionsForTemplate()
            ]);
        $this->getOutput()->send($output, false);
    }
    
    public function postActionAddGroupPrivileges()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->addGroupPrivileges(
                $this->request_data['group_id'], 
                $this->request_data['action_id']
            );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/groups/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/groups/');
        }
    }
        
    public function getActionEditGroupPrivileges()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render('@admin_forms/edit_group_privileges.html',[
                    'current_group' => $this->getRouter()->getThirdParameter(),
                    'groups' => $this->getAuth()->getGroupsForTemplate(),
                    'actions' => $this->getAuth()->getActionsForTemplateByGroupId($this->getRouter()->getThirdParameter())
            ]);
        $this->getOutput()->send($output, false);
    }
    
    public function postActionEditGroupPrivileges()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->editGroupPrivileges(
                $this->request_data['group_id'], 
                array_keys($this->request_data['actions'])
            );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/groups/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/groups/');
        }
    }
    
    public function getActionDeleteGroupPrivileges()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->deleteGroupPrivileges($this->getRouter()->getThirdParameter());
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/groups/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/groups/');
        }
    }
    
    /**
    * Пользователи
    */
    
    public function getActionLogin()
    {
        $output = $this->getRender()->render('@admin_forms/login.html');
        $this->getOutput()->send($output, false);       
    }
    
    public function postActionLogin()
    {
        $output = $this->getAuth()->login(
                    $this->request_data['email'],
                    $this->request_data['password']
                );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $this->getOutput()->sendRedirect('/admin/');
        }
    }
    
    public function getActionLogout()
    {
        $this->getAuth()->logout();
        $this->getOutput()->sendRedirect('/admin/login/');
    }

    public function getActionUsers()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render(
                '@admin/users.html',
                ['users' => $this->getAuth()->getUsersForTemplate()]
                );
        $this->getOutput()->send($output, false);
    }

    public function getActionAddUser()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render('@admin_forms/add_user.html');
        $this->getOutput()->send($output, false);
    }
    
    public function postActionAddUser()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->addUser(
                    $this->request_data['email'],
                    $this->request_data['password'], 
                    $this->request_data['first_name'], 
                    $this->request_data['last_name'], 
                    $this->request_data['description']
                );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/users/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/users/');
        }
    }

    public function getActionChangePassword()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__, false, true);
        $output = $this->getRender()->render('@admin_forms/change_password.html');
        $this->getOutput()->send($output, false);
    }
    
    public function postActionChangePassword()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__, false, true);
        $user_id = $this->getRouter()->getThirdParameter();
        $user_id = (!empty($user_id))?$user_id:$_SESSION['__user_id'];
        $output = $this->getAuth()->changePassword(
                    $this->request_data['current_password'],
                    $this->request_data['new_password'],
                    $user_id
                );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/');
        }
    }
    
    public function getActionDeleteUser()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->deleteUser($this->getRouter()->getThirdParameter());
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/users/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/users/');
        }
    }
    
    public function getActionAddUserToGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render('@admin_forms/add_user_to_group.html',
                [
                    'users' => $this->getAuth()->getUsersForTemplate(),
                    'groups' => $this->getAuth()->getGroupsForTemplate()
            ]);
        $this->getOutput()->send($output, false);
    }
    
    public function postActionAddUserToGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->AddUserToGroup(
                $this->request_data['user_id'], 
                $this->request_data['group_id']
            );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/users/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/users/');
        }
    }
    
    public function getActionDeleteUserFromGroup()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->deleteUserFromGroup($this->getRouter()->getThirdParameter());
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/users/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/users/');
        }
    }
    
    /**
    * Экшены
    */
    
    public function getActionActions()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render(
                '@admin/actions.html',
                ['actions' => $this->getAuth()->getActionsForTemplate()]
                );
        $this->getOutput()->send($output, false);
    }
    
    public function getActionAddAction()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $actions_names = $this->getAuth()->getNewAdminActions();
        $output = $this->getRender()->render('@admin_forms/add_action.html', [
                'actions' => $actions_names
            ]);
        $this->getOutput()->send($output, false);
    }
    
    public function postActionAddAction()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->addAction(
                    $this->request_data['action_name'],
                    $this->request_data['description']
                );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/actions/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/actions');
        }
    }
    
    public function getActionDeleteAction()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getAuth()->deleteAction($this->getRouter()->getThirdParameter());
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/actions/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/actions/');
        }
    }
    
    /**
    * Ленд
    */

    public function getActionLands()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render(
                '@admin/lands.html',
                ['lands' => $this->getLand()->getLandsForTemplate()]
            );
        $this->getOutput()->send($output, false);
    }
    
    public function getActionAddLand()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getRender()->render('@admin_forms/add_land.html');
        $this->getOutput()->send($output, false);
    }
    
    public function postActionAddLand()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getLand()->addLand(
                    $this->request_data['name'],
                    $this->request_data['url'],
                    $this->request_data['description'],
                    (bool) $this->request_data['is_active']
                );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/lands/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/lands');
        }
    }
    
    public function getActionEditLand()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $land_data = $this->getLand()->getLandForTemplate($this->getRouter()->getThirdParameter());
        if (is_array($land_data)) {
            $output = $this->getRender()->render('@admin_forms/add_land.html', [
                    'land' => $land_data
                ]);
            $this->getOutput()->send($output, false);
        } else {
            $_SESSION['message'] = $land_data;
            $_SESSION['redirect_url'] = '/admin/lands/';
            $this->getOutput()->sendRedirect('/admin/error/');
        }
    }
    
    public function postActionEditLand()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getLand()->editLand(
                    $this->getRouter()->getThirdParameter(),
                    $this->request_data['name'],
                    $this->request_data['url'],
                    $this->request_data['description'],
                    (bool) $this->request_data['is_active']
                );
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/lands/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/lands');
        }
    }

    public function getActionDeleteLand()
    {
        $this->checkPrivilegesAndDoAction(__FUNCTION__);
        $output = $this->getLand()->deleteLand($this->getRouter()->getThirdParameter());
        if ($output !== true) {
            $_SESSION['message'] = $output;
            $_SESSION['redirect_url'] = '/admin/lands/';
            $this->getOutput()->sendRedirect('/admin/error/');
        } else {
            $_SESSION['message'] = _('Successful');
            $this->getOutput()->sendRedirect('/admin/lands/');
        }
    }


    /**
    * Общие
    */
    
    public function getActionError()
    {
        //Обновление страницы ошибки
        if (empty($_SESSION['message'])) {
            if (empty($_SESSION['redirect_url'])) {
                $this->getOutput()->sendRedirect('/admin/');
            } else {
                $this->getOutput()->sendRedirect($_SESSION['redirect_url']);
            }
        }
        //Отрисовка страницы ошибки
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        $output = $this->getRender()->render('@admin/error.html', [
                'message' => $message
            ]);
        $this->getOutput()->send($output, false);
    }
    
    private function checkPrivilegesAndDoAction($action_name, $show_error = false, $check_only_islogged = false) // 
    {
        if (!$this->getAuth()->isLogged()) {
            $this->getOutput()->sendRedirect('/admin/login/');
        }
        if ($check_only_islogged) {
            return;
        }
        if (!$this->getAuth()->checkPrivilegesByActionName($action_name)) {
            if ($show_error) {
                $_SESSION['message'] = _('Access denied');
                $this->getOutput()->sendRedirect('/admin/error/');
            } else {
                $this->getOutput()->sendRedirect('/admin/');
            }
        }
    }
    
}
