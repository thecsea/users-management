<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 11/09/15
 * Time: 22.36
 */

namespace it\thecsea\users_management;

require_once(__DIR__."/../vendor/autoload.php");

use it\thecsea\mysqltcs\Mysqltcs;

/**
 * Class GeneralUserTest
 * @package it\thecsea\users_management
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 ClaudioCardinale
 * @version 1.0.0
 */
class GeneralUserTest extends \PHPUnit_Framework_TestCase
{
    public function testNoCorrectInstances()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $thrown = false;
        try {
            User::getUserById($usersManagement, 0);
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
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        $data = $user->getUserInfo();
        $expected = "";
        foreach($data as $key=>$value)
            $expected .= "$key: $value\n";
        $this->assertEquals($expected, (string)$user);
        $user->removeUser();
    }

    public function testGetterSetter()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $user = User::newUser($usersManagement, "t", "tt@hhh2.it", "gggg");
        $this->assertEquals($usersManagement, $user->getUsersManagement());
        $connection2 = clone $connection;
        $usersManagement2 = clone $usersManagement;
        $usersManagement2->setConnection($connection2);
        $this->assertNotEquals($usersManagement, $usersManagement2);
        $user->setUsersManagement($usersManagement2);
        $this->assertEquals($usersManagement2, $user->getUsersManagement());
        $user->removeUser();
    }

    public function testClone()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $connection2 = clone $connection;
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $usersManagement2 = clone $usersManagement;
        $usersManagement2->setConnection($connection2);
        $user = User::newUser($usersManagement, "t", "tt@hhh2.it", "gggg");
        $user2 = clone $user;
        $this->assertEquals($user, $user2);
        $user2->setUsersManagement($usersManagement2);
        $this->assertNotEquals($user, $user2);
        $this->assertEquals($user->getUserInfo(), $user2->getUserInfo());
        $user->removeUser();
    }
}
