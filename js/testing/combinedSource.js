var operationMode = getPermissions(),
  $inputPicker = [],
  pageableGrid,
  picker = [],
  saveTheDate = 0,
  eCounter = 0,
  mode = '',
  i = 0,
  offsetInSeconds = (new Date()).getTimezoneOffset() * TimeVar.SECONDS_IN_MINUTE,
  offsetInHours = offsetInSeconds / TimeVar.SECONDS_IN_HOUR,
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
  if (operationMode) {
    getEmployees();
    getPermissions();
    getExportPermissions();
  } else {
    getEmployee();
  }
  $('.modal-trigger').leanModal();
  $('#logout-button').click(function() {

  });
  $(document).tooltip();
  $(document).on('click', '#lock-card', function() {
    var timecard = $('#timecard');
    var userId = timecard.attr('user-id');
    var week = timecard.attr('week');
    var year = timecard.attr('year');
    $.ajax({
      type: "POST",
      url: "lock_timecard.php",
      data: "user=" + userId + "&week=" + week + "&year=" + year,
      success: function() {
        getEmployee({id: userId});
      }
    });
  });
  $(document).on('change', '[class|="e"]', function() {
    var myeclass = $(this).attr('class').match(/e-[0-9][\w-]*\b/);
    myeclass = parseInt(myeclass[0].substr(2), 10);
    for (i = 0; i < eCounter + 1; i++) {
      if (!rowClear('e-' + i)) {
        $('.e-' + i + ":not(tr)").each(function() {
          // This on 'if' took me like 5 minutes to figure out.  I AM EXHAUSTED.  Like I could fall asleep right here...
          // 5:43pm on February 11th 2015
          if ((!($(this).hasClass('userEmail') || $(this).hasClass('userStart')))) {
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
      $('#timecard').append('<tr class="e-' + eCounter + '"><td><input class="userLast e-' + eCounter + '"\/><\/td><td><input class="userFirst e-' + eCounter + '"\/><\/td><td><select class="userCompany browser-default e-' + eCounter + '"><\/select><\/td><td><input class="userDepartment e-' + eCounter + '"><\/input><\/td><td><input maxlength="6" size="6" class="userADPID e-' + eCounter + '" \/><\/td><td><input class="userEmail e-' + eCounter + '" \/><\/td><td><input maxlength="10" size="10" class="userStart e-' + eCounter + '" \/><\/td><\/tr>');
      var optionString = '<option value="">(none)<\/option>';
      for (var j = 0; j < companyCodes.length; j++) {
        optionString += '<option value="' + companyCodes[j][0] + '">[' + companyCodes[j][0] + '] ' + companyCodes[j][1].substring(0, 11) + '...<\/option>';
      }
      $('.userCompany.e-' + i).html(optionString);
    }
  });
  $(document).on('click', '#initial-confirm-add', function() {
    var toThis = eCounter === 9 ? (rowClear('e-9') ? 9 : 10) : eCounter; // Complicated!  Does this even work properly?
    var isGood = true,
      toAdd = [],
      dataString = '',
      aTempEmployee = [];
    for (i = 0; i < toThis; i++) {
      $('input.e-' + i + ',select.e-' + i).each(function() {
        if ($(this).val() === null || !$(this).val().length) {
          // we allow userEmail and userStart to be empty, because they have default values
          // userStart will be today, and userEmail will be derived from the immediate superior's email
          if (($(this).hasClass('userEmail') || $(this).hasClass('userStart')) && $(this).val().length) {
            if ($(this).hasClass('userEmail')) {
              if (!validateEmail($(this).val())) {
                console.log(i + ' does not have a valid email address');
                isGood = false;
              }
            } else {
              if (!validMoment($(this).val())) {
                console.log(i + ' does not have a valid start date');
                isGood = false;
              }
            }
          }
        } else {
          aTempEmployee.push($(this).val());
        }
        return true;
      });
      toAdd.push(aTempEmployee);
    }
    if (!isGood) {
      return isGood;
    }
    // first we need to gather all our information.  if we hit the max number of rows, we want to test if the last row
    // is empty.  if so, we only want the first 9, otherwise we will take the 10th.  if eCounter is NOT full, we will
    // just use eCounter, because the first eCounter - 1 rows will NOT be clear, but the last one will always be
    // we can't do anything if it is empty
    if (!toAdd.length) {
      console.log('There is nothing to add');
      return false;
    }
    // now we have to form the string of data
    for (i = 0; i < toAdd.length; i++) {
      dataString += (i + '=' + JSON.stringify(toAdd[i]));
      if (i < toAdd.length - 1) {
        dataString += '&';
      }
    }
    $.ajax({
      type: "POST",
      url: "insert_user.php",
      data: dataString
    });
  });
  $(document).on('change', '#range', function() {
    var userId = $('#timecard').attr('user-id');
    if ($(this).val() === 'specificDate' || $(this).val() === 'special') {
      picker[0].open(false);
    } else if ($(this).val() === 'w2d') {
      getEmployee({id: userId, range: 'w2d'});
    } else {
      getEmployee({id: userId});
    }
  });
  $(document).on('click', 'button.addButton', function() {
    var timestamp, validTimestamp, userId, trueTime, thisParent = $(this).parent();
    if (thisParent.prev().children(':first-child').is('input')) {
      timestamp = thisParent.prev().children(':first-child').val();
    } else if (thisParent.next().children(':first-child').is('input')) {
      timestamp = thisParent.next().children(':first-child').val();
    }
    validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + timestamp;
    userId = $('#timecard').attr('user-id');
    var momentTime = (moment(validTimestamp, 'YYYY-MM-DD h:m:s a'));
    if (mode === 'schedule') {
      var syear = momentTime.format('YYYY');
      var sweek = parseInt(momentTime.format('W')) + 1; // add 1 to this, because?
      var sday  = momentTime.format('d');
      sweek = parseInt(sweek) + 1;
      createSchedulePair(userId, {year: syear, week: sweek, day: sday});
    } else {
      trueTime = momentTime.format('X');
      createStamp(userId, trueTime);
    }
  });
  $(document).on('focus', 'input.times', function() {
    $(this).select();
  });
  $(document).on('keyup', 'input[type=text].times', function(e) {
    e.preventDefault();
    if (e.keyCode === $.ui.keyCode.ENTER) {
      $(this).blur();
    } else {
      // todo: this works, however am/pm does not work with this format
      var validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + $(this).val() + ' -' + offsetInHours + '00';
      $(this).css('color', moment(validTimestamp, ['YYYY-MM-DD hh:mm:ss a Z', 'YYYY-MM-DD hh:mm a Z', 'YYYY-MM-DD hh: a Z', 'YYYY-MM-DD HH:mm:ss Z', 'YYYY-MM-DD HH:mm Z', 'YYYY-MM-DD HH: Z']).isValid() ? 'inherit' : 'red');
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
      defaultTime = $(this).attr('default-time'),
      validTimestamp,
      myMoment,
      trueTime;
    if (fieldContents.length) {
      if (fieldContents !== defaultTime) {
        validTimestamp = $(this).closest('tr').attr('stamp-day') + ' ' + fieldContents + ' -' + offsetInHours + '00';
        myMoment = moment(validTimestamp, ['YYYY-MM-DD hh:mm:ss a Z', 'YYYY-MM-DD hh:mm a Z', 'YYYY-MM-DD hh: a Z', 'YYYY-MM-DD HH:mm:ss Z', 'YYYY-MM-DD HH:mm Z', 'YYYY-MM-DD HH: Z']);
        if (!myMoment.isValid()) {
          return;
        }
        trueTime = myMoment.format('X');
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
        callback: function(key, options) {
          if (key === 'add-comment') {
            var stamp = $trigger.attr('id');
            var dialog = $('#dialog');
            var dialogContent = dialog.find('.modal-content');
            var commentDiv = $('#timecard').find('input#' + stamp);
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
                if (e.keyCode === $.ui.keyCode.ENTER) {
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
    $('#' + stampId).parent().parent().parent().children().children().each(function() {
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
  $(document).on('click', '.modal-save-comment', function() {
    var textArea = $('#dialog').find('.modal-content').find('.modal-text').find('textarea');
    var stampid = textArea.attr('stamp-id');
    var oldText = $('#timecard').find('input#' + stampid).attr('title');
    if (textArea.val() === '') {
      $.ajax({
        type: 'POST',
        url: 'timeEdit/delete_comment.php',
        data: 'stampid=' + stampid,
        success: function() {
          getEmployee({id: $('#timecard').attr('user-id')});
        }
      });
    } else if (oldText === '') {
      $.ajax({
        type: 'POST',
        url: 'timeEdit/add_comment.php',
        data: 'stampid=' + stampid + '&comment=' + textArea.val(),
        success: function() {
          getEmployee({id: $('#timecard').attr('user-id')});
        }
      });
    } else if (oldText !== textArea.val()) {
      $.ajax({
        type: 'POST',
        url: 'timeEdit/edit_comment.php',
        data: 'stampid=' + stampid + '&comment=' + textArea.val(),
        success: function() {
          getEmployee({id: $('#timecard').attr('user-id')});
        }
      });
    }
  });
  $('#view-employees').click(function() {
    mode = undefined;
    getEmployees();
  });
  $('#manage-schedules').click(function() {
    mode = 'schedule';
    getEmployees();
  });
  $('#add-employees').click(function() {
    addEmployeesAction();
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
  /* Schedule buttons */

});

function getPermissions() {
  var permissions = null;
  $.ajax({
    url: 'get_permissions.php',
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
    url: 'export_permissions.php',
    success: function(data) {
      $('#exportC').html(data);
      $('#export-times').find('.modal-export').click(function() {
        $.getScript('export.php?companyCode=' + $('#companyCode').val());
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
  $.ajax({
    type: 'POST',
    url: 'get_timecard.json.php',
    data: 'employee=' + id + '&range=' + range + extraString,
    success: function(data) {
      console.log(data);
      $.ajax({
        type: 'POST',
        url: 'build_timecard.php',
        data: 'timestamps=' + data,
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
  var $employeeList;
  var EmployeeList = Backbone.Model.extend({});
  var EmployeeListPageable = Backbone.PageableCollection.extend({
    model: EmployeeList,
    url: 'get_employees.json.php',
    state: {
      pageSize: 10
    },
    mode: 'client'
  });
  var employeeListPageable = new EmployeeListPageable();
  var columns = [{
    name: "",
    cell: "select-row",
    headerCell: "select-all"
  }, {
    name: 'id',
    renderable: false,
    cell: Backgrid.IntegerCell.extend({
      orderSeparator: ''
    }),
    editable: false
  }, {
    name: 'adpid',
    label: 'ADP ID',
    cell: Backgrid.IntegerCell.extend({
      orderSeparator: ''
    }),
    editable: false
  }, {
    name: 'name',
    label: 'Employee Name (Last, First)',
    cell: 'string',
    editable: false
  }, {
    name: 'companycode',
    label: 'Company Code',
    cell: 'string',
    editable: false
  }, {
    name: 'departmentcode',
    label: 'Department Code',
    cell: Backgrid.IntegerCell.extend({
      orderSeparator: ''
    }),
    editable: false
  }];
  var ClickableRow = Backgrid.Row.extend({
    events: {'click': 'onClick', '': ''},
    onClick: function(e) {
      if (e.toElement.tagName !== 'INPUT' && e.toElement.tagName !== 'LABEL') {
        Backbone.trigger('rowclicked', this.model);
      }
    },
    remove: function() {
      this.undelegateEvents();
      this.off();
      for (var i = 0; i < this.cells.length; i++) {
        var cell = this.cells[i];
        cell.remove.apply(cell, arguments);
      }
      return Backbone.View.prototype.remove.apply(this, arguments);
    }
  });
  if (pageableGrid) {
    pageableGrid.remove();
  }
  Backbone.on('rowclicked', function(model) {
    $('.spinner').css('padding-top', '1%').css('padding-bottom', '1%');
    if (mode === 'schedule') {
      fetchSchedule({userId: model.id, year: 2015, week: 44});
    } else {
      getEmployee({id: model.id, range: 'this'});
    }
    if (pageableGrid) {
      pageableGrid.remove();
    }
  });
  pageableGrid = new Backgrid.Grid({
    row: ClickableRow,
    columns: columns,
    collection: employeeListPageable
  });
  $employeeList = $('#ajaxDiv');
  $employeeList.html(pageableGrid.render().el);
  employeeListPageable.getFirstPage({fetch: true}).then(function() {
    var n = 1;
    var selectAll = $('.select-all-header-cell');
    selectAll.find('input').attr('id', "0cell");
    selectAll.append('<label for="0cell"></label>');
    $('.select-row-cell').each(function() {
      $(this).find('input').attr('id', n + "cell");
      $(this).append('<label for="' + n + 'cell"></label>');
      n++;
    });
  });
}

function fetchSchedule(parameters) {
  var userId = parameters.userId;
  var week = parameters.week;
  var year = parameters.year;
  $.ajax({
    type: 'POST',
    url: 'get_schedule.php',
    data: 'userId=' + userId + '&year=' + year + '&week=' + week,
    success: function(data) {
      console.log(data);
      $.ajax({
        type: 'POST',
        url: 'build_schedule.php',
        data: 'timestamps=' + data,
        success: function(data) {
          $('#ajaxDiv').html(data);
        }
      });
    }
  });
}

function getOffsetString() {
  var offsetMinutes = (new Date()).getTimezoneOffset(),
    absoluteOffsetMinutes = offsetMinutes + Math.abs(offsetMinutes),
    offsetString = '',
    extraOffset = 0;
  var offset,
    flooredOffset;
  if (absoluteOffsetMinutes) {
    offsetString = '-';
    absoluteOffsetMinutes = offsetMinutes;
  } else {
    offsetString = '+';
    absoluteOffsetMinutes = Math.abs(offsetMinutes);
  }
  offset = absoluteOffsetMinutes / TimeVar.MINUTES_IN_HOUR;
  flooredOffset = Math.floor(offset);
  if (flooredOffset < 10) {
    offsetString += '0' + flooredOffset;
  } else {
    offsetString += '' + flooredOffset;
  }
  if (flooredOffset !== offset) {
    extraOffset = absoluteOffsetMinutes - (flooredOffset * TimeVar().MINUTES_IN_HOUR);
  }
  if (extraOffset < 10) {
    offsetString += '00';
  } else {
    offsetString += '' + extraOffset;
  }
  return offsetString;
}

function addEmployeesAction() {
  var optionString = '<option value="">(none)<\/option>';
  var ajaxDiv = $('#ajaxDiv');
  ajaxDiv.html('<form><table id="timecard"><tr><th>Last Name<\/th><th>First Name<\/th><th>Company Code<\/th><th>Department Code<\/th><th>ADP ID<\/th><th>Email Address<\/th><th>Start Date<\/th><\/tr><tr class="e-0"><td><input class="userLast e-0"\/><\/td><td><input class="userFirst e-0"\/><\/td><td><select class="userCompany e-0 browser-default"><\/select><\/td><td><input class="userDepartment e-0"><\/input><\/td><td><input class="userADPID e-0" maxlength="6" size="6" \/><\/td><td><input class="userEmail e-0" \/><\/td><td><input maxlength="10" size="10" class="userStart e-0" \/><\/td><\/tr><\/table><\/form><a id="initial-confirm-add" href="#" class="btn green right">Add Employee(s)</a>');
  for (i = 0; i < companyCodes.length; i++) {
    optionString += '<option value="' + companyCodes[i][0] + '">[' + companyCodes[i][0] + '] ' + companyCodes[i][1].substring(0, 11) + '...<\/option>';
  }
  $('.userCompany.e-0').html(optionString);
}

function createStamp(userId, stamp) {
  $.ajax({
    type: 'POST',
    url: 'timeEdit/create_stamp.php',
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
      day  = schedule.day;
  console.log(userId);
  $.ajax({
    type: 'POST',
    url: 'timeEdit/add_schedule.php',
    data: 'userId=' + userId + '&day=' + day + '&week=' + week + '&year=' + year,
    success: function() {
      fetchSchedule({userId: userId, week: week, year: year});
    }
  });
}
