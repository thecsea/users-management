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
        $data = $user2->getUserInfo();
        $this->assertEquals($data['name'],"t");
        $user2 = User::getUserByEmail($usersManagement, $data['email']);
        $data = $user2->getUserInfo();
        $this->assertEquals($data['name'],"t");
        $user2 = User::getUserByApiKey($usersManagement, $data['api_key']);
        $data = $user2->getUserInfo();
        $this->assertEquals($data['name'],"t");
        $user->removeUser();
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
            $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg", md5(rand()));
            $user->removeUser();
        }catch(UsersManagementException $e)
        {
            $thrown = true;
        }
        $this->assertFalse($thrown);
        //apiKey already taken
        $thrown = false;
        $apiKey = md5(rand());
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
}
