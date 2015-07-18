<?php

/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 18/07/15
 * Time: 23.30
 */
namespace it\thecsea\usersManagement;

require_once(__DIR__."/../vendor/autoload.php");

/**
 * Class Test
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @package it\thecsea\usersManagement
 */
class Test extends \PHPUnit_Framework_TestCase
{

    public function test1()
    {
        $test = new usersMangament();
        $this->assertEquals("hello",$test->test("hello"));
    }
}
