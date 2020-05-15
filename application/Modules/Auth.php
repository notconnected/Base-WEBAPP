<?php
namespace Modules;

class Auth extends AbstractModule
{
    /**
    * Пользователи
    */
    
    public function login($email, $password)
    {
        $userEntity = $this->getUserByEmail($email);
        if (!$userEntity) {
            return _('User not found');
        }
        if(password_verify($password, $userEntity->getPassword())) {
            $_SESSION['__authorized'] = true;
            $_SESSION['__user_id'] = $userEntity->getId();
            if (empty($userEntity->getFirstName()) && empty($userEntity->getLastName())) {
                $_SESSION['__user_name'] = $userEntity->getEmail();
            } else {
                $_SESSION['__user_name'] = $userEntity->getFirstName().' '.$userEntity->getLastName();
            }
            return true;
        }
        return false;
    }

    public function logout()
    {
        $_SESSION = [];
    }
    
    public function addUser($email, $password, $first_name, $last_name, $description)
    {
        if (empty($email)) {
            return _('Email is required');
        }
        if (empty($password)) {
            return _('Password is required');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        if (!$this->validateEmail($email)) {
            return _('Invalid email');
        }

        if ($this->getUserByEmail($email)) {
            return _('Email already in use');
        }
        
        $userEntity = new \Entities\User;
        $userEntity->setEmail($email);
        $userEntity->setPassword($hash);
        $userEntity->setFirstName(strip_tags($first_name));
        $userEntity->setLastName(strip_tags($last_name));
        $userEntity->setCreated(new \DateTime());
        $userEntity->setDescription(strip_tags($description));
        $this->getEntityManager()->persist($userEntity);
        $this->getEntityManager()->flush();
        return true;
    }
    
    public function deleteUser($user_id)
    {
        if (empty($user_id)) {
            return _('User not found');
        }
        $userEntity = $this->getEntityManager()->find('\Entities\User', (int)$user_id);
        if ($userEntity) {
            //Удаляем связи с группами
            $user_groups = $this->getEntityManager()
                    ->getRepository('\Entities\UserGroup')
                    ->findBy(['user' => $userEntity]);
            foreach ($user_groups as $userGroupEntity) {
                $this->getEntityManager()->remove($userGroupEntity);
            }
            $this->getEntityManager()->remove($userEntity);
            $this->getEntityManager()->flush();
            return true;
        }
        return _('User not found');
    }
    
    public function changePassword($current_password, $new_password, $user_id, $check_current = true)
    {
        if (empty($user_id)) {
            return _('User not found');
        }
        $userEntity = $this->getEntityManager()->find('\Entities\User', (int)$user_id);
        if(empty($userEntity)) {
            return _('User not found');
        }
        if ($check_current) {
            if(!password_verify($current_password, $userEntity->getPassword())) {
                return _('Current password invalid');
            }
        }
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $userEntity->setPassword($hash);
        $this->getEntityManager()->flush();
        return true;
    }

    public function getUserByEmail($email)
    {
        return $this->getEntityManager()->getRepository('Entities\User')->findOneBy(['email' => $email]);
    }

    protected function validateEmail($email)
    {
       return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function isLogged()
    {
        return $_SESSION['__authorized'] ?? false;
    }
    
    public function getUsersForTemplate()
    {
        $usersEntities = $this->getEntityManager()
                ->getRepository('Entities\User')
                ->findAll();
        $users = [];
        foreach ($usersEntities as $userEntity) {
            array_push($users, [
                    'id' => $userEntity->getId(), 
                    'name' => $userEntity->getFirstName().' '.$userEntity->getLastName(), 
                    'email' => $userEntity->getEmail(),
                    'description' => $userEntity->getDescription(),
                    'created' => $userEntity->getCreated()->format('d-m-Y H:i:s'),
                    'groups' => $this->getUserGroupsForUser($userEntity)
                ]);
        }
        return $users;
    }
    
    public function getUserGroupsForUser($userEntity)
    {
        $groups = [];
        $user_groups = $this->getEntityManager()
                ->getRepository('Entities\UserGroup')
                ->findBy(['user' => $userEntity]);

        foreach ($user_groups as $userGroupEntity) {
            array_push($groups, 
                    [
                        'id' => $userGroupEntity->getId(),
                        'name' => $userGroupEntity->getGroup()->getName()
                    ]
                    );
        }
        return $groups;
    }
    
    public function AddUserToGroup($user_id, $group_id)
    {
        if (empty($user_id)) {
            return _('User not found');
        }
        if (empty($group_id)) {
            return _('Group not found');
        }
        $userGroupEntity = $this->getEntityManager()
                ->getRepository('\Entities\UserGroup')
                ->findOneBy(['user' => $user_id, 'group' => $group_id]);
        if ($userGroupEntity) {
            return true; //уже есть
        }
        $userEntity = $this->getEntityManager()
                ->find('\Entities\User', (int)$user_id);
        if (!$userEntity) {
            return _('User not found');
        }
        $groupEntity = $this->getEntityManager()
                ->find('\Entities\Group', (int)$group_id);
        if (!$groupEntity) {
            return _('Group not found');
        }
        $userGroupEntity = new \Entities\UserGroup;
        $userGroupEntity->setUser($userEntity);
        $userGroupEntity->setGroup($groupEntity);
        $this->getEntityManager()->persist($userGroupEntity);
        $this->getEntityManager()->flush();
        return true;
    }
    
    public function deleteUserFromGroup($user_group_id)
    {
        if (empty($user_group_id)) {
            return _('User privilege not found');
        }
        $userGroupEntity = $this->getEntityManager()
                ->find('\Entities\UserGroup', (int)$user_group_id);
        if ($userGroupEntity) {
            $this->getEntityManager()->remove($userGroupEntity);
            $this->getEntityManager()->flush();
            return true;
        }
        return _('Group privilege not found');
    }
    
    /**
    * Экшены
    */
    
    public function getNewAdminActions()
    {
        $actions = $this->getAllAdminActions();
        $actionsEntities = $this->getEntityManager()
                ->getRepository('Entities\Action')
                ->findAll();

        foreach ($actionsEntities as $action) {
            $key = array_search($action->getName(), $actions);
            unset($actions[$key]);
        }
        return $actions;
    }
    
    public function getAllAdminActions()
    {
        $actions = get_class_methods("\Controllers\Admin");
        $actions = array_filter($actions, function($k) {
                return strpos($k, 'Action');
            });

        $actions = array_map([$this, 'prepareActionName'], $actions);
        
        $actions = array_unique($actions);
        return $actions;
    }
    
    public function getActionsForTemplateByGroupId($group_id)
    {
        $group_id = strip_tags($group_id);
        $actions = [];
        
        if (!empty($group_id)) {
            $actionsEntities = $this->getEntityManager()
                    ->getRepository('Entities\Action')
                    ->findAll();

            foreach ($actionsEntities as $actionEntity) {
                $groupPrivilegeEntity = $this->getEntityManager()
                        ->getRepository('Entities\GroupPrivilege')
                        ->findOneBy(['group' => $group_id, 'action' => $actionEntity]);
                if ($groupPrivilegeEntity) {
                    $checked = true;
                } else {
                    $checked = false;
                }
                array_push($actions, [
                        'id' => $actionEntity->getId(), 
                        'name' => $actionEntity->getName(), 
                        'description' => $actionEntity->getDescription(),
                        'created' => $actionEntity->getCreated()->format('d-m-Y H:i:s'),
                        'checked' => $checked
                    ]);
            }
        }
        return $actions;
    }
    
    public function getActionsForTemplate()
    {
        $actionsEntities = $this->getEntityManager()
                ->getRepository('Entities\Action')
                ->findAll();
        $actions = [];
        foreach ($actionsEntities as $actionEntity) {
            array_push($actions, [
                    'id' => $actionEntity->getId(), 
                    'name' => $actionEntity->getName(), 
                    'description' => $actionEntity->getDescription(),
                    'created' => $actionEntity->getCreated()->format('d-m-Y H:i:s')
                ]);
        }
        return $actions;
    }
    
    public function addAction($action_name, $description)
    {
        $actionEntity = $this->getEntityManager()
                ->getRepository('\Entities\Action')
                ->findOneBy(['name' => $action_name]);
        if ($actionEntity) {
            return _('Action name already exist');
        }
        $actionEntity = new \Entities\Action;
        $actionEntity->setDescription(strip_tags($description));
        $actionEntity->setName(strip_tags($action_name));
        $actionEntity->setCreated(new \DateTime());
        $this->getEntityManager()->persist($actionEntity);
        $this->getEntityManager()->flush();
        return true;
    }
    
    public function deleteAction($action_id)
    {
        if (empty($action_id)) {
            return _('Action not found');
        }
        $actionEntity = $this->getEntityManager()->find('\Entities\Action', (int)$action_id);
        if ($actionEntity) {
            $group_privileges = $this->getEntityManager()
                    ->getRepository('\Entities\GroupPrivilege')
                    ->findBy(['action' => $actionEntity]);
            foreach ($group_privileges as $groupPrivilegeEntity) {
                $this->getEntityManager()->remove($groupPrivilegeEntity);
            }
            $this->getEntityManager()->remove($actionEntity);
            $this->getEntityManager()->flush();
            return true;
        }
        return _('Action not found');
    }
    
    /**
    * Группы
    */
    
    public function addGroup($group_name, $description)
    {
        $group_name = strip_tags($group_name);
        if (empty($group_name)) {
            return _('Group name is required');
        }
        $existing_group = $this->getGroupByName($group_name);
        if ($existing_group) {
            return _('Group name already in use');
        }
        
        $groupEntity = new \Entities\Group;
        $groupEntity->setName($group_name);
        $groupEntity->setCreated(new \DateTime());
        $groupEntity->setDescription(strip_tags($description));
        $this->getEntityManager()->persist($groupEntity);
        $this->getEntityManager()->flush();
        return true;
    }
    
    public function deleteGroup($group_id)
    {
        if (empty($group_id)) {
            return _('Group not found');
        }
        $groupEntity = $this->getEntityManager()->find('\Entities\Group', (int)$group_id);
        if ($groupEntity) {
            //Удаляем все привилегии группы
            $group_privileges = $this->getEntityManager()
                    ->getRepository('\Entities\GroupPrivilege')
                    ->findBy(['group' => $groupEntity]);
            foreach ($group_privileges as $groupPrivilegeEntity) {
                $this->getEntityManager()->remove($groupPrivilegeEntity);
            }
            //Удаляем у всех пользователей группу
            $user_groups = $this->getEntityManager()
                    ->getRepository('\Entities\UserGroup')
                    ->findBy(['group' => $groupEntity]);
            foreach ($user_groups as $userGroupEntity) {
                $this->getEntityManager()->remove($userGroupEntity);
            }
            $this->getEntityManager()->remove($groupEntity);
            $this->getEntityManager()->flush();
            return true;
        }
        return _('Group not found');
    }
    
    public function editGroup($group_id, $group_name, $description)
    {
        if (empty($group_id)) {
            return _('Group not found');
        }
        if (empty($group_name)) {
            return _('Group name is required');
        }
        $groupEntity = $this->getGroupById($group_id);
        if (!$groupEntity) {
            return _('Group not found');
        }
        $existing_group = $this->getGroupByName($group_name, $groupEntity->getId());
        if ($existing_group) {
            return _('Group name already in use');
        }

        $groupEntity->setName(strip_tags($group_name));
        $groupEntity->setDescription(strip_tags($description));
        $this->getEntityManager()->persist($groupEntity);
        $this->getEntityManager()->flush();
        return true;
    }
    
    public function getGroupsForTemplate()
    {
        $groupsEntities = $this->getEntityManager()
                ->getRepository('Entities\Group')
                ->findAll();
        $groups = [];
        foreach ($groupsEntities as $groupEntity) {
            array_push($groups, [
                    'id' => $groupEntity->getId(), 
                    'name' => $groupEntity->getName(), 
                    'description' => $groupEntity->getDescription(),
                    'created' => $groupEntity->getCreated()->format('d-m-Y H:i:s'),
                    'privileges' => $this->getActionsForGroup($groupEntity)
                ]);
        }
        return $groups;
    }
    
    public function getGroupForTemplate($group_id)
    {
        if (empty($group_id)) {
            return _('Group not found');
        }
        $groupEntity = $this->getGroupById($group_id);
        if (!$groupEntity) {
            return _('Group not found');
        }
        return [
                'id' => $groupEntity->getId(), 
                'name' => $groupEntity->getName(), 
                'description' => $groupEntity->getDescription(),
                'created' => $groupEntity->getCreated()->format('d-m-Y H:i:s'),
                'privileges' => $this->getActionsForGroup($groupEntity)
            ];
    }
    
    public function getGroupByName($name, $group_id = null)
    {
        if (!empty($group_id)) {
            $this->getQueryBuilder()->select(['g'])
                ->from('Entities\Group', 'g')
                ->where($this->getQueryBuilder()->expr()->andX(
                        $this->getQueryBuilder()->expr()->neq('g.id', ':group_id'), //!=
                        $this->getQueryBuilder()->expr()->eq('g.name', ':name') //=
                ))->setParameters(['group_id' => $group_id, 'name' => $name]);
            $query = $this->getQueryBuilder()->getQuery();
            return $query->getResult();
        } else {
            return $this->getEntityManager()
                ->getRepository('Entities\Group')
                ->findOneBy(['name' => $name]);
        }
    }
    
    public function getGroupById($group_id)
    {
        return $this->getEntityManager()->find('Entities\Group', $group_id);
    }
    
    public function getActionsForGroup($groupEntity)
    {
        $actions = [];
        $group_privileges = $this->getEntityManager()
                ->getRepository('Entities\GroupPrivilege')
                ->findBy(['group' => $groupEntity]);

        foreach ($group_privileges as $groupPrivilegeEntity) {
            array_push($actions, 
                    [
                        'id' => $groupPrivilegeEntity->getId(),
                        'action_name' => $groupPrivilegeEntity->getAction()->getDescription()
                        .' ('.$groupPrivilegeEntity->getAction()->getName().')'
                    ]
                    );
        }
        return $actions;
    }
    
    /**
    * Привелегии групп
    */
    
    public function deleteGroupPrivileges($group_privilege_id)
    {
        if (empty($group_privilege_id)) {
            return _('Group privilege not found');
        }
        $groupPrivlegeEntity = $this->getEntityManager()->find('\Entities\GroupPrivilege', (int)$group_privilege_id);
        if ($groupPrivlegeEntity) {
            $this->getEntityManager()->remove($groupPrivlegeEntity);
            $this->getEntityManager()->flush();
            return true;
        }
        return _('Group privilege not found');
    }
    
    public function addGroupPrivileges($group_id, $action_id)
    {
        if (empty($group_id)) {
            return _('Group not found');
        }
        if (empty($action_id)) {
            return _('Action not found');
        }
        $groupEntity = $this->getEntityManager()->find('\Entities\Group', (int)$group_id);
        if (!$groupEntity) {
            return _('Group not found');
        }
        $actionEntity = $this->getEntityManager()->find('\Entities\Action', (int)$action_id);
        if (!$actionEntity) {
            return _('Action not found');
        }
        $groupPrivilegeEntity = $this->getEntityManager()
                ->getRepository('\Entities\GroupPrivilege')
                ->findOneBy(['group' => $groupEntity, 'action' => $actionEntity]);
        if ($groupPrivilegeEntity) {
            return true; //уже есть
        }
        $groupPrivilegeEntity = new \Entities\GroupPrivilege;
        $groupPrivilegeEntity->setGroup($groupEntity);
        $groupPrivilegeEntity->setAction($actionEntity);
        $this->getEntityManager()->persist($groupPrivilegeEntity);
        $this->getEntityManager()->flush();
        return true;
    }
    
    public function editGroupPrivileges($group_id, $actions)
    {
        if (empty($group_id)) {
            return _('Group not found');
        }
        if (empty($actions)) {
            return _('Action not found');
        }
        foreach ($actions as $action_name) {
            $actionEntity = $this->getEntityManager()
                    ->getRepository('\Entities\Action')
                    ->findOneBy(['name' => $action_name]);
            if (empty($actionEntity)) {
                continue; //пропускаем несуществующий экшен
            }
            $groupEntity = $this->getEntityManager()->find('\Entities\Group', (int)$group_id);
            if (!$groupEntity) {
                return _('Group not found');
            }

            //Удаление некоторых старых
            $group_privilges = $this->getEntityManager()
                    ->getRepository('\Entities\GroupPrivilege')
                    ->findBy(['group' => $groupEntity]);
            foreach ($group_privilges as $groupPrivilegeEntity) {
                if (!in_array($groupPrivilegeEntity->getAction()->getName(), $actions)) {
                    $this->getEntityManager()->remove($groupPrivilegeEntity);
                }
            }
            
            //Добавление новых привилегий
            $groupPrivilegeEntity = $this->getEntityManager()
                    ->getRepository('\Entities\GroupPrivilege')
                    ->findOneBy(['group' => $groupEntity, 'action' => $actionEntity]);
            if ($groupPrivilegeEntity) {
                continue; //уже есть
            }

            $group_privileges[$actionEntity->getName()] = new \Entities\GroupPrivilege;
            $group_privileges[$actionEntity->getName()]->setGroup($groupEntity);
            $group_privileges[$actionEntity->getName()]->setAction($actionEntity);
            $this->getEntityManager()->persist($group_privileges[$actionEntity->getName()]);
        }
        $this->getEntityManager()->flush();
        return true;
    }
    
    public function checkPrivilegesByGroupsNames(array $allowed_groups)
    {
        if (empty($allowed_groups)) {
            return false;
        }
        if (empty($_SESSION['__user_id'])) {
            return false;
        }
        
        $groups = $this->getEntityManager()
                ->getRepository('\Entities\UserGroup')
                ->findBy(['user_id' => (int)$_SESSION['__user_id']]);
        
        if (!empty(array_intersect($groups, $allowed_groups))) {
            return true;
        }
        return false;
    }
    
    public function checkPrivilegesByActionName($action_name)
    {
        $action_name = $this->prepareActionName($action_name);
        if (empty($action_name)) {
            return false;
        }
        if (empty($_SESSION['__user_id'])) {
            return false;
        }
        
        $actionEntity = $this->getEntityManager()
                ->getRepository('\Entities\Action')
                ->findBy(['name' => $action_name]);
        if (empty($actionEntity)) {
            return false;
        }
        
        $groups_privileges = $this->getEntityManager()
                ->getRepository('\Entities\GroupPrivilege')
                ->findBy(['action' => $actionEntity]);

        if (empty($groups_privileges)) {
            return false;
        }
        
        $allowed_groups = [];
        foreach ($groups_privileges as $groupPrivilegeEntity) {
            $allowed_groups[] = $groupPrivilegeEntity->getGroup()->getName();
        }
        
        $user_groups = $this->getEntityManager()
                ->getRepository('\Entities\UserGroup')
                ->findBy(['user' => (int)$_SESSION['__user_id']]);
        
        $user_groups_arr = [];
        foreach ($user_groups as $userGroupEntity) {
            $user_groups_arr[] = $userGroupEntity->getGroup()->getName();
        }
        
        if (!empty(array_intersect($user_groups_arr, $allowed_groups))) {
            return true;
        }
        return false;
    }
    
    /**
    * Служебное
    */

    private function prepareActionName($k)
    {
            $k = str_replace('getAction', '', $k);
            $k = str_replace('postAction', '', $k);
            return $k;
    }
}


