var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var SubscriptionStatus;
(function (SubscriptionStatus) {
    SubscriptionStatus["SUBSCRIBED"] = "subscribed";
    SubscriptionStatus["UNSUBSCRIBED"] = "unsubscribed";
    SubscriptionStatus["UNKNOWN"] = "unknown";
    SubscriptionStatus["BLOCKED"] = "blocked";
})(SubscriptionStatus || (SubscriptionStatus = {}));
var WebPush = /** @class */ (function () {
    function WebPush(appId, publicKey, controller, localStorageKey) {
        var _this = this;
        if (controller === void 0) { controller = "/wpn/default"; }
        if (localStorageKey === void 0) { localStorageKey = "yii2-wpn-endpoint"; }
        this.appId = appId;
        this.publicKey = publicKey;
        this.controller = controller;
        this.localStorageKey = localStorageKey;
        var wpnBroadcast = new BroadcastChannel("yii2-web-push-notifications");
        wpnBroadcast.onmessage = function (event) {
            var _a = event.data, action = _a.action, campaignId = _a.campaignId;
            $.post(_this.controller + "/report?appId=" + _this.appId, __assign({ endpoint: localStorage.getItem(_this.localStorageKey), action: action,
                campaignId: campaignId }, _this.getCsrfParams()), function () {
                _this.log("Action reported", action);
            }).fail(function (error) {
                _this.log("Action reporting failed", action, error);
            });
        };
    }
    WebPush.prototype.setupRegistration = function (swPath) {
        var _this = this;
        if (swPath === void 0) { swPath = "/sw.js"; }
        if (!("serviceWorker" in navigator)) {
            this.log("Service workers are not supported by this browser");
            return false;
        }
        else if (!("PushManager" in window)) {
            this.log("Push notifications are not supported by this browser");
            return false;
        }
        else if (!("showNotification" in ServiceWorkerRegistration.prototype)) {
            this.log("Notifications are not supported by this browser");
            return false;
        }
        else if (Notification.permission === "denied") {
            this.log("Notifications are denied by the user");
            return false;
        }
        navigator.serviceWorker.register(swPath).then(function (registration) {
            _this.log("ServiceWorker registration success: ", registration);
            _this.checkSubscription();
        }, function (err) {
            this.log("ServiceWorker registration failed: ", err);
        });
    };
    WebPush.prototype.checkSubscription = function (successCb, failureCb, shouldMigrate) {
        var _this = this;
        if (successCb === void 0) { successCb = function (status) { }; }
        if (failureCb === void 0) { failureCb = function (error) { }; }
        if (shouldMigrate === void 0) { shouldMigrate = function (context) { return false; }; }
        navigator.serviceWorker.ready
            .then(function (serviceWorkerRegistration) {
            return serviceWorkerRegistration.pushManager.getSubscription();
        })
            .then(function (subscription) {
            if (!subscription) {
                // We aren't subscribed to push, so set UI to allow the user to enable push
                successCb(SubscriptionStatus.UNSUBSCRIBED);
                return;
            }
            // We are subscribed, give the possibility to migrate from an old provider
            if (shouldMigrate(_this)) {
                return subscription
                    .unsubscribe()
                    .then(function () { return navigator.serviceWorker.ready; })
                    .then(function (serviceWorkerRegistration) {
                    return serviceWorkerRegistration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: _this.urlBase64ToUint8Array(_this.publicKey)
                    });
                })
                    .then(function (subscription) {
                    return _this.sync(subscription, "POST");
                });
            }
            return _this.sync(subscription, "PUT");
        })
            .then(function (subscription) { return subscription && successCb(SubscriptionStatus.SUBSCRIBED); }) // Set your UI to show they have subscribed for push messages
        ["catch"](function (e) {
            _this.log("Error when updating the subscription", e);
            failureCb(e);
        });
    };
    WebPush.prototype.subscribe = function (success, failure) {
        var _this = this;
        if (success === void 0) { success = function (s) { }; }
        if (failure === void 0) { failure = function (error) { }; }
        return this.checkNotificationPermission()
            .then(function () { return navigator.serviceWorker.ready; })
            .then(function (serviceWorkerRegistration) {
            return serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: _this.urlBase64ToUint8Array(_this.publicKey)
            });
        })
            .then(function (subscription) {
            // Subscription was successful
            // create subscription on your server
            localStorage.setItem(_this.localStorageKey, subscription.endpoint);
            return _this.sync(subscription, "POST");
        })
            .then(function (subscription) { return success(subscription); }) // update your UI
        ["catch"](function (e) {
            if (Notification.permission === "denied") {
                // The user denied the notification permission which
                // means we failed to subscribe and the user will need
                // to manually change the notification permission to
                // subscribe to push messages
                failure("Notifications are denied by the user.");
            }
            else {
                // A problem occurred with the subscription; common reasons
                // include network errors or the user skipped the permission
                failure("Impossible to subscribe to push notifications : " + e);
            }
        });
    };
    WebPush.prototype.unsubscribe = function (success, failure) {
        var _this = this;
        if (success === void 0) { success = function () { }; }
        if (failure === void 0) { failure = function () { }; }
        // To unsubscribe from push messaging, you need to get the subscription object
        navigator.serviceWorker.ready
            .then(function (serviceWorkerRegistration) {
            return serviceWorkerRegistration.pushManager.getSubscription();
        })
            .then(function (subscription) {
            // Check that we have a subscription to unsubscribe
            if (!subscription) {
                // No subscription object, so set the state
                // to allow the user to subscribe to push
                success();
                return;
            }
            // We have a subscription, unsubscribe
            // Remove push subscription from server
            return _this.sync(subscription, "DELETE");
        })
            .then(function (subscription) { return subscription.unsubscribe(); })
            .then(function () {
            localStorage.removeItem(_this.localStorageKey);
            success();
        })["catch"](function (e) {
            // We failed to unsubscribe, this can lead to
            // an unusual state, so  it may be best to remove
            // the users data from your data store and
            // inform the user that you have done so
            _this.log("Error when unsubscribing the user", e);
            failure();
        });
    };
    WebPush.prototype.sync = function (subscription, method) {
        var _this = this;
        var contentEncoding = (PushManager.supportedContentEncodings || [
            "aesgcm"
        ])[0];
        localStorage.setItem(this.localStorageKey, subscription.endpoint);
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: _this.controller + "/sync?appId=" + _this.appId,
                type: method,
                data: JSON.stringify(__assign({ endpoint: subscription.endpoint, publicKey: _this.encode(subscription.getKey("p256dh")), authToken: _this.encode(subscription.getKey("auth")), contentEncoding: contentEncoding }, _this.getCsrfParams())),
                success: function (result) {
                    resolve(subscription);
                },
                error: function (err) {
                    _this.log("WPN Sync error: ", err);
                    reject(err);
                }
            });
        });
    };
    WebPush.prototype.checkNotificationPermission = function () {
        return new Promise(function (resolve, reject) {
            if (Notification.permission === "denied") {
                return reject(new Error("Push messages are blocked."));
            }
            if (Notification.permission === "granted") {
                return resolve();
            }
            if (Notification.permission === "default") {
                return Notification.requestPermission().then(function (result) {
                    if (result !== "granted") {
                        reject(new Error("Bad permission result"));
                    }
                    else {
                        resolve();
                    }
                });
            }
            return reject(new Error("Unknown permission"));
        });
    };
    WebPush.prototype.urlBase64ToUint8Array = function (base64String) {
        var padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding)
            .replace(/\-/g, "+")
            .replace(/_/g, "/");
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    };
    WebPush.prototype.getCsrfParams = function () {
        var _a;
        return _a = {},
            _a[jQuery("meta[name=csrf-param]").attr("content")] = jQuery("meta[name=csrf-token]").attr("content"),
            _a;
    };
    WebPush.prototype.encode = function (str) {
        if (!str) {
            return null;
        }
        return btoa(String.fromCharCode.apply(null, new Uint8Array(str)));
    };
    WebPush.prototype.log = function () {
        var params = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            params[_i] = arguments[_i];
        }
        console.log.apply(console, params);
    };
    return WebPush;
}());
