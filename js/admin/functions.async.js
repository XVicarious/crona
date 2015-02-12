var SECOND = 1000, DAY_LENGTH = 86400000;
function getPermissions() {
  $.ajax({
    type: 'POST',
    url: 'export_permissions.php',
    success: function(data) {
      $('#exportC').html(data);
      $('#exportcsv').click(function() {
        $.ajax({
          type: 'POST',
          url: 'export.php',
          data: 'companyCode=' + $('#companyCode').val(),
          success: $('#exportScript').html(data)
        });
      });
    }
  });
}
function rowClear(rowClass) {
  var isClear = true;
  $('input.' + rowClass).each(function() {
    if ($(this).val().length) {
      isClear = false;
      return false;
    }
  });
  //noinspection FunctionWithMultipleReturnPointsJS
  $('select.' + rowClass).each(function() {
    if ($(this).val() !== null) {
      isClear = false;
      return false;
    }
  });
  return isClear;
}
function getEmployee(id, range, extraString) {
  range = range || $('#range').children(':selected').val();
  extraString = extraString || '';
  $.ajax({
    type: 'POST',
    url: 'get_timecard.php',
    data: 'employee=' + id + '&range=' + range + extraString,
    success: function(data) {
      var $timecard = $('#timecardDiv');
      $timecard.empty();
      $timecard.html(data);
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
    success: getEmployee(userId)
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
            success: getEmployee(userId)
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
      if (difference > DAY_LENGTH) {
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
      if (difference > DAY_LENGTH) {
        if (difference <= DAY_LENGTH * 2) {
          thisDay = new Date(thisDay);
          thisDay.setDate(thisDay.getDate() - 1);
          thisDay = (thisDay.getTime() / SECOND) + 18000;
          createStamp(userId, thisDay);
          return false;
        }
      }
    }
  });
  var rDate = $('#r');
  rDate.datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function(date) {
      if ($('#range').val() === 'specificDate') {
        date = $.datepicker.formatDate('@', $.datepicker.parseDate('yy-mm-dd', date)) / SECOND;
        var userId = $('#timecard').attr('user-id'),
            date1 = date + 86399,
            extraString = '&date0=' + date + '&date1=' + date1;
        getEmployee(userId, 'specificDate', extraString);
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
          date1 = ($.datepicker.formatDate('@', $.datepicker.parseDate('yy-mm-dd', date)) / SECOND) + 86400,
                  extraString = '&date0=' + date0 + '&date1=' + date1;
      getEmployee(userId, 'specificDate', extraString);
    }
  });
  $('#range').blur(function() {
    $(this).change();
  });
}
function validateEmail(email) {
  return email.match(/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/)[1];
}
function validMoment(timestamp) {
  var validFormats = ['YYYY/MM/DD','YY/MM/DD','MM/DD/YYYY','MM/DD/YY','DD/MM/YYYY','DD/MM/YY'];
  var isValid = false; // we always assume they did it wrong
  for (var format in validFormats) {
    isValid = isValid || moment(timestamp,format);
  }
  return isValid;
  //var valid = moment(timestamp,"YYYY/MM/DD").isValid() || moment(timestamp, )
}
/*
function fetchSchedule(userId, week, year) {
  // If the week or year isn't defined, get the current one
  week = week !== 'undefined' ? week : moment().week();
  year = year !== 'undefined' ? year : moment().year();
  // It is useless if we weren't provided a userId
  if (userId === 'undefined') {
    return false;
  }
  //noinspection JSLint
  var Schedule = Backbone.Model.extend({}),
      ScheduleList = Backbone.Collection.extend({
        model: Schedule,
        url: 'get_schedule.json.php?userId=' + userId +
             '&week=' + week + '&year=' + year
      }),
      columns = [{
        id: 'day',
        label: 'Day',
        editable: false,
        cell: 'string'
        //formatter: _.extend({}, Backgrid.CellFormatter.prototype, {
        //  fromRaw: function(rawValue) {
            // Parse the values from this fetch to display a proper date
         //   var myMoment = moment(year + ' ' +
         //                         week + ' ' + rawValue, 'YYYY WW E');
         //   return myMoment.format('ddd MM/DD');
         // }
       // })
      }, {
        name: 'in',
        label: 'In',
        cell: Backgrid.Extension.MomentCell.extend({
          modelFormat: 'HH:mm'
        })
      }, {
        name: 'out',
        label: 'Out',
        cell: Backgrid.Extension.MomentCell.extend({
          modelFormat: 'HH:mm'
        })
      }, {
        name: 'department',
        label: 'Department',
        cell: 'integer'
      }],
      grid = new Backgrid.Grid({
        columns: columns,
        collection: ScheduleList
      }),
      $schedule = $('#schedule');
  $schedule.html(grid.render().el);
  ScheduleList.fetch({reset: true});
  return true;
}*/
