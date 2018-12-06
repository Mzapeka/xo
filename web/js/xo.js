$(document).ready(function () {
    let name = null;
    let isStarted = false;


    $('#enter-name').on('click', function (event) {
        if (name === null) {
            $('#add-name-modal').modal('show');
            return;
        }
        alert({type: 'warning', text: 'Name is already set'});
    });

    $('#submit-name').on('click', function (event) {
        let nameString = $('#input-name').val();
        if (nameString.length > 20) {
            alert({type: 'warning', text: 'Length of name should be less then 20 later'});
            $('#add-name-modal').modal('hide');
            return;
        }
        name = escapeInput(nameString);
        alert({type: 'success', text: 'Hi ' + name + '! Welcome to our battle!'});
        $('#add-name-modal').modal('hide');
        $('#enter-name').html(name).attr('disabled', 'disabled');
    })

    $('#start-game').on('click', function (event) {
        if (name == null) {
            alert({type: 'warning', text: 'Please, enter the name'});
            return;
        }
        if (isStarted) {
            alert({type: 'warning', text: 'Game already started'});
            return;
        }
        event.preventDefault();
        $('body').animate({
            opacity: 0
        }, 1000);

        setTimeout(function() {
            $(location).attr('href','/game/start/' + encodeURIComponent(name));
        },1000);
    })
});


function escapeInput(text) {
    let entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
    };

    return String(text).replace(/[&<>"',.\/\[\]]/g, function (s) {
        //return entityMap[s];
        return '';
    });
}


function alert(parameters) {
    let {type, text} = parameters;
    let alert = '<div class="alert alert-' + type + ' alert-dismissable">\n' +
        '  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\n' +
        text + '</div>\n';
    $('div.notification').empty().html(alert);
}