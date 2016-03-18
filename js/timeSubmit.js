var VK_ENTER = 13;
$(function() {
  $('select').material_select();
  $(document).keyup(function(e){
    if (e.which === VK_ENTER) {
      e.preventDefault();
      $('#submit-button').click();
    }
  });
  $('#submit-button').click(function() {
    var username = $('#uname'),
        password = $('input#drowp'),
        usernameLength = username.val().length,
        passwordLength = password.val().length,
        dataString;
    if (!(usernameLength && passwordLength)) {
      if (!usernameLength) {
        Materialize.toast("Please enter your username.", 2000);
      }
      if (!passwordLength) {
        Materialize.toast("Please enter your password.", 2000);
      }
      return false;
    }
    dataString = 'uname=' + username.val() + '&drowp=' + password.val() + '&loginType=' + $('#loginType').val();
    $.ajax({
      url: 'punch.php',
      type: 'POST',
      dataType: 'script',
      data: dataString
    });
    return true;
  });
});
