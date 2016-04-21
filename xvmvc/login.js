$(function() {
  $('#submit-button').click(function() {
    var username = $('#uname'),
        password = $('input#drowp'),
        usernameLength = username.length,
        passwordLength = password.length;
    if (!(usernameLength && passwordLength)) {
      if (!usernameLength) {
        Materialize.toast("Please enter your username", 2000);
      }
      if (!passwordLength) {
        Materialize.toast("Please enter your password", 2000);
      }
      return false;
    }
    $.ajax({
      url: 'controller/ControllerLogin.php',
      type: 'POST',
      data: 'action=' + $('#loginType').val() + '&username=' + username + '&password=' + password
    });
  });
});
