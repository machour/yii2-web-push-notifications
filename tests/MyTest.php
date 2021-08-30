<?php

namespace tests;

use machour\yii2\wpn\exceptions\InvalidApplication;
use machour\yii2\wpn\Module;
use Minishlink\WebPush\WebPush;
use tests\fixtures\WpnAppFixture;
use tests\fixtures\WpnCampaignFixture;

class MyTest extends \Codeception\Test\Unit
{
    use \Codeception\AssertThrows;

    /**
     * @var UnitTester
     */
    protected $tester;

    protected $pusher;

    protected function _before()
    {
        $this->tester->haveFixtures([
            'app' => [
                'class' => WpnAppFixture::class,
                'dataFile' => codecept_data_dir() . 'wpn_app.php',
            ],
            'campaign' => [
                'class' => WpnCampaignFixture::class,
            ],
        ]);

        $module = Module::getInstance();
        $this->pusher = $module->get('pusher');

    }

    protected function _after()
    {

    }

    // tests
    public function testRefusingDisabledApplication()
    {
        $invalidApplication = $this->tester->grabFixture('campaign', 1);
        $this->assertThrows(InvalidApplication::class, function() use ($invalidApplication) {
            $this->pusher->sendPush($invalidApplication);
        });
    }
}