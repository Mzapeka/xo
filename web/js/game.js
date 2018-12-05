function Game(userId, userName) {
    let self = this;
    this.userId = userId;
    this.userName = userName;
    this.turn = null;
    this.i = false;
    this.board = [];
    this.isBlocked = true;

    this.init = function () {
        this.clearField();
        $('.xo__field').on('click', '.xo__cells', function (event) {
            if (self.isBlocked) {
                return;
            }
            self.move(x, y).success(function () {
                self.waitingPreloaderStart();
            });
        });

        $('.end-game').on('click', function () {
            self.endGame();
        });

        $('.new-game').on('click', function () {
            $(location).attr('href','/start/' + encodeURIComponent(self.userName))
        });


        this.startInitPreloader();
        this.updater.init();
        this.updater.activate();
    };

    this.block = function () {
        this.isBlocked = true;
    };

    this.unblock = function () {
        this.isBlocked = false;
    };

    this.move = function (x, y) {
        this.block();
        return $.post('game/step', {x: x, y: y})
            .error(function () {
                self.showInfo({type: 'error', text: 'Server Error. Try make step again'})
                self.unblock();
            })
    };

    this.endGame = function () {
        $.post('game/end').error(function () {
            self.showInfo({type: 'error', text: 'End game Error'});
        })
    };

    this.clearField = function () {
        $('.xo__cells').removeClass('xo__cells-o xo__cells-x');
    };

    this.startInitPreloader = function () {
        document.getElementById('preloaderbg').style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    this.stopInitPreloader = function () {
        document.getElementById('preloaderbg').style.display = 'none';
        document.body.style.overflow = 'visible';
    };

    this.showInfo = function (parameters) {
        let {type, text} = parameters;
        let alert = '<div class="alert alert-' + type + ' alert-dismissable">\n' +
            '  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\n' +
            text + '</div>\n';
        $('div.informer').empty().html(alert);
    };

    this.renderField = function () {
        this.clearField();
        $('.xo__cells').each(function (element) {
            let x = element.attr('data-x');
            let y = element.attr('data-y');
            let turnClass = '';
            if (typeof self.board[y] !== 'undefined' && typeof self.board[y][x] !== 'undefined') {
                if (self.board[y][x] === 'X') {
                    turnClass = 'xo__cells-x';
                }
                if (self.board[y][x] === 'O') {
                    turnClass = 'xo__cells-o';
                }
                element.addClass(turnClass);
            }
        });
    };

    this.endGameScrinShow = function (state) {

    };

    this.stateHandler = function (state) {
        self.waitingPreloaderStop();
        self.stopInitPreloader();
        if (typeof (state) === 'object') {
            self.board = state.board;
            self.turn = state.currentTurn;

            self.renderField();

            if (state.activeUser === self.userId) {
                self.i = true;
                self.updater.deactivate();
            } else {
                self.i = false;
                self.updater.activate();
            }

            if (state.winner) {
                self.updater.deactivate();
                if (state.winner === 'none') {
                    this.endGameScrinShow();
                    return;
                }
                let winnerName = state.winner === self.userId ?self.userName : state.opponentName;
                self.endGameScrinShow(state);
            }
        }
    };

    this.updater = new Updater({
        eventCallback: function (state) {
            self.stateHandler(state);
        }
    });
}


$(document).ready(function () {

});


