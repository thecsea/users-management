<?php

/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 18/07/15
 * Time: 23.30
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

require_once(__DIR__."/../vendor/autoload.php");

/**
 * Class GeneralTest
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @package it\thecsea\users_management
 */
class GeneralTest extends \PHPUnit_Framework_TestCase
{


    public function testCorrectInstance()
    {
        $thrown = false;
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        try {
            new UsersManagement($connection, $db['tables']['users']);
        } catch (UsersManagementException $e) {
            $thrown = true;
        }
        $this->assertFalse($thrown);
    }


    public function testNoCorrectTables()
    {
        $thrown = false;
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        try {
            new UsersManagement($connection, $db['tables']['users']."err");
        } catch (UsersManagementException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function testToString()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $expected = "users table: ".$db['tables']['users']."\nmysqltcs:\n" . (string)$connection;
        $this->assertEquals($expected, (string)$usersManagement);
    }

    public function testGetterSetter()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $this->assertEquals($connection, $usersManagement->getConnection());
        $connection2 = clone $connection;
        $this->assertNotEquals($connection, $connection2);
        $usersManagement->setConnection($connection2);
        $this->assertEquals($connection2, $usersManagement->getConnection());
        $this->assertEquals($db['tables']['users'], $usersManagement->getUsersTable());
        $thrown = false;
        try {
            $usersManagement->setUsersTable("err");
        } catch (UsersManagementException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        $thrown = false;
        try {
            $usersManagement->setUsersTable($db['tables']['users']);
        } catch (UsersManagementException $e) {
            $thrown = true;
        }
        $this->assertFalse($thrown);
        $this->assertEquals($db['tables']['users'], $usersManagement->getUsersTable());
    }

    public function testClone()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $connection2 = clone $connection;
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $usersManagement2 = clone $usersManagement;
        $this->assertEquals($usersManagement, $usersManagement2);
        $usersManagement2->setConnection($connection2);
        $this->assertNotEquals($usersManagement, $usersManagement2);
    }
}
