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
        foreach($data as $key=>$value)
            $ret .= "$key: $value\n";
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
     * @return User
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
     * @return User
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
     * @return User
     * @throws UsersManagementException
     */
    public static function getUserByEmail(UsersManagement $usersManagement, $email)
    {
        $id = $usersManagement->getIdByEmail($email);
        return self::getUserById($usersManagement, $id);
    }

    //
    /**
     * insert a new user in db and return it
     * @param UsersManagement $usersManagement
     * @param String $name
     * @param String $email
     * @param String $password
     * @param String $apiKey
     * @return User
     * @throws UsersManagementException when user data are not corrected
     */
    public static function newUser(UsersManagement $usersManagement, $name, $email, $password, $apiKey = "")
    {
        $id = self::insertUser($usersManagement, $name, $email, $password, $apiKey);
        return self::getUserById($usersManagement, $id);
    }


    /**
     * Insert an user in db
     * @param UsersManagement $usersManagement
     * @param String $name
     * @param String $email
     * @param String $password
     * @param string $apiKey if empty the apiKey is created unique
     * @return int user id
     * @throws UsersManagementException when user data are not corrected
     */
    private static function insertUser(UsersManagement $usersManagement, $name, $email, $password, $apiKey = ""){
        //sql check
        $name = $usersManagement->getConnection()->getEscapedString($name);
        $email = $usersManagement->getConnection()->getEscapedString($email);
        $password = $usersManagement->getConnection()->getEscapedString($password);
        $apiKey = $usersManagement->getConnection()->getEscapedString($apiKey);

        //check email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new UsersManagementException("Email is not valid");
        }

        //check empty api_key
        if($apiKey == "") {
            $apiKey = $usersManagement->createApiKey();
        }else if(strlen($apiKey) != 32) {
            throw new UsersManagementException("Api key is not valid (length)");
        }

        //check if user already exists
        if($usersManagement->getOperations()->getValue("id", "email = '$email' OR api_key = '$apiKey'")) {
            throw new UsersManagementException("User already exists");
        }

        //insert data
        try {
            $usersManagement->getOperations()->insert("name, email, password, api_key", "'$name', '$email', '" . md5($password) . "', '$apiKey'");
        }catch(MysqltcsException $e){
            throw new UsersManagementException("User data are not corrected, user is not created",0, $e);
        }

        //return user id
        return $usersManagement->getConnection()->getLastId();
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
        if($id == null || $id <=0 || $usersManagement->getOperations()->getValue("id", "id = $id") != $id)
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
     * get user informations
     * @return array
     * @throws UsersManagementException when user is not valid
     */
    public function getUserInfo()
    {
        $this->checkUser();
        $ret = $this->usersManagement->getOperations()->getList("*, NULL as password", "id = ".$this->id);
        if($ret && isset($ret[0])){
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
        //valid id
        if($this->id && $this->usersManagement->getOperations()->getValue("id", "id = ".$this->id) == $this->id)
            return true;
        else
            return false;
    }

    /**
     * if user is not valid throw an exception
     * @throws UsersManagementException when user is not valid
     */
    private function checkUser()
    {
        if(!$this->isValid())
            throw new UsersManagementException("User is not valid");
    }

}