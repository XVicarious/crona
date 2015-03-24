function getByName(name) {
  name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
  var regexS = '[\\?&]' + name + '=([^&#]*)', regex = new RegExp(regexS), results = regex.exec(window.location.href);
  return (results === null ? '' : results[1]);
}

$(function() {
  if (getByName('c') === '' && getByName('r') === 'go') {
    $.ajax({
      type: 'POST',
      url: 'resetutil.php',
      success: function(data) {
        $('#ajaxDiv').html(data).css('width', '286px').css('left', 'calc(50% - 143px)').css('top', 'calc(50% - 32px)');
      }
    });
  } else if (getByName('c')) {
    $.ajax({
      type: 'GET',
      url: 'resetutil.php',
      data: 'c=' + getByName('c'),
      success: function(data) {
        var loginForm = $('#loginForm');
        $('#ajaxDiv').html(data).css('left', 'calc(50% - ' + loginForm.width() / 2 + 'px)').css('top', 'calc(50% - ' + loginForm.height() / 2 + 'px)');
      }
    });
  }
  $(document).on('click', '#subby', function() {
    var $pwc = $('#pwc');
    if ($('div#semail').length) {
      var email = $('#email').val();
      $.ajax({
        type: 'POST',
        url: 'resetutil.php',
        data: 'email=' + email + '&function=sendEmail',
        success: function(data) {
          $('#ajaxDiv').html(data);
        }
      });
    } else if ($('div#rpassword').length && ($('#pw').val() === $pwc.val())) {
      if ($pwc.val().length >= 6) {
        /* todo: make this configurable */
        if ($pwc.val().match(/(?=.*[a-z].*)(?=.*[A-Z].*)(?=.*[0-9].*)/)) {
          $.ajax({
            type: 'POST',
            url: 'resetutil.php',
            data: 'function=checkReset&resetId=' + $('#resetId').val() + '&pword=' + $pwc.val() + '&answer=' +
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
