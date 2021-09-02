const reportAction = (payload) => {
  console.log("Broadcasting message", payload)
  fetch(`/web-push/report`, {
    method: 'POST',
    body: JSON.stringify(payload)
  }).then(() => {
      console.log("Action reported");
    }
  ).catch(error => {
    console.log("Action reporting failed", error);
  });
}

self.addEventListener("push", function (event) {
  console.log("in push event listener", event);
  if (!(self.Notification && self.Notification.permission === "granted")) {
    console.error("No notification ?");
    return;
  }

  const sendNotification = payload => {
    console.log("Got payload", payload);
    const decoded = JSON.parse(payload);

    const {title, ...options} = decoded;

    return self.registration.showNotification(title, options).then(() => {
      reportAction( {
        action: "view",
        endpoint: event.notification.data.endpoint,
        campaignId: event.notification.data.campaignId
      });
    });
  };

  if (event.data) {
    const message = event.data.text();
    event.waitUntil(sendNotification(message));

  }
});

self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  // This looks to see if the current window is already open and focuses if it is
  event.waitUntil(
    clients.matchAll({
      type: "window"
    }).then(function () {
      if (clients.openWindow) {
        const data = event.notification.data; // the payload from before
        return clients.openWindow(data.url); // open it
      }
    }).then(() => {
      reportAction( {
        action: "click",
        endpoint: event.notification.data.endpoint,
        campaignId: event.notification.data.campaignId
      });
    })
  );
});

self.addEventListener("install", function (e) {
  e.waitUntil(self.skipWaiting());
});

self.addEventListener("notificationclose", function (e) {
  reportAction( {
    action: "dismiss",
    campaignId: e.notification.data.campaignId,
    endpoint: e.notification.data.endpoint,
  });
});

self.addEventListener("activate", function (e) {
  e.waitUntil(self.clients.claim());
});