var $inputPicker = [], picker = [], saveTheDate = 0;

function getExportPermissions() {
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
    offsetString += '0'+flooredOffset;
  } else {
    offsetString += ''+flooredOffset;
  }
  if (flooredOffset !== offset) {
    extraOffset = absoluteOffsetMinutes - (flooredOffset * TimeVar().MINUTES_IN_HOUR);
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
    }
    return true;
  });
  $('select.' + rowClass).each(function() {
    if ($(this).val() !== null) {
      isClear = false;
    }
    return false;
  });
  return isClear;
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
    url: 'get_timecard_2.php',
    //container: '#ajaxDiv',
    data: 'mode=' + mode + '&employee=' + id + '&range=' + range + extraString,
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
            fixedDate = moment(fixedDate+' 00:00:00 '+getOffsetString(),'D MMMM, YYYY h:m:s Z').utc().unix() +
                               (TimeVar.SECONDS_IN_DAY - 1);
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
  var newDateInputClass = $('input.newDate');
  newDateInputClass.each(function() {
    var myGrandparent = $(this).parent().parent(),
      earlierDay = myGrandparent.prev().attr('stamp-day'),
      thisDay = myGrandparent.attr('stamp-day');
    var difference,
        daysBetweenTodayStamp;
    // Lets try to clean up these variables...
    var earlierDate = $(this).parent().parent().prev().attr('stamp-day'),
        todaysDate = $(this).parent().parent().attr('stamp-day'),
        userId = $('#timecard').attr('user-id');
    var rangeEndDate = moment(earlierDate || todaysDate, 'YYYY-MM-DD').endOf('week');
    var rangeStartDate = moment(earlierDate || todaysDate, 'YYYY-MM-DD').startOf('week');
    if (earlierDay === undefined) { // before first stamp
      daysBetweenTodayStamp = moment().diff(rangeStartDate, 'days');
      $(this).pickadate({
        selectMonths: true,
        selectYears: true,
        min: rangeStartDate.toArray(),
        max: -daysBetweenTodayStamp,
        onSet: function(thingSet) {
          if (thingSet.select) {
            createStamp(userId, thingSet.select / TimeVar.MILLISECONDS_IN_SECOND);
            this.close();
          }
        }
      });
    } else if (!thisDay) { // after last stamp
      // todo: test the fixme
      // fixme: if the timecard is empty for the specified period, the whole calendar is selectable
      $(this).pickadate({
        selectMonths: true,
        min: moment(todaysDate, 'YYYY-MM-DD').add(1, 'days').toArray(),
        max: rangeEndDate.toArray(),
        onSet: function(thingSet) {
          if (thingSet.select) {
            createStamp(userId, thingSet.select / TimeVar.MILLISECONDS_IN_SECOND);
          }
        }
      });
    } else { // between stamps
      difference = moment(earlierDate, 'YYYY-MM-DD').diff(moment(todaysDate, 'YYYY-MM-DD'), 'days');
      if (Math.abs(difference) > 2) {
        $(this).pickadate({
          selectMonths: true,
          selectYears: true,
          min: moment(earlierDate, 'YYYY-MM-DD').add(1, 'days').toArray(),
          max: moment(todaysDate, 'YYYY-MM-DD').subtract(1, 'days').toArray(),
          onSet: function(thingSet) {
            if (thingSet.select) {
              createStamp(userId, thingSet.select / TimeVar.MILLISECONDS_IN_SECOND);
            }
          }
        });
      }
    }
  });
  newDateClass.click(function() {
    var twoParent = $(this).parent().parent(),
      earlierDay = twoParent.prev().attr('stamp-day'),
      thisDay = twoParent.attr('stamp-day'),
      userId = $('#timecard').attr('user-id'),
      difference;
    if (earlierDay && thisDay) {
      earlierDay = new Date(earlierDay);
      thisDay = new Date(thisDay);
      difference = thisDay.getTime() - earlierDay.getTime();
      earlierDay.setDate(earlierDay.getDate() + 2);
      if (difference > TimeVar.MILLISECONDS_IN_DAY && difference <= TimeVar.MILLISECONDS_IN_DAY * 2) {
        thisDay = new Date(thisDay);
        thisDay.setDate(thisDay.getDate() - 1);
        thisDay = (thisDay.getTime() / TimeVar.MILLISECONDS_IN_SECOND) + offsetInSeconds;
        createStamp(userId, thisDay);
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
  var userId, stampId, defaultTime;
  ui.draggable.draggable('disable');
  ui.draggable.draggable('option','revert',false);
  userId = $('#timecard').attr('user-id');
  stampId = $(ui.draggable).find('.times').attr('stamp-id');
  defaultTime = $(ui.draggable).find('.times').attr('default-time');
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
  var validFormats = ['YYYY/MM/DD', 'YY/MM/DD', 'MM/DD/YYYY', 'MM/DD/YY', 'DD/MM/YYYY', 'DD/MM/YY'],
      isValid = false; // we always assume they did it wrong
  var format;
  for (format in validFormats) {
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
  var data, Schedule, ScheduleList, scheduleDays, grid, columns, $schedule;
  // It is useless if we weren't provided a userId
  if (userId === 'undefined') {
    return false;
  }
  // If the week or year isn't defined, get the current one
  week = week || moment().week();
  year = year || moment().year();
  data = {userId: userId, week: week, year: year};
  Schedule = Backbone.Model.extend({});
  ScheduleList = Backbone.Collection.extend({
        model: Schedule,
        url: 'get_schedule.php'
      });
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
            var myMoment;
            //Parse the values from this fetch to display a proper date
            moment.locale('en-US');
            // Have to subtract one from rawValue for a hotfix.  Unknown as to why it thinks Monday is the start of the
            // week in the en-US locale.
            myMoment = moment(year + ' ' + week + ' ' + --rawDay, 'YYYY WW E');
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
  scheduleDays = new ScheduleList();
  grid = new Backgrid.Grid({columns: columns, collection: scheduleDays});
  $schedule = $('#ajaxDiv');
  grid.listenTo(scheduleDays, "backgrid:edited", function(model, column) {
    var siblingName, sibling, newValue;
    if (column.attributes.name !== 'department') {
      // we need in if out, and out if in
      siblingName = column.attributes.name === 'in' ? 'out' : 'in';
      // the cells like to change the values to strings, make sure they're ints, because we're about to work with them
      newValue = parseInt(model.attributes[column.attributes.name]);
      sibling = parseInt(model.attributes[siblingName]);
      // todo: make sure to tell the user they're wrong
      if (siblingName === 'in' && newValue < sibling) {
        // throw an error, you can't leave before you get there
        model.attributes[column.attributes.name] = model._previousAttributes[column.attributes.name];
      } else if (siblingName === 'out' && sibling < newValue) {
        // same thing, different sibling
        model.attributes[column.attributes.name] = model._previousAttributes[column.attributes.name];
      }
    }
    if (model.attributes.id) {
      $.ajax({
        type: 'POST',
        url: 'timeEdit/change_schedule.php',
        data: 'id=' + model.attributes.id + '&' + column.attributes.name + '=' + newValue,
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
  var pageableGrid, $employeeList;
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
  pageableGrid = new Backgrid.Grid({
    row: ClickableRow,
    columns: columns,
    collection: employeeListPageable
  });
  $employeeList = $('#ajaxDiv');
  $employeeList.html(pageableGrid.render().el);
  employeeListPageable.getFirstPage({fetch: true});
}
