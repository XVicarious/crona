var $inputPicker = [], picker = [], saveTheDate = 0;

function getPermissions() {
  $.ajax({
    type: 'POST',
    url: 'export_permissions.php',
    success: function(data) {
      $('#exportC').html(data);
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
  var extraOffset = 0;
  if (absoluteOffsetMinutes) {
    offsetString = '-';
    absoluteOffsetMinutes = offsetMinutes;
  } else {
    offsetString = '+';
    absoluteOffsetMinutes = Math.abs(offsetMinutes);
  }
  var offset = absoluteOffsetMinutes / TimeVar.MINUTES_IN_HOUR;
  var flooredOffset = Math.floor(offset);
  if (flooredOffset < 10) {
    offsetString += '0'+flooredOffset;
  } else {
    offsetString += ''+flooredOffset;
  }
  if (flooredOffset !== offset) {
    extraOffset = absoluteOffsetMinutes - (flooredOffset * 60);
  }
  if (extraOffset < 10) {
    offsetString += '00';
  } else {
    offsetString += ''+extraOffset;
  }
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
  var url = 'get_timecard_2.php?employee='+id+'&range='+range+extraString;
  $.ajax({
    type: 'POST',
    url: 'get_timecard_2.php',
    //container: '#ajaxDiv',
    data: 'employee=' + id + '&range=' + range + extraString,
    success: function(data) {
      $('#ajaxDiv').html(data);
      var datepicker = $('#date0');
      var secondDate = $('#date1');
      $inputPicker[0] = datepicker.pickadate({
        selectMonths: true,
        selectYears: true,
        onSet: function(thing) {
          var fixedDate = this.get('value');
          var rangeChildren = $('#range').children(':selected');
          var endOfDay = 0;
          var extraString = '';
          if (thing.select) {
            fixedDate = moment(fixedDate+' 00:00:00 '+getOffsetString(),'D MMMM, YYYY h:m:s Z').utc().unix();
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
            fixedDate = moment(fixedDate+' 00:00:00 '+getOffsetString(),'D MMMM, YYYY h:m:s Z').utc().unix() + (TimeVar.SECONDS_IN_DAY - 1);
            extraString += '&date0='+saveTheDate+'&date1='+fixedDate;
            getEmployee({id: id, range: 'specificDate', extraString: extraString});
            this.close();
          }
        }
      });
      $inputPicker[1].css('display','none');
      picker[1] = $inputPicker[1].pickadate('picker');
      $inputPicker[0].css('display','none');
      picker[0] = $inputPicker[0].pickadate('picker');
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
    if (!earlierDay) { // before first stamp
      var thisThisDay = new Date(thisDay);
      var weekstart = new Date(new Date().getDate() - thisThisDay.getDay());
      $(this).datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: weekstart,
        maxDate: thisThisDay,
        onSelect: function(date) {
          var chosenDate = $.datepicker.parseDate('yy-mm-dd', date);
          chosenDate = $.datepicker.formatDate('@', chosenDate);
          createStamp(userId, chosenDate / TimeVar.MILLISECONDS_IN_SECOND);
        }
      });
    } else if (!thisDay) { // after last stamp
      // fixme: if the timecard is empty for the specified period, the whole calendar is selectable
      var earlierDay2 = new Date(earlierDay);
      var weekend = new Date(earlierDay);
      weekend.setDate(weekend.getDate() + 6);
      earlierDay2.setDate(earlierDay2.getDate() + 2);
      $(this).datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: earlierDay2,
        maxDate: weekend,
        onSelect: function(date) {
          var selectedDate = $.datepicker.parseDate('yy-mm-dd', date);
          selectedDate = $.datepicker.formatDate('@', selectedDate) / TimeVar.MILLISECONDS_IN_SECOND;
          createStamp(userId, selectedDate);
        }
      });
    } else { // between stamps
      earlierDay = new Date(earlierDay);
      thisDay = new Date(thisDay);
      var difference = thisDay.getTime() - earlierDay.getTime();
      earlierDay.setDate(earlierDay.getDate() + 2);
      if (difference > TimeVar.MILLISECONDS_IN_DAY * 2) {
        $(this).datepicker({
          dateFormat: 'yy-mm-dd',
          showOtherMonths: true,
          selectOtherMonths: true,
          minDate: earlierDay,
          maxDate: thisDay,
          onSelect: function(date) {
            var selectedDate = $.datepicker.formatDate('@', $.datepicker.parseDate('yy-mm-dd', date)) / TimeVar.MILLISECONDS_IN_SECOND;
            createStamp(userId, selectedDate);
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
      if (difference > TimeVar.MILLISECONDS_IN_DAY && difference <= TimeVar.MILLISECONDS_IN_DAY * 2) {
        thisDay = new Date(thisDay);
        thisDay.setDate(thisDay.getDate() - 1);
        thisDay = (thisDay.getTime() / TimeVar.MILLISECONDS_IN_SECOND) + offsetInSeconds;
        createStamp(userId, thisDay);
        return false;
      }
    }
  });
}

function validateEmail(email) {
  return email.match(/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/)[1];
}

function makeDraggable() {
  $('.draggableTimes').each(function() {
    $(this).find('.times').attr('disabled','true');
    $(this).draggable({stack:'.droppableTimes',containment: '#timecard',cursor: 'move',revert: true});
  });
  $('.droppabledTimes').droppable({accept:'.draggableTimes',hoverClass:'draggableHover',drop: handleDrop});
  $('i.trashBin').droppable({accept:'.draggableTimes',hoverClass:'draggableHover',drop:handleDelete});
}

function handleDrop(event, ui) {
  ui.draggable.position({of:$(this),my:'left top',at:'left top'});
}

function handleDelete(event, ui) {
  ui.draggable.draggable('disable');
  ui.draggable.draggable('option','revert',false);
  var userId = $('#timecard').attr('user-id');
  var stampId = $(ui.draggable).find('.times').attr('stamp-id');
  var defaultTime = $(ui.draggable).find('.times').attr('default-time');
  $.ajax({
    type: 'POST',
    url: 'timeEdit/delete_stamp.php',
    data: 'sid=' + stampId + '&dtime=' + defaultTime,
    success: function() {
      getEmployee({id: userId});
    }
  });
}

function validMoment(timestamp) {
  var validFormats = ['YYYY/MM/DD', 'YY/MM/DD', 'MM/DD/YYYY', 'MM/DD/YY', 'DD/MM/YYYY', 'DD/MM/YY'];
  var isValid = false; // we always assume they did it wrong
  for (var format in validFormats) {
    if (validFormats.hasOwnProperty(format)) {
      isValid = isValid || moment(timestamp, format);
    }
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
            var rawDay = rawValue;
            //Parse the values from this fetch to display a proper date
            moment.locale('en-US');
            // Have to subtract one from rawValue for a hotfix.  Unknown as to why it thinks Monday is the start of the
            // week in the en-US locale.
            var myMoment = moment(year + ' ' + week + ' ' + --rawDay, 'YYYY WW E');
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
  scheduleDays.fetch({data: data, reset: true});
  $schedule.before('<a id="schedule-range-button" class="btn"><i class="mdi-action-event center"><\/i><\/a>');
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
