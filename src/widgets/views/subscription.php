<?php

/* @var $this \yii\web\View */
/* @var $app \machour\yii2\wpn\models\WpnApp */
/* @var $shouldMigrate string */

if (!$shouldMigrate) {
    $shouldMigrate = 'function() { return false; }';
}

$this->registerJs(<<<JS
jQuery(() => {
  var wp = new WebPush({$app->id}, "{$app->public_key}");
  var wpnStatus = jQuery('.wpn-status');
  jQuery(".wpn-subscribe").on("click", function() {
    wp.subscribe(function(subscription) {
      wpnStatus.text("Subscribed, endpoint is" + subscription.endpoint);
    }, function (error) { 
      wpnStatus.text("Got error: " + error);
     });
  });

  jQuery(".wpn-unsubscribe").on("click", function() {
    wp.unsubscribe(function () { 
      wpnStatus.text("Unsubscribed");
     }, function () { 
      wpnStatus.text("Error while unsubscribing");
      });
  });

  wp.setupRegistration("/sw.js", function(status) {
    wpnStatus.text(status);
  }, function(error) {
    wpnStatus.text("error");
    console.error(error);
  }, $shouldMigrate);


});
JS
);

?>

<div class="wpn wpn-subscription">
    <div class="wpn-status">unknown</div>
    <button class="wpn-subscribe">Subscribe</button>
    <button class="wpn-unsubscribe">Unsubscribe</button>
</div>
