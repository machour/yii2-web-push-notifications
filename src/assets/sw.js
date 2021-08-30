const wpnBroadcast = new BroadcastChannel("yii2-web-push-notifications");

self.addEventListener("push", function(event) {
  console.log("in push event listener", event);
  if (!(self.Notification && self.Notification.permission === "granted")) {
    console.error("No notification ?");
    return;
  }

  const sendNotification = payload => {
    console.log("Got payload", payload);
    const decoded = JSON.parse(payload);

    const { title, ...options } = decoded;

    return self.registration.showNotification(title, options);
  };

  if (event.data) {
    const message = event.data.text();
    event.waitUntil(sendNotification(message));
  }
});

self.addEventListener("notificationclick", function(event) {
  event.notification.close();
  // This looks to see if the current window is already open and focuses if it is
  event.waitUntil(
    clients
      .matchAll({
        type: "window"
      })
      .then(function() {
        if (clients.openWindow) {
          const data = event.notification.data; // the payload from before
          return clients.openWindow(data.url); // open it
        }
      })
      .then(() => {
        wpnBroadcast.postMessage({
          action: "click",
          campaignId: event.notification.data.campaignId
        });
      })
  );
});

self.addEventListener("install", function(e) {
  e.waitUntil(self.skipWaiting());
});

self.addEventListener("notificationclose", function(e) {
  wpnBroadcast.postMessage({
    action: "dismiss",
    campaignId: e.notification.data.campaignId
  });
});

self.addEventListener("activate", function(e) {
  e.waitUntil(self.clients.claim());
});