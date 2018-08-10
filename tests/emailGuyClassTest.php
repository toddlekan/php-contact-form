<?php
use PHPUnit\Framework\TestCase;

include_once(dirname(__FILE__)."/../classes/emailGuyClass.php");

class EmailGuyClassTest extends TestCase
{
    public function testCompareTokensFail()
    {

        $output = EmailGuy::compareTokens(['a'], ['b']);

        $this->assertEquals("status_token", $output);

    }

    public function testCompareTokensSuccess()
    {

        $output = EmailGuy::compareTokens(['csrf' => 'a'], ['csrf' => 'a']);

        $this->assertEquals("", $output);

    }

    public function testCompareRequiredFail()
    {

        $output = EmailGuy::required("");

        $this->assertEquals("status_required", $output);

    }

    public function testCompareRequiredSuccess()
    {

        $output = EmailGuy::required("xxx");

        $this->assertEquals("", $output);

    }

    public function testCompareValidateEmailFail()
    {

        $output = EmailGuy::validateEmail("xxx");

        $this->assertEquals("status_email", $output);

    }

    public function testCompareValidateEmailSuccess()
    {

        $output = EmailGuy::validateEmail("x@x.com");

        $this->assertEquals("", $output);

    }

}
