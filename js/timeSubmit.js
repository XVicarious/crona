$(function() {
  //noinspection FunctionWithMultipleReturnPointsJS
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
        if ($('#badup').length) {
          $('#bad-login .modal-message').text("Bad username or password!");
          $('#bad-login').openModal();
        } else if ($('#a').length) {
          // administrate
        } else if ($('#b').length) {
          // view card
        } else if ($('#accepted').length) {
          toast('Timestamp Accepted!', 4000, 'toasty', function() {
            $('#uname').val('');
            $('#drowp').val('');
          });
        } else if ($('#not-accepted').length) {
          $('#bad-login .modal-message').html('Your timestamp was <b>NOT</b> accepted.<br>This is usually because you\'re not on an approved IP address.');
          $('#bad-login').openModal();
        }
      }
    });
    return true;
  });
});
