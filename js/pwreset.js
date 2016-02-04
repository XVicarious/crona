function getByName(name) {
  var getName = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
  var regexS = '[\\?&]' + getName + '=([^&#]*)', regex = new RegExp(regexS), results = regex.exec(window.location.href);
  return (results === null ? '' : results[1]);
}

$(function() {
  if (getByName('c') === '') {
    $('#ajaxDiv').load('user_email.html');
  } else {
    $.ajax({
      type: 'GET',
      url: 'resetutil.php',
      data: 'c=' + getByName('c'),
      success: function(data) {
        var loginForm = $('#loginForm');
        $('#ajaxDiv').html(data);
      }
    });
  }
  $(document).on('click', '#subby', function() {
    var passwordConfirmationInput = $('#pwc');
    var email = $('#email').val();
    if ($('div#semail').length) {
      $.ajax({
        type: 'POST',
        url: 'resetutil.php',
        data: 'email=' + email + '&function=sendEmail',
        success: function() {
          Materialize.toast("If a user with that email exists, an email has been dispatched with a link to reset your" +
                            " password.",5000);
          //$('#ajaxDiv').html(data);
        }
      });
    } else if ($('div#rpassword').length && ($('#pw').val() === passwordConfirmationInput.val())) {
      if (passwordConfirmationInput.val().length >= 6) {
        /* todo: make this configurable */
        if (passwordConfirmationInput.val().match(/(?=.*[a-z].*)(?=.*[A-Z].*)(?=.*[0-9].*)/)) {
          $.ajax({
            type: 'POST',
            url: 'resetutil.php',
            data: 'function=checkReset&resetId=' + $('#resetId').val() + '&pword=' + passwordConfirmationInput.val() + '&answer=' +
            $('#answer').val() + '&qid=' + $('#qid').val() + '&user=' + $('#uid').val(),
            success: function(data) {
              $('#ajaxDiv').html(data);
            }
          });
        }
      }
    }
    return false;
  });
  $(document).on('keydown', '#pwc', function() {
    var pw = $('#pw');
    if ($(this).val() !== pw.val()) {
      $(this).css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
      pw.css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
    }
  });
  $(document).on('keyup', '#pwc', function() {
    var pw = $('#pw');
    if ($(this).val() === pw.val()) {
      $(this).css('box-shadow', 'none');
      pw.css('box-shadow', 'none');
    }
  });
});
