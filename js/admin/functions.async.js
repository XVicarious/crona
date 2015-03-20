var SECOND = 1000, DAY_LENGTH = 86400000, DAY_LENGTH_SECONDS = 86400, $inputPicker = null, picker = null;

function getPermissions() {
  $.ajax({
    type: 'POST',
    url: 'export_permissions.php',
    success: function(data) {
      $('#exportC').html(data);
      // todo: doesn't successfully export the script, why?
      $('#export-times').find('.modal-export').click(function() {
        $.getScript('export.php?companyCode='+$('#companyCode').val());
      });
    }
  });
}
function getOffsetString() {
  var offsetMinutes = (new Date()).getTimezoneOffset();
  var absoluteOffsetMinutes = offsetMinutes + Math.abs(offsetMinutes);
  var offsetString = '';
  var offsetHourString = '';
  var offsetMinuteString = '';
  var extraOffset = 0;
  if (absoluteOffsetMinutes) {
    offsetString = '-';
    absoluteOffsetMinutes = offsetMinutes;
  } else {
    offsetString = '+';
    absoluteOffsetMinutes = Math.abs(offsetMinutes);
  }
  var offset = absoluteOffsetMinutes / 60;
  var flooredOffset = Math.floor(offset);
  if (flooredOffset < 10) {
    offsetHourString = '0'+flooredOffset;
  } else {
    offsetHourString = ''+flooredOffset;
  }
  if (flooredOffset !== offset) {
    extraOffset = absoluteOffsetMinutes - (flooredOffset * 60);
  }
  if (extraOffset < 10) {
    offsetMinuteString = '00';
  } else {
    offsetMinuteString = ''+extraOffset;
  }
  offsetString += offsetHourString + offsetMinuteString;
  return offsetString;
}
function rowClear(rowClass) {
  var isClear = true;
  $('input.' + rowClass).each(function() {
    if ($(this).val().length) {
      isClear = false;
      return false;
    }
  });
  $('select.' + rowClass).each(function() {
    if ($(this).val() !== null) {
      isClear = false;
      return false;
    }
  });
  return isClear;
}
function getEmployee(parameters) {
  var id = parameters.id;
  var range = parameters.range;
  var extraString = parameters.extraString;
  range = range || $('#range').children(':selected').val();
  extraString = extraString || '';
  $.ajax({
    type: 'POST',
    url: 'get_timecard.php',
    data: 'employee=' + id + '&range=' + range + extraString,
    success: function(data) {
      $('#ajaxDiv').html(data);
      $inputPicker = $('.datepicker').pickadate({
        selectMonths: true,
        selectYears: true,
        onSet: function(thing, value) {
          var fixedDate = this.get('value');
          fixedDate = moment(fixedDate+' 00:00:00 '+getOffsetString(),'D MMMM, YYYY h:m:s Z').utc().unix();
          var endOfDay = fixedDate + (DAY_LENGTH_SECONDS - 1);
          var extraString = '&date0='+fixedDate+'&date1='+endOfDay;
          getEmployee({id: id, range: 'specificDate', extraString: extraString})
          this.close();
        }
      });
      $inputPicker.css('display','none');
      picker = $inputPicker.pickadate('picker');
      $('#range').val(range);
      bindNewDate();
    }
  });
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
function bindNewDate() {
  var newDateClass = $('.newDate');
  newDateClass.each(function() {
    var $myGrandparent = $(this).parent().parent(),
      earlierDay = $myGrandparent.prev().attr('stamp-day'),
      thisDay = $myGrandparent.attr('stamp-day'),
      userId = $('#timecard').attr('user-id');
    if (!earlierDay) {
      var thisThisDay = new Date(thisDay);
      $(this).datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        maxDate: thisThisDay,
        onSelect: function(date) {
          date = $.datepicker.parseDate('yy-mm-dd', date);
          date = $.datepicker.formatDate('@', date) / SECOND;
          $.ajax({
            type: 'POST',
            url: 'timeEdit/create_stamp.php',
            data: 'user=' + userId + '&date=' + date,
            success: getEmployee({id: userId})
          });
        }
      });
    } else if (!thisDay) {
      var earlierDay2 = new Date(earlierDay);
      earlierDay2.setDate(earlierDay2.getDate() + 2);
      $(this).datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: earlierDay2,
        onSelect: function(date) {
          date = $.datepicker.parseDate('yy-mm-dd', date);
          date = $.datepicker.formatDate('@', date) / SECOND;
          createStamp(userId, date);
        }
      });
    } else {
      earlierDay = new Date(earlierDay);
      thisDay = new Date(thisDay);
      var difference = thisDay.getTime() - earlierDay.getTime();
      earlierDay.setDate(earlierDay.getDate() + 2);
      if (difference > DAY_LENGTH * 2) {
        $(this).datepicker({
          dateFormat: 'yy-mm-dd',
          showOtherMonths: true,
          selectOtherMonths: true,
          minDate: earlierDay,
          maxDate: thisDay,
          onSelect: function(date) {
            date = $.datepicker.formatDate('@', $.datepicker.parseDate('yy-mm-dd', date)) / SECOND;
            createStamp(userId, date);
          }
        });
      }
    }
  });
  newDateClass.click(function() {
    var twoParent = $(this).parent().parent(),
      earlierDay = twoParent.prev().attr('stamp-day'),
      thisDay = twoParent.attr('stamp-day'),
      userId = $('#timecard').attr('user-id');
    if (earlierDay && thisDay) {
      earlierDay = new Date(earlierDay);
      thisDay = new Date(thisDay);
      var difference = thisDay.getTime() - earlierDay.getTime();
      earlierDay.setDate(earlierDay.getDate() + 2);
      if (difference > DAY_LENGTH && difference <= DAY_LENGTH * 2) {
        thisDay = new Date(thisDay);
        thisDay.setDate(thisDay.getDate() - 1);
        thisDay = (thisDay.getTime() / SECOND) + offsetInSeconds;
        createStamp(userId, thisDay);
        return false;
      }
    }
  });
  /*
  var rDate = $('#r');
  rDate.datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function(date) {
      if ($('#range').val() === 'specificDate') {
        date = $.datepicker.formatDate('@', $.datepicker.parseDate('yy-mm-dd', date)) / SECOND;
        var userId = $('#timecard').attr('user-id'),
          date1 = date + (DAY_LENGTH_SECONDS - 1),
          extraString = '&date0=' + date + '&date1=' + date1;
        getEmployee({id: userId, range: 'specificDate', extraString: extraString});
      }
    },
    onClose: function() {
      if ($('#range').val() !== 'specificDate') {
        $('#r2').datepicker('show');
      }
    }
  });
  $('#r2').datepicker({
    dateFormat: 'yy-mm-dd',
    minDate: $.datepicker.parseDate('yy-mm-dd', rDate.val()),
    onSelect: function(date) {
      var userId = $('#timecard').attr('user-id'),
        date0 = $.datepicker.formatDate('@', $.datepicker.parseDate('yy-mm-dd', $('#r').val())) / SECOND,
        date1 = ($.datepicker.formatDate('@', $.datepicker.parseDate('yy-mm-dd', date)) / SECOND) + DAY_LENGTH_SECONDS,
        extraString = '&date0=' + date0 + '&date1=' + date1;
      getEmployee({id: userId, range: 'specificDate', extraString: extraString});
    }
  });
  $('#range').blur(function() {
    $(this).change();
  });
  */
}
function validateEmail(email) {
  return email.match(/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/)[1];
}
function validMoment(timestamp) {
  var validFormats = ['YYYY/MM/DD', 'YY/MM/DD', 'MM/DD/YYYY', 'MM/DD/YY', 'DD/MM/YYYY', 'DD/MM/YY'];
  var isValid = false; // we always assume they did it wrong
  for (var format in validFormats) {
    isValid = isValid || moment(timestamp, format);
  }
  return isValid;
  //var valid = moment(timestamp,"YYYY/MM/DD").isValid() || moment(timestamp, )
}
function fetchSchedule(parameters) {
  var userId = parameters.userId;
  var week = parameters.week;
  var year = parameters.year;
  // It is useless if we weren't provided a userId
  if (userId === 'undefined') {
    return false;
  }
  // If the week or year isn't defined, get the current one
  week = week || moment().week();
  year = year || moment().year();
  var data = {userId: userId, week: week, year: year};
  var Schedule = Backbone.Model.extend({});
  var ScheduleList = Backbone.Collection.extend({
        model: Schedule,
        url: 'get_schedule.php'
      }),
      columns = [{
        name: 'id',
        renderable: false,
        cell: 'integer'
      },{
        id: 'day',
        name: 'day',
        label: 'Day',
        editable: false,
        cell: 'string',
        formatter: _.extend({}, Backgrid.CellFormatter.prototype, {
          fromRaw: function(rawValue) {
            //Parse the values from this fetch to display a proper date
            moment.locale('en-US');
            // Have to subtract one from rawValue for a hotfix.  Unknown as to why it thinks Monday is the start of the
            // week in the en-US locale.
            var myMoment = moment(year + ' ' + week + ' ' + --rawValue, 'YYYY WW E');
            return myMoment.format('ddd MM/DD');
          }
        })
      },{
        name: 'in',
        label: 'In',
        cell: Backgrid.Extension.MomentCell.extend({
          modelFormat: 'X',
          displayFormat: 'h:mm a',
          displayInUTC: false
        }),
        formatter: _.extend({}, Backgrid.Extension.MomentFormatter.prototype, {
          toRaw: function(formattedValue, model) {
            var day = model.attributes.day;
            var builtString = year + ' ' + week + ' ' + --day + ' ' + formattedValue;
            // Format that like I want to...
            var mom = moment(builtString, 'YYYY WW E h:mm a');
            return mom.format('X');
          }
        })
      },{
        name: 'out',
        label: 'Out',
        cell: Backgrid.Extension.MomentCell.extend({
          modelFormat: 'X',
          displayFormat: 'h:mm a',
          displayInUTC: false
        }),
        formatter: _.extend({}, Backgrid.Extension.MomentFormatter.prototype, {
          toRaw: function(formattedValue, model) {
            var day = model.attributes.day;
            var builtString = year + ' ' + week + ' ' + --day + ' ' + formattedValue;
            // Format that like I want to...
            var mom = moment(builtString, 'YYYY WW E h:mm a');
            return mom.format('X');
          }
        })
      },{
        name: 'department',
        label: 'Department',
        cell: 'integer'
      }];
  var scheduleDays = new ScheduleList();
  var grid = new Backgrid.Grid({
        columns: columns,
        collection: scheduleDays
      }),
      $schedule = $('#timecardDiv');
  grid.listenTo(scheduleDays, "backgrid:edited", function (model, column) {
    if (column.attributes.name !== 'department') {
      // we need in if out, and out if in
      var siblingName = column.attributes.name === 'in' ? 'out' : 'in';
      // the cells like to change the values to strings, make sure they're ints because we're about to work with them
      var newValue = parseInt(model.attributes[column.attributes.name]);
      var sibling = parseInt(model.attributes[siblingName]);
      // todo: make sure to tell the user they're wrong
      if (siblingName === 'in' && newValue < sibling) {
        // throw an error, you can't leave before you get there
        model.attributes[column.attributes.name] = model._previousAttributes[column.attributes.name];
        return false;
      } else if (siblingName === 'out' && sibling < newValue) {
        // same thing, different sibling
        model.attributes[column.attributes.name] = model._previousAttributes[column.attributes.name];
        return false;
      }
    }
    if (model.attributes.id) {
      $.ajax({
        type: 'POST',
        url: 'timeEdit/change_schedule.php',
        data: 'id=' + model.attributes.id + '&' + column.attributes.name + '=' + model.attributes[column.attributes.name],
        success: function() {
          scheduleDays.fetch({data: data, reset: true});
        }
      });
    } else {
      $.ajax({
        type: 'POST',
        url: 'timeEdit/add_schedule.php',
        data: 'userId=' + userId + '&' + column.attributes.name + '=' + model.attributes[column.attributes.name],
        success: function() {
          scheduleDays.fetch({data: data, reset:true});
        }
      });
    }
  });
  grid.render().sort('day','ascending');
  $schedule.html(grid.render().el);
  //$schedule.spin('large');
  scheduleDays.fetch({data: data, reset: true});
  $schedule.before('<a id="schedule-range-button" class="btn"><i class="mdi-action-event center"></i></a>');
  return true;
}
function getEmployees() {
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
    events: {'click': 'onClick'},
    onClick: function() {
      Backbone.trigger('rowclicked', this.model);
    }
  });
  Backbone.on('rowclicked', function(model) {
    var $timeDiv = $('#timecardDiv');
    $timeDiv.empty();
    $timeDiv.spin('large', '#000');
    $('.spinner').css('padding-top', '1%').css('padding-bottom', '1%');
    if (mode === 'schedule') {
      fetchSchedule({userId: model.id});
    } else {
      getEmployee({id: model.id, range: 'this'});
    }
  });
  var pageableGrid = new Backgrid.Grid({
    row: ClickableRow,
    columns: columns,
    collection: employeeListPageable
  }), $employeeList = $('#ajaxDiv');
  $employeeList.html(pageableGrid.render().el);
  employeeListPageable.getFirstPage({fetch: true});
}
