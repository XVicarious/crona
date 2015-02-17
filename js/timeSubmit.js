$(function() {
  var $centerDiv = $('#bestdiv');
  $('#typeRadio').buttonset();
  //noinspection FunctionWithMultipleReturnPointsJS
  $('#submitButton').button().click(function() {
    var username = $('#uname'),
      password = $('input#drowp');
    if (!(username.val().length && password.val().length)) {
      if (username.val().length) {
        username.removeClass('ui-state-error');
      } else {
        username.addClass('ui-state-error');
      }
      if (password.val().length) {
        password.removeClass('ui-state-error');
      } else {
        password.addClass('ui-state-error');
      }
      return false;
    }
    var dataString = 'uname=' + username.val() + '&drowp=' + password.val() + '&loginType=' +
      $('input[name=loginType]:checked').val();
    $.ajax({
      type: 'POST',
      url: 'punch.php',
      data: dataString,
      success: function(data) {
        $('#ajaxDiv').html(data);
        if ($('#a').length) {
          $(location).attr('href', 'admin');
        } else if ($('#b').length) {
          $(location).attr('href', 'view');
        } else if ($('#badup').length) {
          $('#uname, #drowp').addClass('ui-state-error');
        }
      }
    });
    return true;
  });
  $centerDiv.css('left', 'calc(50% - ' + $centerDiv.width() / 2 + ')')
    .css('top', 'calc(50% - ' + $centerDiv.height() / 2 + ')');
});
