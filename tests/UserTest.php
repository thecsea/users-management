<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 09/09/15
 * Time: 22.38
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
 * Class UserTest
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @package it\thecsea\users_management
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        $id = $user->getId();
        $data = $user->getUserInfo();
        $this->assertEquals($data['name'],"t");
        $user2 = User::getUserById($usersManagement, $id);
        $data2 = $user2->getUserInfo();
        $this->assertEquals($data,$data2);
        $user2 = User::getUserByEmail($usersManagement, $data['email']);
        $data2 = $user2->getUserInfo();
        $this->assertEquals($data,$data2);
        $user2 = User::getUserByApiKey($usersManagement, $data['api_key']);
        $data = $user2->getUserInfo();
        $this->assertEquals($data,$data2);
        $this->assertEquals($data['password'],"");
        $user2 = User::getUserByLogin($usersManagement, $data['email'], "gggg");
        $data2 = $user2->getUserInfo();
        $this->assertEquals($data, $data2);
        //password error
        $thrown = false;
        try{
            User::getUserByLogin($usersManagement, $data['email'], "gghgg");
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        $user->removeUser();
        //remove tests
        $thrown = false;
        try{
            $user->getId();
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        $thrown = false;
        try{
            $user2->getId();
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);

    }

    public function testNewUser()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $thrown = false;
        try{
            User::newUser($usersManagement, "t", "tthhh.it", "gggg");
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        $thrown = false;
        try{
            User::newUser($usersManagement, "t", "tt@hhh.it", "gggg","t");
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        $thrown = false;
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        try{
            User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $user->removeUser();
        $this->assertTrue($thrown);
        $thrown = false;
        try{
            User::newUser($usersManagement, str_pad("", 256, "x"), "tt@hhh.it", "gggg");
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        $thrown = false;
        try{
            User::newUser($usersManagement, "t", str_pad("", 256, "x"), "gggg");
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        //correct apiKey
        $thrown = false;
        try{
            $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg", $usersManagement->hash(rand()));
            $user->removeUser();
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertFalse($thrown);
        //apiKey already taken
        $thrown = false;
        $apiKey = $usersManagement->hash(rand());
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg", $apiKey);
        try{
            User::newUser($usersManagement, "t", "tt@hhh.it2", "gggg", $apiKey);
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $user->removeUser();
        $this->assertTrue($thrown);
    }

    public function testUpdate()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        $user2 = User::getUserById($usersManagement, $user->getId());
        $dataO = $user->getUserInfo();
        $dataO2 = $user2->getUserInfo();
        //name
        $user->updateName("y");
        $data = $user->getUserInfo();
        $this->assertEquals("y", $data['name']);
        $data2 = $user2->getUserInfo();
        $this->assertEquals("y", $data2['name']);
        $dataO['name'] = "y";
        $dataO2['name'] = "y";
        $this->assertEquals($dataO, $data);
        $this->assertEquals($dataO2, $data2);
        //email
        $user->updateEmail("tt@tt.it");
        $data = $user->getUserInfo();
        $this->assertEquals("tt@tt.it", $data['email']);
        $data2 = $user2->getUserInfo();
        $this->assertEquals("tt@tt.it", $data2['email']);
        $dataO['email'] = "tt@tt.it";
        $dataO2['email'] = "tt@tt.it";
        $this->assertEquals($dataO, $data);
        $this->assertEquals($dataO2, $data2);
        //apiKey
        $apiKey = $usersManagement->hash(rand());
        $user->updateApiKey($apiKey);
        $data = $user->getUserInfo();
        $this->assertEquals($apiKey, $data['api_key']);
        $data2 = $user2->getUserInfo();
        $this->assertEquals($apiKey, $data2['api_key']);
        $dataO['api_key'] = $apiKey;
        $dataO2['api_key'] = $apiKey;
        $this->assertEquals($dataO, $data);
        $this->assertEquals($dataO2, $data2);
        $user->removeUser();

    }

    public function testPassword()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        $this->assertTrue($user->checkCorrectPassword("gggg"));
        $this->assertFalse($user->checkCorrectPassword("ggkg"));
        $dataO = $user->getUserInfo();
        $user->updatePassword("ggkg");
        $this->assertFalse($user->checkCorrectPassword("gggg"));
        $this->assertTrue($user->checkCorrectPassword("ggkg"));
        $this->assertEquals($dataO, $user->getUserInfo());
        $user->removeUser();
    }

    public function testEnabled()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        $data = $user->getUserInfo();
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($data['enabled']);
        $user->updateEnabled(false);
        $data2 = $user->getUserInfo();
        $data['enabled'] = false;
        $this->assertFalse($data2['enabled']);
        $this->assertFalse($user->isEnabled());
        $this->assertEquals($data, $data2);
        $user->removeUser();
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg", "", false);
        $this->assertFalse($user->isEnabled());
        $user->removeUser();
    }

    public function testEquals()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $connection2 = clone $connection;
        $usersManagement2 = clone $usersManagement;
        $usersManagement->setConnection($connection2);
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");

        //clone
        $user2 = clone $user;
        $this->assertTrue($user->equals($user2));
        $this->assertEquals($user, $user2);
        $user2->setUsersManagement($usersManagement2);
        $this->assertTrue($user->equals($user2));
        $this->assertNotEquals($user, $user2);

        //new same user
        $user2 = User::getUserById($usersManagement, $user->getId());
        $this->assertTrue($user->equals($user2));
        $this->assertEquals($user, $user2);
        $user2->setUsersManagement($usersManagement2);
        $this->assertTrue($user->equals($user2));
        $this->assertNotEquals($user, $user2);

        //new user
        $user2 = User::newUser($usersManagement, "t", "tt@hshh.it", "gggg");
        $this->assertFalse($user->equals($user2));
        $this->assertNotEquals($user, $user2);
        $user->removeUser();
        $user2->removeUser();
    }
}
