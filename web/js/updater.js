function Updater(config) {
    let self = this;
    let _deafultConfig = {
        updateInterval: 500,
        eventCallback: function (data) {},
    };
    this._config = Object.assign({}, _deafultConfig, config);

    let isActive = false;
    let lastState = {};

    this.activate = function () {
        isActive = true;
    };

    this.deactivate = function () {
        isActive = false;
    };

    this.init = function () {
        setInterval(function () {
            if (isActive) {
                $.ajax('game/status', {
                    url: 'game/status',
                    type: 'POST',
                    dataType: 'json',
                    success: function (data) {
                        if(data.status === 'update') {
                            lastState = data.data;
                            self._config.eventCallback(lastState);
                        }
                    }
                });
            }
        }, self._config.updateInterval)
    }
}