var TOAST_LENGTH = 4000;
$(function() {
  $('#submit-button').click(function() {
    var username = $('#uname'),
      password = $('input#drowp'),
      dataString;
    if (!(username.val().length && password.val().length)) {
      return false;
    }
    dataString = 'uname=' + username.val() + '&drowp=' + password.val() + '&loginType=' +
      $('#loginType').val();
    $.ajax({
      type: 'POST',
      url: 'punch.php',
      data: dataString,
      success: function(data) {
        var badLogin,
            badLoginMessage;
        $('#ajaxDiv').html(data);
        badLogin = $('#bad-login');
        badLoginMessage = badLogin.find('.modal-message');
        if ($('#badup').length) {
          badLoginMessage.text("Bad username or password!");
          badLogin.openModal();
        }
        if ($('#a').length) {
          $(location).attr('href','admin');
        }
        if ($('#b').length) {
          $(location).attr('href','view');
          //toast('Working on it!', TOAST_LENGTH);
        }
        if ($('#accepted').length) {
          Materialize.toast('Timestamp Accepted!', TOAST_LENGTH, 'toasty', function() {
            $('#uname').val('');
            $('#drowp').val('');
          });
        }
        if ($('#not-accepted').length) {
          badLoginMessage.html('Your timestamp was <b>NOT<\/b> accepted.<br>This is usually because you\'re not on an approved IP address.');
          badLogin.openModal();
        }
      }
    });
    return true;
  });
});
