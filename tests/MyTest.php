<?php
namespace machour\yii2\wpn\tests;

use machour\yii2\wpn\tests\fixtures\WpnAppFixture;

class MyTest extends \Codeception\Test\Unit
{
    /**
     * @var \machour\yii2\wpn\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => WpnAppFixture::class,
                'dataFile' => codecept_data_dir() . 'wpn_app.php'
            ]
        ]);
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {

    }
}