<?php

// vendor/bin/phpunit --configuration phpunit.xml --colors=always

namespace API;

use PHPUnit\Framework\TestCase;

require_once(realpath(dirname(__FILE__) . '/../../config.php'));
require_once(realpath(dirname(__FILE__) . '/../Translation.php'));

class TranslationTest extends TestCase
{
    private $_app;
    private $_api;

    public function setUp()
    {
        $this->_app = new \Slim\App();
        $this->_api = new Translation($this->_app);
    }

    public function testGetTranslation()
    {
        // set up Slim mock environment
        $environment = \Slim\Http\Environment::mock();
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        // create test response
        $response = new \Slim\Http\Response();
        $response = $this->_api->getTranslation($request, $response, [
            'lang'=>'en-US',
            'code'=>'PHPUNIT_TEST'
        ]);
        $response->getBody()->rewind();

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response);
        $this->assertNotNull($response->getBody());

        $contents = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue(is_array($contents));
        $this->assertNotTrue(empty($contents));

        $this->assertArrayHasKey('lang', $contents);
        $this->assertArrayHasKey('code', $contents);
        $this->assertArrayHasKey('text', $contents);

        $this->assertSame($contents['lang'], 'en-US');
        $this->assertSame($contents['code'], 'PHPUNIT_TEST');
        $this->assertSame($contents['text'], 'phpunit test');
    }
}
