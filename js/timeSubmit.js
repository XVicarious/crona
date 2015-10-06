$(function() {
  $('#submit-button').click(function() {
    var username = $('#uname'),
      password = $('input#drowp'),
      dataString;
    if (!(username.val().length && password.val().length)) {
      return false;
    }
    dataString = 'uname=' + username.val() + '&drowp=' + password.val() + '&loginType=' + $('#loginType').val();
    $.ajax({
      url: 'punch.new.php',
      type: 'POST',
      dataType: 'script',
      data: dataString
    });
    return true;
  });
});
