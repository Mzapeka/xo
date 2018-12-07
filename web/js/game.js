function Game(userId, userName) {
    let self = this;
    this.userId = userId;
    this.userName = userName;
    this.turn = null;
    this.i = false;
    this.board = [];
    this.isBlocked = true;

    this.init = function () {
        this.waitingPreloaderStop();
        this.clearField();
        $('.xo__field').on('click', '.xo__cells', function (event) {
            console.log(event);
            console.log(event);
            if (self.isBlocked) {
                return;
            }
            let x = $(this).attr('data-x');
            let y = $(this).attr('data-y');
            self.move(x, y).done(function () {
                self.waitingPreloaderStart();
                self.block();
                self.updater.activate()
            });
        });

        $('#end-game').on('click', function (event) {
            self.endGame(event);
        });

        $('.informer').on('click', '.new-game', function () {
            $(location).attr('href', '/game/start/' + encodeURIComponent(self.userName))
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
        return $.post('/game/step', {x: x, y: y})
            .fail(function (data) {
                self.showInfo({type: 'error', text: 'Server Error. Try make step again'});
                self.unblock();
            });
    };

    this.endGame = function (event) {
        $.post('/game/end')
            .fail(function () {
                self.showInfo({type: 'error', text: 'End game Error'});
            })
            .done(function () {
                event.preventDefault();
                $('body').animate({
                    opacity: 0
                }, 1000);

                setTimeout(function() {
                    $(location).attr('href','/');
                },1000);
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

    self.waitingPreloaderStart = function () {
        document.getElementById('preloader-start').style.display = 'flex';
        $('.wrapper').css({opacity: '50%'});
    };

    self.waitingPreloaderStop = function () {
        document.getElementById('preloader-start').style.display = 'none';
        $('.wrapper').css({opacity: '100%'});
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
            let x = $(this).attr('data-x');
            let y = $(this).attr('data-y');
            let turnClass = '';
            if (typeof self.board[y] !== 'undefined' && typeof self.board[y][x] !== 'undefined') {
                if (self.board[y][x] === 'X') {
                    turnClass = 'xo__cells-x';
                }
                if (self.board[y][x] === 'O') {
                    turnClass = 'xo__cells-o';
                }
                $(this).addClass(turnClass);
            }
        });
    };

    this.endGameScrinShow = function (state) {
        if (state.winner === 'not_win') {
            message = 'Ничья!';
        } else {
            message = 'You ' + (state.winner === self.userId ? 'WIN!!! ' : 'lose ( ');
        }

        message += '<button id="new-game" class="btn btn-sm btn-success new-game">New Game</button>';

        self.showInfo({type: (state.winner === self.userId ? "success" : "danger"), text: message});
    };

    this.stateHandler = function (state) {
        self.waitingPreloaderStop();
        self.stopInitPreloader();
        $('.panel .panel-body').text(state.yourName + ' VS ' + state.opponentName);
        if (typeof (state) === 'object') {
            self.board = state.board;
            self.turn = state.currentTurn;

            self.renderField();

            if (state.activeUser === self.userId) {
                self.showInfo({type: 'warning', text: 'Your turn: ' + self.turn});
                self.i = true;
                self.updater.deactivate();
                self.unblock();
            } else {
                self.showInfo({type: 'success', text: 'Opponent turn'});
                self.i = false;
                self.updater.activate();
                self.block();
            }

            if (state.winner) {
                self.updater.deactivate();
                self.endGameScrinShow(state);
            }
        }
    };

    this.updater = new Updater({
        eventCallback: function (state) {
            self.stateHandler(state);
        },
        connectionErrorCallback: function () {
            self.showInfo({type: 'warning', text: 'Connection error'});
            self.stopInitPreloader();
        },
        userId: this.userId,
    });
}

let game = new Game(USER_ID, USER_NAME);
game.init();



