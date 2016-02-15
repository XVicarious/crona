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
      dataString;
    if (!(username.val().length && password.val().length)) {
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
