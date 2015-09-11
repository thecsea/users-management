<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 18/07/15
 * Time: 23.28
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
use it\thecsea\mysqltcs\Mysqltcs;
use it\thecsea\mysqltcs\MysqltcsException;
use it\thecsea\mysqltcs\MysqltcsOperations;


/**
 * Class UsersManagement
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @package it\thecsea\users_management
 */
class UsersManagement
{
    /**
     * @var Mysqltcs
     */
    private $connection;

    /**
     * @var String
     */
    private $usersTable;

    /**
     * @var MysqltcsOperations
     */
    private $operations;

    /**
     * @param Mysqltcs $connection a valid and connected instance of Mysqltcs
     * @param String $usersTable users table name
     * @throws UsersManagementException on connection errors
     */
    public function __construct(Mysqltcs $connection, $usersTable)
    {
        $this->connection = $connection;
        $this->usersTable = $usersTable;

        self::connectionCheck($connection);
        self::usersTableCheck($connection, $usersTable);
        $this->operations = new MysqltcsOperations($connection, $usersTable);
    }

    /**
     * throw exception if mysqltcs passed is not valid
     * @param Mysqltcs $connection
     * @throws UsersManagementException
     */
    private static function connectionCheck(Mysqltcs $connection)
    {
        if($connection == null || !($connection instanceof Mysqltcs)) {
            throw new UsersManagementException("Connection passed is not an instance of Mysqltcs");
        }

        if(!$connection->isConnected()) {
            throw new UsersManagementException("Connection passed is not connected");
        }
    }


    /**
     * throw exception if usersTable passed is not valid
     * @param Mysqltcs $connection
     * @param string $usersTable
     * @throws UsersManagementException
     */
    private static function usersTableCheck(Mysqltcs $connection, $usersTable)
    {
        //check table name
        try{
            $operations = new MysqltcsOperations($connection, $usersTable);
            if($operations->getTableInfo("Name") != $usersTable) {
                throw new UsersManagementException("Table name passed is not corrected");
            }
        }catch(MysqltcsException $e){
            throw new UsersManagementException("Table name passed is not corrected",0, $e);
        }
    }


    /**
     * This entails that you can clone every instance of this class
     */
    public function __clone()
    {
        $this->operations = clone $this->operations;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return ("users table: ".$this->usersTable."\nmysqltcs:\n" . (string)$this->connection);
    }

    /**
     * @return Mysqltcs
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * getOperations with clone
     * @return MysqltcsOperations
     */
    public function getOperations()
    {
        //clone to avoid to modify original operations
        $ret = clone $this->operations;
        return $ret;
    }

    /**
     * @return String
     */
    public function getUsersTable()
    {
        return $this->usersTable;
    }

    /**
     * @param Mysqltcs $connection
     */
    public function setConnection(Mysqltcs $connection)
    {
        $this->connection = $connection;
        self::connectionCheck($connection);
        $this->operations->setMysqltcs($connection);
    }

    /**
     * @param String $usersTable
     */
    public function setUsersTable($usersTable)
    {
        $this->usersTable = $usersTable;
        self::usersTableCheck($this->connection, $usersTable);
        $this->operations->setDefaultFrom($usersTable);
    }


    /**
     * Return an unique apiKey
     * @return string
     */
    public function createApiKey()
    {
        do{
            $key = md5(rand());
        }while($this->operations->getValue("id","api_key = '$key'"));
        return $key;
    }

    /**
     * get id by apiKey
     * @param string $apiKey
     * @return null|int id
     */
    public function getIdByApiKey($apiKey)
    {
        //sql check
        $apiKey = $this->connection->getEscapedString($apiKey);

        //get id and return
        return $this->operations->getValue("id", "api_key = '$apiKey'");
    }

    /**
     * get id by email
     * @param string $email
     * @return null|int id
     */
    public function getIdByEmail($email)
    {
        //sql check
        $email = $this->connection->getEscapedString($email);

        //get id and return
        return $this->operations->getValue("id", "email = '$email'");
    }
}