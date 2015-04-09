$(function() {
  $(document).on('click', '#modlog-button', function() {
    var modLogModal = $('#dialog');
    var modLogText = modLogModal.find('.modal-text');
    modLogModal.find('.modal-footer').html(
      '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close">Close</a>' +
      '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-refresh">Refresh</a>'
    );
    modLogModal.openModal({
      ready: function() {
        var ModificationList = Backbone.Model.extend({});
        var ModificationListList = Backbone.Collection.extend({
          model: ModificationList,
          url: 'get_modifications.json.php'
        });
        var modList = new ModificationListList();
        var columns = [{
          name: 'modtime',
          label: 'Date/Time Changed',
          cell: Backgrid.StringCell,
          editable: false
        }, {
          name: 'userchanged',
          label: 'User Changed',
          cell: Backgrid.StringCell,
          editable: false
        }, {
          name: 'from',
          label: 'Original Value',
          cell: Backgrid.StringCell,
          editable: false
        }, {
          name: 'to',
          label: 'New Value',
          cell: Backgrid.StringCell,
          editable: false
        }];
        var grid = new Backgrid.Grid({
          columns: columns,
          collection: modList
        });
        modLogText.html(grid.render().el);
        modList.fetch({reset:true});
        modLogModal.find('.modal-close').click(function() {
          modLogModal.close();
        });
        modLogModal.find('.modal-refresh').click(function() {
          modList.fetch({reset:true});
        });
      }
    });
  });
  $(document).on('click', '#timestamp-button', function() {
    var modLogModal = $('#dialog');
    var modLogText = modLogModal.find('.modal-text');
    modLogModal.find('.modal-footer').html(
      '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-close">Close</a>' +
      '<a href="#" class="waves-effect waves-light btn-flat modal-action modal-refresh">Refresh</a>'
    );
    modLogModal.openModal({
      ready: function() {
        var ModificationList = Backbone.Model.extend({});
        var ModificationListList = Backbone.Collection.extend({
          model: ModificationList,
          url: 'get_stamp_log.json.php'
        });
        var modList = new ModificationListList();
        var columns = [{
          name: 'user',
          label: 'Username',
          cell: Backgrid.StringCell,
          editable: false
        }, {
          name: 'time',
          label: 'Timestamp',
          cell: Backgrid.StringCell,
          editable: false
        }];
        var grid = new Backgrid.Grid({
          columns: columns,
          collection: modList
        });
        modLogText.html(grid.render().el);
        modList.fetch({reset:true});
        modLogModal.find('.modal-close').click(function() {
          modLogModal.close();
        });
        modLogModal.find('.modal-refresh').click(function() {
          modList.fetch({reset:true});
        });
      }
    });
  });
});
