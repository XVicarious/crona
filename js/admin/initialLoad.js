var eCounter = 0,
    mode = '',
    i = 0,
    offsetInSeconds = (new Date()).getTimezoneOffset() * 60,
    offsetInHours = offsetInSeconds / 3600,
    companyCodes = [["48N", "HNB Venture Ptrs LLC"],
                    ["49C", "Hampton Inn Boston/Natick"],
                    ["49D", "Crowne Plaza Boston"],
                    ["49E", "Holiday Inn Somervil"],
                    ["7IS", "Skybokx 109 Natick"],
                    ["9NI", "Hart Hotels DLC, LLC"],
                    ["ANY", "FLH Development, LLC"],
                    ["FB1", "Madison Beach Hotel"],
                    ["GE3", "Distinctive Hospitality Group"],
                    ["GG8", "Seneca Market 1"],
                    ["H4G", "DDH Hotel Mystic LLC"],
                    ["HUG", "ATA Associates"],
                    ["HXH", "Portland Harbor Hotel"],
                    ["KZH", "Clayton Harbor Hotel"],
                    ["L99", "Lenroc, L.P."],
                    ["NPJ", "WPH Midtown Associates"],
                    ["NPM", "WPH Airport Associates"],
                    ["PPP", "Golden Triangle Associates"],
                    ["Q56", "Hart Management Group"],
                    ["RK3", "HBK Restaurant LLC"],
                    ["ZVT", "Twenty Flint Rd LLC"]];
$(function() {
  getEmployees();
  getPermissions();
  $(".modal-trigger").leanModal();
  $('#addemployeeButton').click(function() {
    var $dialog = $('#dialog-timecard');
    $dialog.attr('title', 'Add Employees');
    $('#timecardDiv').html('<form><table id="timecard"><tr><th>Last Name</th><th>First Name</th><th>Company Code</th><th>Department Code</th><th>ADP ID</th><th>Email Address</th><th>Start Date</th></tr><tr class="e-0"><td><input class="userLast e-0"/></td><td><input class="userFirst e-0"/></td><td><select class="userCompany e-0"></select></td><td><select class="userDepartment e-0"><option value="100">Accounting (100)</option></select></td><td><input class="userADPID e-0" maxlength="6" size="6" /></td><td><input class="userEmail e-0" /></td><td><input maxlength="10" size="10" class="userStart e-0" /></td></tr></table></form>');
    var optionString = '<option value="">(none)</option>';
    for (i = 0; i < companyCodes.length; i++) {
      optionString += '<option value="' + companyCodes[i][0] + '">[' + companyCodes[i][0] + '] ' + companyCodes[i][1].substring(0, 11) + '...</option>';
    }
    $('.userCompany.e-0').html(optionString);
    var dialog = $dialog.dialog({
      modal: true,
      draggable: false,
      width: $(window).width() * 0.9,
      height: 'auto',
      resizable: false,
      buttons: {
        'Add Employees': function() {
          var toThis = eCounter === 9 ? (rowClear('e-9') ? 9 : 10) : eCounter,
            isEverythingGood = true,
            aToAdd = [],
            dataString = '',
            aTempEmployee = [];
          // is everything set?
          for (i = 0; i < toThis; i++) {
            $('input.e-' + i + ',select.e-' + i).each(function() {
              if ($(this).val() === null || !$(this).val().length) {
                // we allow userEmail and userStart to be empty because they have default values
                // userStart will be today, and userEmail will be their immediate superior's email
                if ($(this).hasClass('userEmail') || $(this).hasClass('userStart')) {
                  if ($(this).val().length) {
                    if ($(this).hasClass('userEmail')) {
                      if (!validateEmail($(this).val())) {
                        isEverythingGood = false;
                        return false;
                      }
                    } else {
                      if (!validMoment($(this).val())) {
                        isEverythingGood = false;
                        return false;
                      }
                    }
                  }
                } else {
                  isEverythingGood = false;
                  return false;
                }
              } else {
                aTempEmployee.push($(this).val());
              }
              return true;
            });
            aToAdd.push(aTempEmployee);
          }
          if (!isEverythingGood) {
            return false;
          }
          // First we need to gather up all of that information!  If we hit the max number of rows, we want to test if
          // the last row is empty.  If so, we only want the first 9, otherwise we will take the 10th.  If eCounter is
          // NOT full, we will just use eCounter because the first eCounter - 1 rows will not be clear, but the last
          // one will always be.
          // We can't do anything if it is empty
          if (!aToAdd.length) {
            return false;
          }
          // Now we have to form the string:
          for (i = 0; i < aToAdd.length; i++) {
            dataString += (i + '=' + JSON.stringify(aToAdd[i]));
            if (i < aToAdd.length - 1) {
              dataString += '&';
            }
          }
          $.ajax({
            type: 'POST',
            url: 'insert_user.php',
            data: dataString
          });
          $(this).dialog('close');
        },
        'Cancel': function() {
          $(this).dialog('close');
        }
      }
    });
  });
  $(document).on('change', '[class|="e"]', function() {
    var myeclass = $(this).attr('class').match(/e-[0-9][\w-]*\b/);
    myeclass = parseInt(myeclass[0].substr(2), 10);
    for (i = 0; i < eCounter + 1; i++) {
      if (!rowClear('e-' + i)) {
        $('.e-' + i).each(function() {
          // This on 'if' took me like 5 minutes to figure out.  I AM EXHAUSTED.  Like I could fall asleep right here...
          // 5:43pm on February 11th 2015
          if (!($(this).hasClass('userEmail') || $(this).hasClass('userStart'))) {
            if ($(this).val() !== null && $(this).val().length) {
              $(this).removeClass('ui-state-error');
            } else {
              $(this).addClass('ui-state-error');
            }
          }
        });
      }
    }
    if (myeclass === eCounter && eCounter < 10) {
      eCounter++;
      $('#timecard').append('<tr class="e-' + eCounter + '"><td><input class="userLast e-' + eCounter + '"/></td><td><input class="userFirst e-' + eCounter + '"/></td><td><select class="userCompany e-' + eCounter + '"></select></td><td><select class="userDepartment e-' + eCounter + '"></select></td><td><input maxlength="6" size="6" class="userADPID e-' + eCounter + '" /></td><td><input class="userEmail e-' + eCounter + '" /></td><td><input maxlength="10" size="10" class="userStart e-' + eCounter + '" /></td></tr>');
      var optionString = '<option value="">(none)</option>';
      for (var j = 0; j < companyCodes.length; j++) {
        optionString += '<option value="' + companyCodes[j][0] + '">[' + companyCodes[j][0] + '] ' + companyCodes[j][1].substring(0, 11) + '...</option>';
      }
      $('.userCompany.e-' + i).html(optionString);
      // After adding a new row, we want to make sure we resize the dialog
      var $dialog = $('#dialog-timecard');
      $dialog.dialog('option', 'position', $dialog.dialog('option', 'position'));
    }
  });
  $(document).on('change', '#range', function() {
    var userId = $('#timecard').attr('user-id');
    if ($(this).val() === 'specificDate' || $(this).val() === 'special') {
      picker.open(false);
    } else if ($(this).val() === 'w2d') {
      getEmployee({id: userId, range: 'w2d'});
    } else {
      getEmployee({id: userId});
    }
  });
  $(document).on('click', 'input.addButton', function() {
    var timestamp, $thisParent = $(this).parent();
    if ($thisParent.prev().children(':first-child').is('input')) {
      timestamp = $thisParent.prev().children(':first-child').val();
    } else if ($thisParent.next().children(':first-child").is("input')) {
      timestamp = $thisParent.next().children(':first-child').val();
    }
    var validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + timestamp,
      userId = $('#timecard').attr('user-id'),
      trueTime = (moment(validTimestamp, 'YYYY-MM-DD h:m:s a').format('X'));
    createStamp(userId, trueTime);
  });
  $(document).on('focus', 'input.times', function() {
    $(this).select();
  });
  $(document).on('keyup', 'input[type=text].times', function(e) {
    e.preventDefault();
    if (e.keyCode === 13) {
      $(this).blur();
    } else {
      var validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + $(this).val() + ' -0' + offsetInHours + '00';
      $(this).css('color', moment(validTimestamp).isValid() ? 'inherit' : 'red');
    }
  });
  $(document).on('keydown', '.times', function(e) {
    if (e.keyCode === 9) {
      e.preventDefault();
      $(this).parent().next('td').children('input.times').focus();
    }
  });
  $(document).on('blur', 'input[type=text].times', function() {
    var fieldContents = $(this).val(),
      me = $(this),
      stampId = $(this).attr('stamp-id'),
      userId = $('#timecard').attr('user-id'),
      defaultTime = $(this).attr('default-time');
    if (fieldContents.length) {
      if (fieldContents !== defaultTime) {
        var validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + fieldContents + ' -0' + offsetInHours + '00';
        if (!moment(validTimestamp).isValid()) {
          return;
        }
        var trueTime = (Date.parse(validTimestamp) / SECOND);
        $.ajax({
          type: 'POST',
          url: 'timeEdit/change_stamp.php',
          data: 'sid=' + stampId + '&time=' + trueTime + '&dtime=' + defaultTime,
          success: function() {
            getEmployee({id: userId});
          }
        });
      }
    } else {
      var confirmModal = $('#dialog');
      confirmModal.find('.modal-text').text('Are you sure you want to delete this time stamp?  This action cannot be undone!');
      confirmModal.find('.modal-footer').html(
        '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-okay">Okay</a>' +
        '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-cancel">Cancel</a>'
      );
      confirmModal.openModal({
        dismissible: false,
        ready: function() {
          confirmModal.find('.modal-cancel').click(function() {
            me.val(defaultTime);
            getEmployee({id: userId});
          });
          confirmModal.find('.modal-okay').click(function() {
            $.ajax({
              type: 'POST',
              url: 'timeEdit/delete_stamp.php',
              data: 'sid=' + stampId + '&dtime=' + defaultTime,
              success: function() {
                getEmployee({id: userId});
              }
            });
          });
        }
      });
    }
  });
  $.contextMenu({
    selector: '.context-menu',
    className: 'timeMenu',
    build: function($trigger) {
      return {
        className: 'mod' + $trigger.attr('id'),
        items: {
          'department-transfer': {
            name: 'Department',
            className: 'department-transfer',
            type: 'text',
            value: $trigger.parent().attr('department-special'),
            events: {
              keyup: function(e) {
                if (e.keyCode === 13) {
                  var stampId = $(this).parent().parent().parent().attr('class').match(/\bmod(\d+)\b/)[1];
                  var userId = $('#timecard').attr('user-id');
                  $.ajax({
                    type: 'POST',
                    url: 'timeEdit/change_department.php',
                    data: 'sid=' + stampId + '&modifier=' + $(this).val(),
                    success: function() {
                      getEmployee({id: userId});
                    }
                  });
                }
              }
            }
          },
          separator: '-----',
          'noModifier': {
            name: '(none)',
            type: 'radio',
            radio: 'specialModifier',
            value: '',
            selected: ($trigger.attr('alt') === '')
          },
          'vacationModifier': {
            name: 'Vacation (V)',
            type: 'radio',
            radio: 'specialModifier',
            value: 'V',
            selected: ($trigger.attr('alt') === 'V')
          },
          'sickModifier': {
            name: 'Sick (S)',
            type: 'radio',
            radio: 'specialModifier',
            value: 'S',
            selected: ($trigger.attr('alt') === 'S')
          },
          'sadnessModifier': {
            name: 'Bereavement (F)',
            type: 'radio',
            radio: 'specialModifier',
            value: 'F',
            selected: ($trigger.attr('alt') === 'F')
          },
          'holidayModifier': {
            name: 'Holiday (H)',
            type: 'radio',
            radio: 'specialModifier',
            value: 'H',
            selected: ($trigger.attr('alt') === 'H')
          }
        }
      };
    }
  });
  $(document).on('click', 'li.context-menu-item label input[type=radio]', function() {
    // This is *insane*
    var stampId = $(this).parent().parent().parent().attr('class').match(/\bmod(\d+)\b/)[1],
      modifier = $(this).val(),
      userId = $('#timecard').attr('user-id');
    // We need to traverse the row to find more stamps
    var stampString = '' + stampId;
    $('#' + stampId).parent().parent().children().each(function() {
      var hopefulInput = $(this).children(':first-child');
      if (hopefulInput.is('input') && hopefulInput.attr('stamp-id') && hopefulInput.attr('stamp-id') !== stampId) {
        stampString += ',' + hopefulInput.attr('stamp-id');
      }
    });
    $.ajax({
      type: 'POST',
      url: 'timeEdit/change_modifier.php',
      data: 'sids=' + stampString + '&modifier=' + modifier,
      success: function() {
        getEmployee({id: userId});
      }
    });
  });
  $('#view-employees').click(function() {
    getEmployees();
  });
});
