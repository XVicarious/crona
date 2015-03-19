$(function() {
  $('#submit-button').click(function() {
    var username = $('#uname'),
      password = $('input#drowp');
    if (!(username.val().length && password.val().length)) {
      return false;
    }
    var dataString = 'uname=' + username.val() + '&drowp=' + password.val() + '&loginType=' +
      $('#loginType').val();
    $.ajax({
      type: 'POST',
      url: 'punch.php',
      data: dataString,
      success: function(data) {
        $('#ajaxDiv').html(data);
        var badLogin = $('#bad-login');
        var badLoginMessage = badLogin.find('.modal-message');
        if ($('#badup').length) {
          badLoginMessage.text("Bad username or password!");
          badLogin.openModal();
        } else if ($('#a').length) {
          $(location).attr('href','admin');
        } else if ($('#b').length) {
          //$(location).attr('href','view');
          toast('Working on it!', 4000);
        } else if ($('#accepted').length) {
          toast('Timestamp Accepted!', 4000, 'toasty', function() {
            $('#uname').val('');
            $('#drowp').val('');
          });
        } else if ($('#not-accepted').length) {
          badLoginMessage.html('Your timestamp was <b>NOT</b> accepted.<br>This is usually because you\'re not on an approved IP address.');
          badLogin.openModal();
        }
      }
    });
    return true;
  });
});
