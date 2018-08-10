<?php
use PHPUnit\Framework\TestCase;

include_once(dirname(__FILE__)."/../classes/csrfClass.php");

class CSRFClassTest extends TestCase
{
    public function testCompareTokensFail()
    {

        $output = CSRF::compareTokens(['a'], ['b']);

        $this->assertEquals(false, $output);

    }

    public function testCompareTokensSuccess()
    {

        $output = CSRF::compareTokens(['csrf' => 'a'], ['csrf' => 'a']);

        $this->assertEquals(true, $output);

    }


}
