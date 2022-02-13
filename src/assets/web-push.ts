enum SubscriptionStatus {
  SUBSCRIBED = 'subscribed',
  UNSUBSCRIBED = 'unsubscribed',
  UNKNOWN = 'unknown',
  BLOCKED = 'blocked',
}

class WebPush {
  readonly appId;
  readonly publicKey;
  readonly controller;

  private readonly LS = {
    ENDPOINT: "yii2-wpn-endpoint",
    LAST_SYNC: "yii2-wpn-last_sync",
  };
  private readonly syncInterval: number;

  constructor(
    appId: number,
    publicKey: string,
    controller = "/wpn/default",
    syncInterval = 1000 * 60 * 10
  ) {
    this.appId = appId;
    this.publicKey = publicKey;
    this.controller = controller;
    this.syncInterval = syncInterval;
  }

  setupRegistration(swPath = "/sw.js", successCb = (status: SubscriptionStatus) => {}, failureCb = (error) => {}, shouldMigrate = (context) => false) {
    if (!("serviceWorker" in navigator)) {
      this.log("Service workers are not supported by this browser");
      return false;
    } else if (!("PushManager" in window)) {
      this.log("Push notifications are not supported by this browser");
      return false;
    } else if (!("showNotification" in ServiceWorkerRegistration.prototype)) {
      this.log("Notifications are not supported by this browser");
      return false;
    } else if (Notification.permission === "denied") {
      this.log("Notifications are denied by the user");
      return false;
    }

    navigator.serviceWorker.register(swPath).then(
      () => {
        const lastSync = parseInt(localStorage.getItem(this.LS.LAST_SYNC));
        const now = Date.now();

        if (!lastSync || (now - lastSync > this.syncInterval)) {
          console.log(now, lastSync, now - lastSync, this.syncInterval)
          this.checkSubscription(successCb, failureCb, shouldMigrate);
          localStorage.setItem(this.LS.LAST_SYNC, String(now));
        }
      },
      (err) => {
        this.log("ServiceWorker registration failed: ", err);
      }
    );
  }

  checkSubscription(successCb = (status: SubscriptionStatus) => {}, failureCb = (error) => {}, shouldMigrate = (context) => false) {
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration =>
        serviceWorkerRegistration.pushManager.getSubscription()
      )
      .then(subscription => {
        if (!subscription) {
          // We aren't subscribed to push, so set UI to allow the user to enable push
          successCb(SubscriptionStatus.UNSUBSCRIBED);
          return;
        }

        // We are subscribed, give the possibility to migrate from an old provider
        if (shouldMigrate(this)) {
          return subscription
              .unsubscribe()
              .then(() => navigator.serviceWorker.ready)
              .then(serviceWorkerRegistration =>
                  serviceWorkerRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
                  })
              )
              .then(subscription => {
                return this.sync(subscription, "POST");
              });
        }

        return this.sync(subscription, "PUT");
      })
      .then(
        subscription => subscription && successCb(SubscriptionStatus.SUBSCRIBED)
      ) // Set your UI to show they have subscribed for push messages
      .catch(e => {
        this.log("Error when updating the subscription", e);
        failureCb(e);
      });
  }

  subscribe(success = (s: PushSubscription) => {}, failure = (error) => {}) {
    return this.checkNotificationPermission()
      .then(() => navigator.serviceWorker.ready)
      .then(serviceWorkerRegistration =>
        serviceWorkerRegistration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
        })
      )
      .then((subscription: PushSubscription) => {
        // Subscription was successful
        // create subscription on your server
        localStorage.setItem(this.LS.ENDPOINT, subscription.endpoint);
        return this.sync(subscription, "POST");
      })
      .then(
        subscription => success(subscription)
      ) // update your UI
      .catch(e => {
        if (Notification.permission === "denied") {
          // The user denied the notification permission which
          // means we failed to subscribe and the user will need
          // to manually change the notification permission to
          // subscribe to push messages
          failure("Notifications are denied by the user.");
        } else {
          // A problem occurred with the subscription; common reasons
          // include network errors or the user skipped the permission
          failure("Impossible to subscribe to push notifications : " + e);
        }
      });
  }

  unsubscribe(success = () => {}, failure = () => {}) {
    // To unsubscribe from push messaging, you need to get the subscription object
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration =>
        serviceWorkerRegistration.pushManager.getSubscription()
      )
      .then(subscription => {
        // Check that we have a subscription to unsubscribe
        if (!subscription) {
          // No subscription object, so set the state
          // to allow the user to subscribe to push
          success();
          return;
        }

        // We have a subscription, unsubscribe
        // Remove push subscription from server
        return this.sync(subscription, "DELETE");
      })
      .then(subscription => subscription.unsubscribe())
      .then(() => {
        localStorage.removeItem(this.LS.ENDPOINT);
        success();
      })
      .catch(e => {
        // We failed to unsubscribe, this can lead to
        // an unusual state, so  it may be best to remove
        // the users data from your data store and
        // inform the user that you have done so
        this.log("Error when unsubscribing the user", e);
        failure();
      });
  }

  sync(subscription, method): Promise<PushSubscription> {
    const contentEncoding = (PushManager.supportedContentEncodings || [
      "aesgcm"
    ])[0];

    localStorage.setItem(this.LS.ENDPOINT, subscription.endpoint);
    return new Promise((resolve, reject) => {
      $.ajax({
        url: `${this.controller}/sync?appId=${this.appId}`,
        type: method,
        data: JSON.stringify({
          endpoint: subscription.endpoint,
          publicKey: this.encode(subscription.getKey("p256dh")),
          authToken: this.encode(subscription.getKey("auth")),
          contentEncoding,
          ...this.getCsrfParams()
        }),
        success: result => {
          resolve(subscription);
        },
        error: err => {
          this.log("WPN Sync error: ", err);
          reject(err);
        }
      });
    });
  }

  checkNotificationPermission() {
    return new Promise<void>((resolve, reject) => {
      if (Notification.permission === "denied") {
        return reject(new Error("Push messages are blocked."));
      }

      if (Notification.permission === "granted") {
        return resolve();
      }

      if (Notification.permission === "default") {
        return Notification.requestPermission().then(result => {
          if (result !== "granted") {
            reject(new Error("Bad permission result"));
          } else {
            resolve();
          }
        });
      }

      return reject(new Error("Unknown permission"));
    });
  }

  urlBase64ToUint8Array(base64String) {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, "+")
      .replace(/_/g, "/");

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  getCsrfParams() {
    return {
      [jQuery("meta[name=csrf-param]").attr("content")]: jQuery(
          "meta[name=csrf-token]"
      ).attr("content")
    };
  }

  encode(str) {
    if (!str) {
      return null;
    }

    return btoa(String.fromCharCode.apply(null, new Uint8Array(str)));
  }

  log(...params) {
    console.log(...params);
  }
}