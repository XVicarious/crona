$(function() {
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
      fetchSchedule(model.id, moment().week(), moment().year());
    } else {
      getEmployee(model.id, 'this');
    }
    var dialog = $('#dialog-timecard').dialog({
      modal: true,
      draggable: false,
      width: $(window).width() * 0.9,
      height: 'auto',
      resizable: false,
      buttons: {
        'Close': function() {
          $(this).dialog('close');
        }
      }
    });
    $(document).ajaxComplete(function() {
      var $dialogTC = $('#dialog-timecard');
      $dialogTC.dialog('option', 'position', $dialogTC.dialog('option', 'position'));
    });
  });
  var pageableGrid = new Backgrid.Grid({
    row: ClickableRow,
    columns: columns,
    collection: employeeListPageable
  }), $employeeList = $('#ajaxDiv');
  $employeeList.html(pageableGrid.render().el);
  //var filter = new Backgrid.Extension.ClientSideFilter({
  //  collection: employeeListPageable,
  //  fields: ['name', 'adpid', 'companycode', 'departmentcode']
  //});
  //$employeeList.before(filter.render().el);
  //$(filter.el).css({float: 'right', margin: '20px'});
  employeeListPageable.getFirstPage({fetch: true});
});
