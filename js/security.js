$(function() {
  var $s1ic = $('#s1ic'),
    $s2ic = $('#s2ic'),
    $s3ic = $('#s3ic'),
    $s1i = $('#s1i'),
    $s2i = $('#s2i'),
    $s3i = $('#s3i');
  $('select').change(function() {
    var aOptions = [], mySelect = $('select');
    mySelect.each(function() {
      aOptions.push($(this).val());
    });
    mySelect.each(function() {
      var selected = $(this);
      $(this).find('option').prop('hidden', false);
      $.each(aOptions, function(key, value) {
        if ((value.length) && (value !== selected.val())) {
          selected.find('option').filter('[value="' + value + '"]').prop('hidden', true);
        }
      });
    });
  });
  $s1ic.keydown(function() {
    if ($(this).val() !== $s1i.val()) {
      $(this).css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
      $s1i.css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
    }
  });
  $s1ic.keyup(function() {
    if ($(this).val() === $s1i.val()) {
      $(this).css('box-shadow', 'none');
      $s1i.css('box-shadow', 'none');
    }
  });
  $s2ic.keydown(function() {
    if ($(this).val() !== $s2i.val()) {
      $(this).css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
      $s2i.css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
    }
  });
  $s2ic.keyup(function() {
    if ($(this).val() === $s2i.val()) {
      $(this).css('box-shadow', 'none');
      $s2i.css('box-shadow', 'none');
    }
  });
  $s3ic.keydown(function() {
    if ($(this).val() !== $s3i.val()) {
      $(this).css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
      $s3i.css('box-shadow', 'inset 0 0 7px rgba(255,0,0,1');
    }
  });
  $s3ic.keyup(function() {
    if ($(this).val() === $s3i.val()) {
      $(this).css("box-shadow', 'none");
      $s3i.css('box-shadow', 'none');
    }
  });
  $('#submit').click(function() {
    var s1 = $('#s1'),
      s2 = $('#s2'),
      s3 = $('#s3');
    if (!(''.match(new RegExp($s1ic+'|'+$s2ic+'|'+$s3ic)) || s1.val() === s2.val() || s1.val() === s3.val() ||
          s2.val() === s3.val()) && $s1i.val() === $s1ic.val() && $s2i.val() === $s2ic.val() &&
          $s3i.val() === $s3ic.val()) {
        $.ajax({
          type: 'POST',
          url: 'insert_security.php',
          data: 's1=' + s1.val() + '&s2=' + s2.val() + '&s3=' + s3.val() + '&s1i=' + $s1ic.val() + '&s2i=' +
                $s2ic.val() + '&s3i=' + $s3ic.val(),
          success: function() {
            $(location).attr('href', 'http://xvss.net/time/');
          }
        });
      }
  });
});
