<?php

namespace tests;

use GuzzleHttp\Exception\RequestException;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Notification;
use Minishlink\WebPush\SubscriptionInterface;
use Minishlink\WebPush\WebPush;
use Psr\Http\Message\ResponseInterface;

class WebPushMock extends WebPush
{
    public $notifications;

    public function queueNotification(SubscriptionInterface $subscription, ?string $payload = null, array $options = [], array $auth = []): void
    {
        $this->notifications[] = new Notification($subscription, $payload, $options, $auth);
    }

    public function flush(?int $batchSize = null): \Generator
    {
        if (null === $this->notifications || empty($this->notifications)) {
            yield from [];
            return;
        }

        if (null === $batchSize) {
            $batchSize = $this->defaultOptions['batchSize'];
        }

        $batches = array_chunk($this->notifications, $batchSize);

        // reset queue
        $this->notifications = [];

        foreach ($batches as $batch) {
            // for each endpoint server type
            $requests = $this->prepare($batch);

            $promises = [];

            foreach ($requests as $request) {
                $promises[] = $this->client->sendAsync($request)
                    ->then(function ($response) use ($request) {
                        /** @var ResponseInterface $response * */
                        return new MessageSentReport($request, $response);
                    })
                    ->otherwise(function ($reason) {
                        /** @var RequestException $reason **/
                        if (method_exists($reason, 'getResponse')) {
                            $response = $reason->getResponse();
                        } else {
                            $response = null;
                        }
                        return new MessageSentReport($reason->getRequest(), $response, false, $reason->getMessage());
                    });
            }

            foreach ($promises as $promise) {
                yield $promise->wait();
            }
        }

        if ($this->reuseVAPIDHeaders) {
            $this->vapidHeaders = [];
        }
    }

}