var $inputPicker = [], picker = [], saveTheDate = 0;
$(function() {
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
  getEmployee({range: 'this'});
});
function getEmployee(parameters) {
  var id = parameters.id;
  var range = parameters.range;
  var extraString = parameters.extraString;
  range = range || $('#range').children(':selected').val();
  extraString = extraString || '';
  $.ajax({
    type: 'POST',
    url: 'masterTime.php',
    data: 'range=' + range + extraString + '&id=' + id,
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
    }
  });
}
