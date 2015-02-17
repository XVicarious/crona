var i = 0;
$(function() {
  $('#saveRules').click(function() {
    //var lowercase = '/.*[a-z].*/';
    //var uppercase = '/.*[A-Z].*/';
    //var digits = '/.*[0-9].*/';
    //var specials = '/.*[\`\~\!\@\#\$\%\^\&\*\(\)\_\+\-\=].*/';
    var requireData = 'minLength=' + $('#minLength').val() + '&requires=',
      requireArray = [];
    $('.requires:checked').each(function() {
      requireArray.push($(this).val());
    });
    for (i = 0; i < requireArray.length; i++) {
      requireData += requireArray[i];
      if (i !== requireArray.length - 1) {
        requireData += ',';
      }
    }
    $.ajax({
      type: 'POST',
      url: 'generate_password_rules.php',
      data: requireData
    });
  });
});
