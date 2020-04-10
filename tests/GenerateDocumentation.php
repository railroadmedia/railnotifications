<?php

use Railroad\Railnotifications\Tests\TestCase;

class GenerateDocumentation extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function test_command()
    {

        \Illuminate\Support\Facades\Artisan::call('apidoc:generate');

        $this->assertTrue(true);
    }
}
