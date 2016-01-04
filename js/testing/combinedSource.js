var operationMode = getPermissions(),
  $inputPicker = [],
  picker = [],
  saveTheDate = 0,
  mode = EditMode.TIMECARD,
  i = 0,
  offsetInSeconds = (new Date()).getTimezoneOffset() * TimeVar.SECONDS_IN_MINUTE,
  offsetInHours = offsetInSeconds / TimeVar.SECONDS_IN_HOUR,
  timeDateFormats = ['YYYY-MM-DD hh:mm:ss a Z', 'YYYY-MM-DD hh:mm a Z', 'YYYY-MM-DD hh: a Z',
                     'YYYY-MM-DD HH:mm:ss Z', 'YYYY-MM-DD HH:mm Z', 'YYYY-MM-DD HH: Z'];
  /*companyCodes = [["48N", "HNB Venture Ptrs LLC"],
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
    ["ZVT", "Twenty Flint Rd LLC"]];*/

$(function() {
  var a = location.pathname.split('/'),
    myInt,
    week,
    year;
  a = a.filter(function(e){return e.replace(/(\r\n|\n|\r)/gm,"");});
  if (operationMode) {
    if (a.length > 3 && a[3] === 'schedule') {
      mode = 'schedule';
      $('a.page-title').text('Crona Timestamp - Schedule');
      if (a.length > 4) {
        myInt = parseInt(a[4]);

        // next is 7 because we want both the year and week
        if (a.length > 6) {
          year = parseInt(a[5]);
          week = parseInt(a[6]);
          fetchSchedule({userId: myInt, year: year, week: week});
        } else {
          fetchSchedule({userId: myInt});
        }
      } else {
        getEmployees();
      }
    } else {
      getEmployees();
    }
    getPermissions();
    getExportPermissions();
  } else {
    getEmployee();
  }
  $('.modal-trigger').leanModal();
  //$('#logout-button').click(function() {});
  $(document).tooltip();
  $(document).on('click', '#lock-card', function() {
    var timecard = $('#timecard');
    var userId = timecard.attr('user-id');
    var week = timecard.attr('week');
    var year = timecard.attr('year');
    $.ajax({
      type: "POST",
      url: "/devel/time/admin/lock_timecard.php",
      data: "user=" + userId + "&week=" + week + "&year=" + year,
      success: function() {
        getEmployee({id: userId});
      }
    });
  });
  $(document).on('change', '#range', function() {
    var userId = $('#timecard').attr('user-id');
    if ($(this).val() === 'specificDate' || $(this).val() === 'special') {
      picker[0].open(false);
    } else {
      getEmployee({id: userId});
    }
  });
  $(document).on('click', 'button.addButton', function() {
    var thisParent = $(this).parent(),
      thisPrevChild = thisParent.prev().children(':first-child'),
      thisNextChild = thisParent.next().children(':first-child'),
      userId = $('#timecard').attr('user-id'),
      timestamp, validTimestamp, trueTime, syear, sweek, sday, momentTime;
    if (thisPrevChild.is('input')) {
      timestamp = thisPrevChild.val();
    } else if (thisNextChild.is('input')) {
      timestamp = thisNextChild.val();
    }
    validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + timestamp;
    momentTime = (moment(validTimestamp, 'YYYY-MM-DD h:m:s a'));
    if (mode === EditMode.SCHEDULE) {
      syear = momentTime.isoWeekYear();
      sweek = momentTime.isoWeek();
      sday = momentTime.isoWeekday();
      createSchedulePair(userId, {year: syear, week: sweek, day: sday});
    } else if (mode === EditMode.TIMECARD) {
      trueTime = momentTime.unix();
      createStamp(userId, trueTime);
    }
  });
  $(document).on('focus', 'input.times', function() {
    $(this).select();
  });
  $(document).on('keyup', 'input[type=text].times', function(e) {
    var validTimestamp;
    e.preventDefault();
    if (e.keyCode === $.ui.keyCode.ENTER) {
      $(this).blur();
    } else {
      // todo: this works, however am/pm does not work with this format
      validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + $(this).val() + ' -' + offsetInHours + '00';
      $(this).css('color', moment(validTimestamp, timeDateFormats).isValid() ? 'inherit' : 'red');
    }
  });
  $(document).on('keydown', '.times', function(e) {
    if (e.keyCode === 9) {
      e.preventDefault();
      $(this).parent().next('td').children('input.times').focus();
    }
  });
  $(document).on('blur', 'input[type=text].times.sched', function() {
    var meThis = $(this),
      fieldContents = meThis.val(),
      defaultTime = meThis.attr('default-time'),
      scheduleId = meThis.attr('stamp-id'),
      changed = 'in',
      validTimestamp, myMoment, formattedTime;
    if (fieldContents.length) {
      if (fieldContents !== defaultTime) {
        validTimestamp = fieldContents + ' ' + getOffsetString();
        myMoment = moment(validTimestamp, ['hh:mm:ss a Z', 'hh:mm a Z', 'hh: a Z', 'HH: Z', 'HH:mm:ss Z', 'HH:mm Z']);
        formattedTime = myMoment.utc().format('HH:mm:ss');
        if (meThis.hasClass('sched-out')) {
          changed = 'out';
        }
        $.ajax({
          type: 'POST',
          data: 'id=' + scheduleId + '&' + changed + '=' + formattedTime,
          url: '/devel/time/admin/timeEdit/change_schedule.php',
          success: function() {
            var table = meThis.closest('table'),
              userId = table.attr('user-id'),
              year = table.attr('year'),
              week = table.attr('week');
            fetchSchedule({userId: userId, year: year, week: week});
          }
        });
      }
    }
  });
  $(document).on('blur', 'input[type=text].times.ts-card', function() {
    var fieldContents = $(this).val(),
      me = $(this),
      stampId = $(this).attr('stamp-id'),
      userId = $('#timecard').attr('user-id'),
      defaultTime = $(this).attr('default-time'),
      validTimestamp, myMoment, trueTime, confirmModal;
    if (fieldContents.length) {
      if (fieldContents !== defaultTime) {
        validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + fieldContents + ' -' + offsetInHours + '00';
        myMoment = moment(validTimestamp, timeDateFormats);
        if (!myMoment.isValid()) {
          return;
        }
        trueTime = myMoment.format('X');
        $.ajax({
          type: 'POST',
          url: '/devel/time/admin/timeEdit/change_stamp.php',
          data: 'sid=' + stampId + '&time=' + trueTime + '&dtime=' + defaultTime,
          success: function() {
            getEmployee({id: userId});
          }
        });
      }
    } else {
      confirmModal = $('#dialog');
      confirmModal.find('.modal-text').text('Are you sure you want to delete this time stamp?  This action cannot be undone!');
      confirmModal.find('.modal-footer').html(
        '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-okay">Okay<\/a>' +
        '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-cancel">Cancel<\/a>'
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
              url: '/devel/time/admin/timeEdit/delete_stamp.php',
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
        callback: function(key) {
          var stamp, dialog, dialogContent, commentDiv;
          if (key === 'add-comment') {
            stamp = $trigger.attr('id');
            dialog = $('#dialog');
            dialogContent = dialog.find('.modal-content');
            commentDiv = $('#timecard').find('input#' + stamp);
            dialogContent.find('.modal-title').text('Add Comment');
            dialogContent.find('.modal-text').html('<textarea stamp-id="' + stamp + '" rows="16">' + commentDiv.attr('title') + '</textarea>');
            dialog.find('.modal-footer').html('<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-save-comment">Save Comment</a>' +
              '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-cancel">Discard Changes</a>');
            dialog.openModal();
          }
        },
        items: {
          'add-comment': {
            name: 'Comment...',
            className: 'stamp-comment'
          },
          'department-transfer': {
            name: 'Department',
            className: 'department-transfer',
            type: 'text',
            value: $trigger.parent().attr('department-special'),
            events: {
              keyup: function(e) {
                var stampId, userId;
                if (e.keyCode === $.ui.keyCode.ENTER) {
                  stampId = $(this).parent().parent().parent().attr('class').match(/\bmod(\d+)\b/)[1];
                  userId = $('#timecard').attr('user-id');
                  $.ajax({
                    type: 'POST',
                    url: '/devel/time/admin/timeEdit/change_department.php',
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
    $('#' + stampId).parent().parent().parent().children().children().each(function() {
      var hopefulInput = $(this).children(':first-child');
      if (hopefulInput.is('input') && hopefulInput.attr('stamp-id') && hopefulInput.attr('stamp-id') !== stampId) {
        stampString += ',' + hopefulInput.attr('stamp-id');
      }
    });
    $.ajax({
      type: 'POST',
      url: '/devel/time/admin/timeEdit/change_modifier.php',
      data: 'sids=' + stampString + '&modifier=' + modifier,
      success: function() {
        getEmployee({id: userId});
      }
    });
  });
  $(document).on('click', '.modal-save-comment', function() {
    var textArea = $('#dialog').find('.modal-content').find('.modal-text').find('textarea');
    var stampid = textArea.attr('stamp-id');
    var oldText = $('#timecard').find('input#' + stampid).attr('title');
    if (textArea.val() === '') {
      $.ajax({
        type: 'POST',
        url: '/devel/time/admin/timeEdit/delete_comment.php',
        data: 'stampid=' + stampid,
        success: function() {
          getEmployee({id: $('#timecard').attr('user-id')});
        }
      });
    } else if (oldText === '') {
      $.ajax({
        type: 'POST',
        url: '/devel/time/admin/timeEdit/add_comment.php',
        data: 'stampid=' + stampid + '&comment=' + textArea.val(),
        success: function() {
          getEmployee({id: $('#timecard').attr('user-id')});
        }
      });
    } else if (oldText !== textArea.val()) {
      $.ajax({
        type: 'POST',
        url: '/devel/time/admin/timeEdit/edit_comment.php',
        data: 'stampid=' + stampid + '&comment=' + textArea.val(),
        success: function() {
          getEmployee({id: $('#timecard').attr('user-id')});
        }
      });
    }
  });
  $('#view-employees').click(function() {
    mode = EditMode.TIMECARD;
    $('a.page-title').text('Crona Timestamp');
    History.pushState(null, null, 'https://xvss.net/devel/time/admin/');
    getEmployees();
  });
  $('#manage-schedules').click(function() {
    mode = EditMode.SCHEDULE;
    $('a.page-title').text('Crona Timestamp - Schedule');
    History.pushState(null, null, 'https://xvss.net/devel/time/admin/schedule/');
    getEmployees();
  });
  $('#system-admin').click(function() {
    $.ajax({
      type: 'POST',
      url: 'admin_console.php',
      success: function(data) {
        $('#ajaxDiv').html(data);
        $('.collapsible').collapsible();
      }
    });
  });
  $(document).on('click', 'td', function() {
    var trId = $(this).parent().attr('user-id'),
        year, week;
    if (trId) {
      if (mode === EditMode.SCHEDULE) {
        year = moment().isoWeekYear();
        week = moment().isoWeek();
        History.pushState(null, null, 'https://xvss.net/devel/time/admin/schedule/' + trId + '/');
        fetchSchedule({userId: trId, year: year, week: week});
      } else {
        getEmployee({id: trId});
      }
    }
  });
  /* Schedule buttons */

});

function getPermissions() {
  var permissions = null;
  $.ajax({
    url: '/devel/time/admin/get_permissions.php',
    async: false,
    success: function(data) {
      permissions = JSON.parse(data);
    }
  });
  return permissions;
}

function getExportPermissions() {
  $.ajax({
    type: 'POST',
    url: '/devel/time/admin/export_permissions.php',
    success: function(data) {
      $('#exportC').html(data);
      $('#export-times').find('.modal-export').click(function() {
        $.getScript('/devel/time/admin/export.php?companyCode=' + $('#companyCode').val());
      });
    }
  });
}

function getEmployee(parameters) {
  var id = parameters.id;
  var range = parameters.range;
  var extraString = parameters.extraString;
  //todo: when all is set with the new system, fix this
  var mode = 2; //parameters.mode;
  range = range || $('#range').children(':selected').val();
  extraString = extraString || '';
  // todo: convert to stuff
  $.ajax({
    type: 'POST',
    url: '/devel/time/admin/get_timecard.json.php',
    data: 'employee=' + id + '&range=' + range + extraString,
    success: function(data) {
      $.ajax({
        replace: true,
        type: 'POST',
        url: '/devel/time/admin/build_timecard.php',
        data: 'timestamps=' + data + '&mode=' + mode,
        success: function(data) {
          var datepicker, secondDate;
          $('#ajaxDiv').html(data);
          datepicker = $('#date0');
          secondDate = $('#date1');
          $inputPicker[0] = datepicker.pickadate({
            selectMonths: true,
            selectYears: true,
            onSet: function(thing) {
              var fixedDate = this.get('value');
              var rangeChildren = $('#range').children(':selected');
              var endOfDay = 0;
              var extraString = '';
              if (thing.select) {
                fixedDate = moment(fixedDate + ' 00:00:00 ' + getOffsetString(), 'D MMMM, YYYY h:m:s Z').utc().unix();
                if (rangeChildren.val() === 'specificDate') {
                  endOfDay += fixedDate + (TimeVar.SECONDS_IN_DAY - 1);
                  extraString += '&date0=' + fixedDate + '&date1=' + endOfDay;
                  getEmployee({id: id, range: 'specificDate', extraString: extraString});
                } else if (rangeChildren.val() === 'special') {
                  saveTheDate = fixedDate;
                  picker[1].open(false);
                  this.close();
                }
              }
            }
          });
          $inputPicker[1] = secondDate.pickadate({
            selectMonths: true,
            selectYears: true,
            onSet: function(thing) {
              var fixedDate = this.get('value');
              var extraString = '';
              if (thing.select) {
                fixedDate = moment(fixedDate + ' 00:00:00 ' + getOffsetString(), 'D MMMM, YYYY h:m:s Z').utc().unix() +
                  (TimeVar.SECONDS_IN_DAY - 1);
                extraString += '&date0=' + saveTheDate + '&date1=' + fixedDate;
                getEmployee({id: id, range: 'specificDate', extraString: extraString});
                this.close();
              }
            }
          });
          $inputPicker[1].css('display', 'none');
          picker[1] = $inputPicker[1].pickadate('picker');
          $inputPicker[0].css('display', 'none');
          picker[0] = $inputPicker[0].pickadate('picker');
          $('#range').val(range);
        }
      });
    }
  });
}

function getEmployees() {
  $.ajax({
    type: 'POST',
    url: '/devel/time/admin/get_employees.json.php',
    success: function(data) {
      $('#ajaxDiv').html(data);
    }
  });
}

function fetchSchedule(parameters) {
  var userId = parameters.userId,
    week = parameters.week || moment().isoWeek(),
    year = parameters.year || moment().isoWeekYear(),
    unixStart, unixEnd;
  var scheduleMoment = moment().isoWeekYear(year).isoWeek(week).isoWeekday(1).hour(0).minute(0).second(0);
  scheduleMoment.subtract(1, 'days');
  unixStart = scheduleMoment.unix();
  scheduleMoment.endOf('week').subtract(1, 'days');
  unixEnd = scheduleMoment.unix();
  $.ajax({
    type: 'POST',
    url: '/devel/time/admin/get_schedule.php',
    data: 'userId=' + userId + '&ustart=' + unixStart + '&uend=' + unixEnd,
    success: function(data) {
      $.ajax({
        type: 'POST',
        url: '/devel/time/admin/build_schedule.php',
        data: 'timestamps=' + data,
        success: function(data) {
          $('#ajaxDiv').html(data);
        }
      });
    }
  });
}

function getOffsetString() {
  var offset = moment().utcOffset();
  if (offset > 0) {
    offset = '+' + offset;
  }
  return offset;
}

function createStamp(userId, stamp) {
  $.ajax({
    type: 'POST',
    url: '/devel/time/admin/timeEdit/create_stamp.php',
    data: 'user=' + userId + '&date=' + stamp,
    success: function() {
      getEmployee({id: userId});
    }
  });
}

function createSchedulePair(userId, schedule) {
  // schedule = {year: <year>, week: <week>, day: <day>}
  var year = schedule.year,
    week = schedule.week,
    day  = parseInt(schedule.day),
    scheduleMoment, unixStamp;
  // adding to sunday technically adds to the previous week
  if (day === 0) {
    day = 7;
  }
  scheduleMoment = moment().isoWeekYear(year).isoWeek(week).isoWeekday(day).hour(0).minute(0).second(0);
  unixStamp = scheduleMoment.unix();
  $.ajax({
    type: 'POST',
    url: '/devel/time/admin/timeEdit/add_schedule.php',
    data: 'userId=' + userId + '&unix=' + unixStamp,
    success: function() {
      fetchSchedule({userId: userId, week: week, year: year});
    }
  });
}

function generateExceptions() {
  $.ajax({
    type: 'POST',
    data: 'exceptionMode=gather',
    url: '/devel/time/admin/generate_exceptions.php'
  });
}
