function Updater(config) {
    let self = this;
    let _deafultConfig = {
        updateInterval: 1000,
        userId: null,
        eventCallback: function (data) {},
        connectionErrorCallback: function () {}
    };
    this._config = Object.assign({}, _deafultConfig, config);

    let isActive = false;
    let lastState = {};

    let errorCounter = 20;

    this.activate = function () {
        isActive = true;
    };

    this.deactivate = function () {
        isActive = false;
    };

    this.init = function () {
        setInterval(function () {
            if (isActive) {
                $.ajax({
                    url: '/status/get',
                    type: 'POST',
                    data: {userId: self._config.userId},
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === 'update') {
                            lastState = data.data;
                            self._config.eventCallback(lastState);
                            $.post('/status/confirm');
                        } else {
                            if (lastState === {} && data.data !== []) {
                                lastState = data.data;
                                self._config.eventCallback(lastState);
                            }
                        }
                    },
                    error: function () {
                        errorCounter--;
                    }
                });
                if (errorCounter < 0) {
                    self.deactivate();
                    errorCounter = 20;
                    self._config.connectionErrorCallback()
                }
            }
        }, self._config.updateInterval)
    }
}