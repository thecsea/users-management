<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 09/09/15
 * Time: 22.52
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace it\thecsea\users_management;
use it\thecsea\mysqltcs\MysqltcsException;


/**
 * Class User
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @package it\thecsea\users_management
 */
class User
{
    /**
     * @var UsersManagement
     */
    private $usersManagement;
    /**
     * @var int
     */
    private $id;

    /**
     * @param UsersManagement $usersManagement
     */
    private function __construct(UsersManagement $usersManagement){
        self::usersManagementCheck($usersManagement);
        $this->usersManagement = $usersManagement;
    }

    /**
     * getUsersManagement with clone
     * @return UsersManagement
     */
    public function getUsersManagement()
    {
        //clone to avoid to modify original operations
        $ret = clone $this->usersManagement;
        return $ret;
    }

    /**
     * @param UsersManagement $usersManagement
     */
    public function setUsersManagement(UsersManagement $usersManagement)
    {
        self::usersManagementCheck($usersManagement);
        $this->usersManagement = $usersManagement;
    }

    /**
     * @return string
     */
    function __toString()
    {
        $data = $this->getUserInfo();
        $ret = "";
        foreach($data as $key=>$value) {
            if($value === true)
                $value = "true";
            else if($value === false)
                $value = "false";
            $ret .= "$key: $value\n";
        }
        return $ret;
    }

    /**
     * This entails that you can clone every instance of this class
     */
    public function __clone()
    {
    }

    /**
     * throw exception if usersManagement passed is not valid
     * @param UsersManagement $usersManagement
     * @throws UsersManagementException
     */
    private static function usersManagementCheck(UsersManagement $usersManagement)
    {
        if($usersManagement == null || !($usersManagement instanceof UsersManagement)) {
            throw new UsersManagementException("usersManagement passed is not an instance of UsersManagement");
        }
    }

    /**
     * set user id propriety (escaped)
     * @param int $id
     */
    private function setId($id)
    {
        $this->id = $this->usersManagement->getConnection()->getEscapedString($id);
    }

    /**
     * @param UsersManagement $usersManagement
     * @param int|null $id
     * @return User instance of the user
     * @throws UsersManagementException
     */
    public static function getUserById(UsersManagement $usersManagement, $id)
    {
        $c = __CLASS__;
        /* @var $instance User */
        $instance = new $c($usersManagement);
        self::checkId($usersManagement, $id);
        $instance->setId($id);
        return $instance;
    }

    /**
     * @param UsersManagement $usersManagement
     * @param string $apiKey
     * @return User instance of the user
     * @throws UsersManagementException
     */
    public static function getUserByApiKey(UsersManagement $usersManagement, $apiKey)
    {
        $id = $usersManagement->getIdByApiKey($apiKey);
        return self::getUserById($usersManagement, $id);
    }

    /**
     * @param UsersManagement $usersManagement
     * @param string $email
     * @return User instance of the user
     * @throws UsersManagementException
     */
    public static function getUserByEmail(UsersManagement $usersManagement, $email)
    {
        $id = $usersManagement->getIdByEmail($email);
        return self::getUserById($usersManagement, $id);
    }

    /**
     * Get user by email checking the password, if the password is not correct an exception is thrown
     * @param UsersManagement $usersManagement
     * @param string $email
     * @param string $password
     * @return User instance of the user
     * @throws UsersManagementException
     */
    public static function getUserByLogin(UsersManagement $usersManagement, $email, $password)
    {
        $user = self::getUserByEmail($usersManagement, $email);
        if(!$user->checkCorrectPassword($password))
            throw new UsersManagementException("Password is not correct");
        return $user;
    }

    /**
     * insert a new user in db and return it
     * @param UsersManagement $usersManagement
     * @param String $name
     * @param String $email
     * @param String $password
     * @param string $apiKey if empty the apiKey is created unique
     * @param bool $enabled set the enabled value
     * @return User instance of the user
     * @throws UsersManagementException when user data are not corrected
     */
    public static function newUser(UsersManagement $usersManagement, $name, $email, $password, $apiKey = "", $enabled = true)
    {
        $id = self::insertUser($usersManagement, $name, $email, $password, $apiKey, $enabled);
        return self::getUserById($usersManagement, $id);
    }


    /**
     * Insert an user in db
     * @param UsersManagement $usersManagement
     * @param String $name
     * @param String $email
     * @param String $password
     * @param string $apiKey if empty the apiKey is created unique
     * @param bool $enabled set the enabled value
     * @return int user id
     * @throws UsersManagementException when user data are not corrected
     */
    private static function insertUser(UsersManagement $usersManagement, $name, $email, $password, $apiKey = "", $enabled = true){
        //sql check
        $name = $usersManagement->getConnection()->getEscapedString($name);
        $email = $usersManagement->getConnection()->getEscapedString($email);
        $password = $usersManagement->getConnection()->getEscapedString($password);
        $enabled = $usersManagement->getConnection()->getEscapedString($enabled);
        $apiKey = $usersManagement->getConnection()->getEscapedString($apiKey);

        self::checkName($name);
        self::checkEmail($usersManagement, $email);
        $apiKey = self::checkApiKey($usersManagement, $apiKey);

        //insert data
        try {
            $usersManagement->getOperations()->insert("name, email, password, api_key, enabled", "'$name', '$email', '" . md5($password) . "', '$apiKey', ".($enabled?1:0));
        }catch(MysqltcsException $e){
            throw new UsersManagementException("Mysql error during insert, please take a look to exception cause",0, $e);
        }

        //return user id
        return $usersManagement->getConnection()->getLastId();
    }

    /**
     * @param string $name
     * @throws UsersManagementException error description
     */
    private static function checkName( $name)
    {
        if(strlen($name)>255){
            throw new UsersManagementException("Name exceeds the maximum length (255)");
        }
    }

    /**
     * @param UsersManagement $usersManagement
     * @param string $email
     * @throws UsersManagementException error description
     */
    private static function checkEmail(UsersManagement $usersManagement, $email)
    {
        if(strlen($email)>255){
            throw new UsersManagementException("Email exceeds the maximum length (255)");
        }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new UsersManagementException("Email is not valid");
        }else if($usersManagement->getOperations()->getValue("id", "email = '$email'")) {
            throw new UsersManagementException("User already exists");
        }
    }

    /**
     * Check apiKey and return it or an unique random apiKey if the apiKey passed is empty
     * @param UsersManagement $usersManagement
     * @param string $apiKey
     * @return string unique random apiKey if $apiKey is empty
     * @throws UsersManagementException error description
     */
    private static function checkApiKey(UsersManagement $usersManagement, $apiKey = "")
    {
        if($apiKey == "") {
           return $usersManagement->createApiKey();
        }else if(strlen($apiKey) != 32) {
            throw new UsersManagementException("ApiKey's length is not correct (32 is the correct length)");
        }else if($usersManagement->getOperations()->getValue("id", "api_key = '$apiKey'")) {
            throw new UsersManagementException("ApiKey chosen is already taken");
        }

        return $apiKey;
    }

    /**
     * check if the id is valid
     * @param UsersManagement $usersManagement
     * @param int|null $id
     * @throws UsersManagementException
     */
    private static function checkId(UsersManagement $usersManagement, $id)
    {
        $id = $usersManagement->getConnection()->getEscapedString($id);
        if($id == null || !is_numeric($id) || $id <=0 || $usersManagement->getOperations()->getValue("id", "id = $id") != $id)
            throw new UsersManagementException("User is not valid");
    }

    /**
     * @return mixed
     * @throws UsersManagementException when user is not valid
     */
    public function getId()
    {
        $this->checkUser();
        return $this->id;
    }

    /**
     * remove the User from db
     * @throws UsersManagementException when user is not valid
     */
    public function removeUser()
    {
        $this->checkUser();
        $this->usersManagement->getOperations()->deleteRows("id = ".$this->id);
        $this->id = 0; //invalid id
    }

    /**
     * get user information
     * @return array
     * @throws UsersManagementException when user is not valid
     */
    public function getUserInfo()
    {
        $this->checkUser();
        $ret = $this->usersManagement->getOperations()->getList("*, NULL as password", "id = ".$this->id);
        if($ret && isset($ret[0])){
            $ret[0]['enabled'] = ($ret[0]['enabled']!="0")?true:false;
            return $ret[0];
        }
        return $ret;
    }

    /**
     * return true if the user is valid
     * @return bool
     */
    public function isValid()
    {
        return $this->exists();
    }

    /**
     * return true if the the user exists
     * @return bool
     */
    private function exists(){
        if($this->id && $this->usersManagement->getOperations()->getValue("id", "id = ".$this->id) == $this->id) {
            return true;
        }else {
            return false;
        }
    }

    /**
     * return true if the the user is enabled
     * @return bool
     * @throws UsersManagementException when user is not valid
     */
    public function isEnabled()
    {
        $this->checkUser();
        if($this->usersManagement->getOperations()->getValue("enabled", "id = ".$this->id) != 0) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * update enabled status in database
     * @param boolean $enabled
     * @throws UsersManagementException when an update error is occurred
     * @throws UsersManagementException when user is not valid
     */
    public function updateEnabled($enabled)
    {
        $this->checkUser();
        $enabled = $this->usersManagement->getOperations()->getEscapedString($enabled);
        $enabled = $enabled?1:0;

        $this->update("enabled", $enabled);

    }

    /**
     * if user is not valid throw an exception. IMPORTANT this method must be called at the start of every public method
     * @throws UsersManagementException when user is not valid
     */
    private function checkUser()
    {
        if(!$this->isValid())
            throw new UsersManagementException("User is not valid");
    }

    /**
     * update name
     * @param string $name
     * @throws UsersManagementException when name is not valid or user is not valid or an update error is occurred
     */
    public function updateName($name)
    {
        $this->checkUser();
        $name = $this->usersManagement->getOperations()->getEscapedString($name);
        self::checkName($name);
        $this->update("name", $name);
    }

    /**
     * update email
     * @param string $email
     * @throws UsersManagementException when email is not valid or user is not valid or an update error is occurred
     */
    public function updateEmail($email)
    {
        $this->checkUser();
        $email = $this->usersManagement->getOperations()->getEscapedString($email);
        self::checkEmail($this->usersManagement, $email);
        $this->update("email", $email);
    }

    /**
     * Update password. CAUTION: the method already makes the md5 hash
     * @param string $password unencrypted password
     * @throws UsersManagementException when password is not valid or user is not valid or an update error is occurred
     */
    public function updatePassword($password)
    {
        $this->checkUser();
        $password = $this->usersManagement->getOperations()->getEscapedString($password);
        $this->update("password", md5($password));
    }

    /**
     * update apiKey
     * @param string $apiKey
     * @throws UsersManagementException when apiKey is not valid or user is not valid or an update error is occurred
     */
    public function updateApiKey($apiKey)
    {
        $this->checkUser();
        $apiKey = $this->usersManagement->getOperations()->getEscapedString($apiKey);
        $apiKey = self::checkApiKey($this->usersManagement, $apiKey);
        $this->update("api_key", $apiKey);
    }

    /**
     * update a field of the current user
     * @param string $field field name
     * @param string $value field value
     * @throws UsersManagementException when an update error is occurred
     */
    private function update($field, $value)
    {
        try{
            $this->usersManagement->getOperations()->update(array("$field"=>"'$value'"), "id = ".$this->id);
        }catch(MysqltcsException $e){
            throw new UsersManagementException("Mysql error during update $field, please take a look to exception cause",0, $e);
        }
    }


    /**
     * Check if the password passed is correct. CAUTION: the method already makes the md5 hash
     * @param string $password unencrypted password
     * @return bool true if the password is correct, else false
     * @throws UsersManagementException when user is not valid
     */
    public function checkCorrectPassword($password)
    {
        $this->checkUser();
        $password = $this->usersManagement->getOperations()->getEscapedString($password);
        $passwordDb = $this->usersManagement->getOperations()->getValue("password", "id = ".$this->id);
        if($passwordDb == md5($password))
            return true;
        else
            return false;
    }

    /**
     * return true if two user have the same userInfo. CAUTION if two user are the same userInfo but different
     * usersMangement (so different connection or user table for example) the method return true, not false
     * @param User $user
     * @return bool
     * @throws UsersManagementException when user is not valid
     */
    public function equals(User $user)
    {
        $this->checkUser();
        return ($this->getUserInfo() == $user->getUserInfo());
    }
}