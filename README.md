# users-management
Build status: [![Build Status](https://travis-ci.org/thecsea/users-management.svg?branch=master)](https://travis-ci.org/thecsea/users-management) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecsea/users-management/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecsea/users-management/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/thecsea/users-management/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thecsea/users-management/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/thecsea/users-management/badges/build.png?b=master)](https://scrutinizer-ci.com/g/thecsea/users-management/build-status/master) [![Latest Stable Version](https://poser.pugx.org/thecsea/users-management/v/stable)](https://packagist.org/packages/thecsea/users-management) [![Total Downloads](https://poser.pugx.org/thecsea/users-management/downloads)](https://packagist.org/packages/thecsea/users-management) [![Latest Unstable Version](https://poser.pugx.org/thecsea/users-management/v/unstable)](https://packagist.org/packages/thecsea/users-management) [![License](https://poser.pugx.org/thecsea/users-management/license)](https://packagist.org/packages/thecsea/users-management)

The most powerful and the simplest library to add a customizable users management system

* Password stored with hash (MD5)
* Object oriented: every user is an object
* Very simply, only two object: User (single user) and UsersManagement (environment)
* Integrated with [mysqltcs](https://github.com/thecsea/mysqltcs)
* Use a already enstablisehd database connection (mysqltcs connection)
* ApiKey support
* Enabled user support



#Download
##Get/update composer
This library require composer (download composer here https://getcomposer.org/)

Update composer

`php composer.phar self-update`

##Download

Download via composer require (we suggest to create a dedicated directory for this)

`php composer.phar require thecsea/users-management`

or insert library as dependency in your composer project

`thecsea/users-management": "1.0.*`

in the last case you have to install or update you project

`php composer.phar install`

or

`php composer.phar update`

**N.B. If you don't have access to server terminal you can perform installation on your pc and upload all via ftp**

##Update users-management

You can update *users-management* (according to version limit set in `composer.json`)

`php composer.phar update`


#Use

The examples are not implemented yet, although you can see how to use the library looking the `tests`

##Firt use

You have to import the sql structure `tests/usersManagement.sql` 

##Simple example

    <?php
    require_once(__DIR__."/vendor/autoload.php"); //composer autoload
    $db = require(__DIR__."/config.php");
    use it\thecsea\mysqltcs\Mysqltcs;
    use it\thecsea\musers_management\UsersManagement;
    $connection = new Mysqltcs($db['host'],  $db['user'], $db['psw'], $db['db']); //myslqtcs connection
    $usersManagement = new UsersManagement($connection, $db['tables']['users']); //environment
    $user = User::newUser($usersManagement, "t", "tt@hhh.it", "gggg"); //new user, already inserted in db
    $user2 = User::getUserByLogin($usersManagement, "tt@hhh.it", "gggg"); //LOGIN get user checking password
    $users = $usersManagement->getUsers(); //get list of users
    print_r($users[0]->getUserInfo()); //print user info (associative matrix) 
    ?>

N.B. config.php is a file that contains the mysql connection data as array.

**N.B. you have to include composer autoload to use the library**
    
##How it works
This library is fully object oriented so you have tostring, equals (user), clone and so on

###Exception
This library use exception to show error, every method can throw two exception:

* `UsersManagementException` thrown on logic error (for example wrong password)
* `Mysqltcsexception` thrown on myslq error (for example db permission problem)

###Methods and documentation
The ueser contains other useful method, you can see how to use method looking the phpdoc

###Extra features
This library include extra features like apiKey string and enabled flag for each user, you can use these information as you want, this class provide only the insert and update methods for these information

N.B. this class create an unique apiKey for each user (default behavior)

###Db access
You can obviously access to db, but we suggest to don't modify the structure.


#Test

This library is tested, you can find tests under `tests`, coverage: [![Code Coverage](https://scrutinizer-ci.com/g/thecsea/users-management/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thecsea/users-management/?branch=master)


# By [thecsea.it](http://www.thecsea.it)
