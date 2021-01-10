<?php

// vendor/bin/phpunit --configuration phpunit.xml --colors=always

namespace API;

use PHPUnit\Framework\TestCase;

require_once(realpath(dirname(__FILE__) . '/../../config.php'));
require_once(realpath(dirname(__FILE__) . '/../Change.php'));

class ChangeTest extends TestCase
{
    private $_app;
    private $_api;

    public function setUp()
    {
        $this->_app = new \Slim\App();
        $this->_api = new Change($this->_app);
    }

    public function testGetChange()
    {
        // set up Slim mock environment
        $environment = \Slim\Http\Environment::mock();
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        // create test response
        $response = new \Slim\Http\Response();
        $response = $this->_api->getChange($request, $response, ['owed'=>0.01, 'paid'=>0.07, 'lang'=>'en-US']);
        $response->getBody()->rewind();

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response);
        $this->assertNotNull($response->getBody());

        $contents = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('owed', $contents);
        $this->assertArrayHasKey('paid', $contents);
        $this->assertArrayHasKey('lang', $contents);
        $this->assertArrayHasKey('change', $contents);
        $this->assertArrayHasKey('value', $contents);

        $this->assertTrue(is_numeric($contents['owed']));
        $this->assertTrue($contents['owed'] >= 0);

        $this->assertTrue(is_numeric($contents['paid']));
        $this->assertTrue($contents['paid'] >= 0);

        $this->assertTrue(is_numeric($contents['value']));
        $this->assertTrue($contents['value'] >= 0);

        $this->assertRegExp('/^\d+\s{1}\D+\,\d+\s{1}\D+$/', $contents['change']);

        $this->assertSame($contents['owed'], '0.01');
        $this->assertSame($contents['paid'], '0.07');
        $this->assertSame($contents['lang'], 'en-US');
        $this->assertSame($contents['change'], '1 nickel,1 penny');
        $this->assertSame($contents['value'], '0.06');

        // create new test response, with randomized divisor
        $response = new \Slim\Http\Response();
        $response = $this->_api->getChange($request, $response, ['owed'=>0.03, 'paid'=>100.00, 'lang'=>'en-US']);
        $response->getBody()->rewind();

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response);
        $this->assertNotNull($response->getBody());

        $contents_1 = json_decode($response->getBody()->getContents(), true);

        // create another new test response, with randomized divisor
        $response = new \Slim\Http\Response();
        $response = $this->_api->getChange($request, $response, ['owed'=>0.03, 'paid'=>100.00, 'lang'=>'en-US']);
        $response->getBody()->rewind();

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response);
        $this->assertNotNull($response->getBody());

        $contents_2 = json_decode($response->getBody()->getContents(), true);

        $this->assertSame($contents_1['owed'], $contents_2['owed']);
        $this->assertSame($contents_1['paid'], $contents_2['paid']);
        $this->assertSame($contents_1['lang'], $contents_2['lang']);
        $this->assertSame($contents_1['value'], $contents_2['value']);
        // theoretically could fail, with astronomical probability
        $this->assertNotSame($contents_1['change'], $contents_2['change']);
    }

    public function testGetChangeDenomination()
    {
        // set up Slim mock environment
        $environment = \Slim\Http\Environment::mock();
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        // create test response
        $response = new \Slim\Http\Response();
        $response = $this->_api->getChangeDenomination($request, $response, ['lang'=>'en-US']);
        $response->getBody()->rewind();

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response);
        $this->assertNotNull($response->getBody());

        $contents = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue(is_array($contents));
        $this->assertNotTrue(empty($contents));

        $this->assertArrayHasKey('lang', $contents[0]);
        $this->assertArrayHasKey('name', $contents[0]);
        $this->assertArrayHasKey('plural', $contents[0]);
        $this->assertArrayHasKey('value', $contents[0]);

        $this->assertTrue(is_numeric($contents[0]['value']));
    }
}
