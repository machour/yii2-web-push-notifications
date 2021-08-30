<?php

/* @var $this \yii\web\View */
/* @var $app \machour\yii2\wpn\models\WpnApp */

$this->registerJs(<<<JS
$(() => {
  var wp = new WebPush({$app->id}, "{$app->public_key}");
  var wpnStatus = $('.wpn-status');
  $(".wpn-subscribe").on("click", function() {
    wp.subscribe(function(subscription) {
      wpnStatus.text("Subscribed, endpoint is" + subscription.endpoint);
    }, function (error) { 
      wpnStatus.text("Got error: " + error);
     });
  });

  $(".wpn-unsubscribe").on("click", function() {
    wp.unsubscribe(function () { 
      wpnStatus.text("Unsubscribed");
     }, function () { 
      wpnStatus.text("Error while unsubscribing");
      });
  });

  wp.checkSubscription(function(status) {
    wpnStatus.text(status);
  }, function(error) {
    wpnStatus.text("error");
    console.error(error);
  });


});
JS
);

?>

<div class="wpn wpn-subscription">
    <div class="wpn-status">unknown</div>
    <button class="wpn-subscribe">Subscribe</button>
    <button class="wpn-unsubscribe">Unsubscribe</button>
</div>
