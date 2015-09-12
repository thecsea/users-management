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
 * Class UsersTest
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @package it\thecsea\users_management
 */
class UsersTest extends \PHPUnit_Framework_TestCase
{
    public  function testGetUsers()
    {
        $db = require(__DIR__."/config.php");
        $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']);
        $usersManagement = new UsersManagement($connection, $db['tables']['users']);
        $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg");
        $user2 = User::newUser($usersManagement, "t", "tt@hhht.it", "gggg");
        $users = $usersManagement->getUsers();
        $data1= $users[0]->getUserInfo();
        if($data1['id'] == $user->getId())
        {
            $this->assertEquals($user->getUserInfo(),$users[0]->getUserInfo());
            $this->assertEquals($user2->getUserInfo(),$users[1]->getUserInfo());
        }else
        {
            $this->assertEquals($user->getUserInfo(),$users[1]->getUserInfo());
            $this->assertEquals($user2->getUserInfo(),$users[0]->getUserInfo());
        }
        $user->removeUser();
        $user2->removeUser();
    }
}
