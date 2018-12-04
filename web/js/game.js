/*
function Game(config) {
    let self = this;
    this.gameId = null; // Данная переменная будет содержать уникальный ID игры.
        this.turn = null; // Чем будет ходить игрок, будет содержать X или O
        this.i = false; // Чей сейчас ход, игрока или соперника
        this.init = function() { ... }; // Здесь будут основные обработчики взаимодействия с серверной частью
    this.startGame = function (gameId, turn, x, y) { ... }; // Генерация игрового поля и установка всех параметров игры
    this.block = function(state) { ... }; // Маска на игровое поля, чтобы нельзя было ничего нажимать когда ходит противник и просто красиво :)
    this.move = function (id, turn, win) { ... }; // Отметка хода на игровом поле
    this.endGame = function (turn, win) { ... }; // Конец игры, вывод сообщения
}
*/

$(document).ready(function () {
    clearField();
    $('.xo__field').on('click', '.xo__cells', function () {
        $(this).addClass('xo__cells-o');
    })
});

function clearField() {
    $('.xo__cells').removeClass('xo__cells-o xo__cells-x');
}