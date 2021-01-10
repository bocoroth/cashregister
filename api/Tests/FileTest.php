<?php

// vendor/bin/phpunit --configuration phpunit.xml --colors=always

namespace API;

use PHPUnit\Framework\TestCase;

require_once(realpath(dirname(__FILE__) . '/../../config.php'));
require_once(realpath(dirname(__FILE__) . '/../File.php'));

class FileTest extends TestCase
{
    private $_app;
    private $_api;

    public function setUp()
    {
        $this->_app = new \Slim\App();
        $this->_api = new File($this->_app);
    }

    public function testPostFile()
    {
        // set up Slim mock environment
        $environment = \Slim\Http\Environment::mock();
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        // set up mock POST data
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withMethod('POST');
        $request->getBody()->write(json_encode(
            [
                'file_data' => "2.12,3.00\n1.97,2.00",
                'separator' => ',',
                'decimal' => '.'
            ],
            JSON_PRETTY_PRINT
        ));

        // create test response
        $response = new \Slim\Http\Response();
        $response = $this->_api->postFile($request, $response, []);
        $response->getBody()->rewind();

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response);
        $this->assertNotNull($response->getBody());

        $contents = json_decode($response->getBody()->getContents(), true);

        $this->assertTrue(is_array($contents));
        $this->assertNotTrue(empty($contents));

        $this->assertNotTrue(empty($contents[0]));
        $this->assertArrayHasKey('owed', $contents[0]);
        $this->assertArrayHasKey('paid', $contents[0]);
        $this->assertTrue(is_numeric($contents[0]['owed']));
        $this->assertTrue(is_numeric($contents[0]['paid']));
        $this->assertSame($contents[0]['owed'], '2.12');
        $this->assertSame($contents[0]['paid'], '3.00');

        $this->assertNotTrue(empty($contents[1]));
        $this->assertArrayHasKey('owed', $contents[1]);
        $this->assertArrayHasKey('paid', $contents[1]);
        $this->assertTrue(is_numeric($contents[1]['owed']));
        $this->assertTrue(is_numeric($contents[1]['paid']));
        $this->assertSame($contents[1]['owed'], '1.97');
        $this->assertSame($contents[1]['paid'], '2.00');
    }
}
