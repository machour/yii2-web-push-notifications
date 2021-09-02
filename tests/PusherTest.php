<?php

namespace tests;

use machour\yii2\wpn\exceptions\InvalidApplication;
use machour\yii2\wpn\models\WpnSubscription;
use machour\yii2\wpn\Module;
use Minishlink\WebPush\WebPush;
use tests\fixtures\WpnAppFixture;
use tests\fixtures\WpnCampaignFixture;
use tests\fixtures\WpnSubscriptionFixture;

class PusherTest extends \Codeception\Test\Unit
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
            ],
            'campaign' => [
                'class' => WpnCampaignFixture::class,
            ],
            'subscription' => [
                'class' => WpnSubscriptionFixture::class,
            ],
        ]);

        $stub = $this->make(WebPush::class, [
            'queueNotification' => function() {

            },
            'flush' => function() {
                return [1];
            }
        ]);
        \Yii::$container->set(WebPush::class, $stub);


        $module = Module::getInstance();
        $this->pusher = $module->get('pusher');

    }

    // tests
    public function testRefusingDisabledApplication()
    {
        $campaign = $this->tester->grabFixture('campaign', 1);
        $this->assertThrows(InvalidApplication::class, function() use ($campaign) {
            $this->pusher->sendPush($campaign);
        });
    }


    public function testMock()
    {
        $campaign = $this->tester->grabFixture('campaign', 0);

        $this->pusher->sendPush($campaign);
    }
}