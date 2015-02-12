/*
 backgrid-filter
 http://github.com/wyuenho/backgrid

 Copyright (c) 2013 Jimmy Yuen Ho Wong and contributors
 Licensed under the MIT @license.
 */
!function(a, b) {
  "object" == typeof exports ? !function() {
    var a;
    try {
      a = require("lunr")
    } catch (c) {
    }
    module.exports = b(require("underscore"), require("backbone"), require("backgrid"), a)
  }() : b(a._, a.Backbone, a.Backgrid, a.lunr)
}(this, function(a, b, c, d) {
  "use strict";
  var e = c.Extension.ServerSideFilter = b.View.extend({
    tagName: "form",
    className: "backgrid-filter form-search",
    template: function(a) {
      return '<span class="search">&nbsp;</span><input type="search" ' + (a.placeholder ? 'placeholder="' + a.placeholder + '"' : "") + ' name="' + a.name + '" ' + (a.value ? 'value="' + a.value + '"' : "") + '/><a class="clear" data-backgrid-action="clear" href="#">&times;</a>'
    },
    events: {
      "keyup input[type=search]": "showClearButtonMaybe",
      "click a[data-backgrid-action=clear]": "clear",
      submit: "search"
    },
    name: "q",
    value: null,
    placeholder: null,
    initialize: function(a) {
      e.__super__.initialize.apply(this, arguments), this.name = a.name || this.name, this.value = a.value || this.value, this.placeholder = a.placeholder || this.placeholder, this.template = a.template || this.template;
      var c = this.collection, d = this;
      b.PageableCollection && c instanceof b.PageableCollection && "server" == c.mode && (c.queryParams[this.name] = function() {
        return d.searchBox().val() || null
      })
    },
    clearSearchBox: function() {
      this.value = null, this.searchBox().val(null), this.showClearButtonMaybe()
    },
    showClearButtonMaybe: function() {
      var a = this.clearButton(), b = this.searchBox().val();
      b ? a.show() : a.hide()
    },
    searchBox: function() {
      return this.$el.find("input[type=search]")
    },
    clearButton: function() {
      return this.$el.find("a[data-backgrid-action=clear]")
    },
    query: function() {
      return this.value = this.searchBox().val(), this.value
    },
    search: function(a) {
      a && a.preventDefault();
      var c = {}, d = this.query();
      d && (c[this.name] = d);
      var e = this.collection;
      b.PageableCollection && e instanceof b.PageableCollection ? e.getFirstPage({
        data: c,
        reset: !0,
        fetch: !0
      }) : e.fetch({data: c, reset: !0})
    },
    clear: function(a) {
      a && a.preventDefault(), this.clearSearchBox();
      var c = this.collection;
      b.PageableCollection && c instanceof b.PageableCollection ? c.getFirstPage({
        reset: !0,
        fetch: !0
      }) : c.fetch({reset: !0})
    },
    render: function() {
      return this.$el.empty().append(this.template({
        name: this.name,
        placeholder: this.placeholder,
        value: this.value
      })), this.showClearButtonMaybe(), this.delegateEvents(), this
    }
  }), f = c.Extension.ClientSideFilter = e.extend({
    events: a.extend({}, e.prototype.events, {
      "click a[data-backgrid-action=clear]": function(a) {
        a.preventDefault(), this.clear()
      }, "keydown input[type=search]": "search", submit: function(a) {
        a.preventDefault(), this.search()
      }
    }), fields: null, wait: 149, initialize: function(b) {
      f.__super__.initialize.apply(this, arguments), this.fields = b.fields || this.fields, this.wait = b.wait || this.wait, this._debounceMethods(["search", "clear"]);
      var c = this.collection = this.collection.fullCollection || this.collection, d = this.shadowCollection = c.clone();
      this.listenTo(c, "add", function(a, b, c) {
        d.add(a, c)
      }), this.listenTo(c, "remove", function(a, b, c) {
        d.remove(a, c)
      }), this.listenTo(c, "sort", function(a) {
        this.searchBox().val() || d.reset(a.models)
      }), this.listenTo(c, "reset", function(b, c) {
        c = a.extend({reindex: !0}, c || {}), c.reindex && null == c.from && null == c.to && d.reset(b.models)
      })
    }, _debounceMethods: function(b) {
      a.isString(b) && (b = [b]), this.undelegateEvents();
      for (var c = 0, d = b.length; d > c; c++) {
        var e = b[c], f = this[e];
        this[e] = a.debounce(f, this.wait)
      }
      this.delegateEvents()
    }, makeRegExp: function(a) {
      return new RegExp(a.trim().split(/\s+/).join("|"), "i")
    }, makeMatcher: function(a) {
      var b = this.makeRegExp(a);
      return function(a) {
        for (var c = this.fields || a.keys(), d = 0, e = c.length; e > d; d++)if (b.test(a.get(c[d]) + ""))return !0;
        return !1
      }
    }, search: function() {
      var b = a.bind(this.makeMatcher(this.query()), this), c = this.collection;
      c.pageableCollection && c.pageableCollection.getFirstPage({silent: !0}), c.reset(this.shadowCollection.filter(b), {reindex: !1})
    }, clear: function() {
      this.clearSearchBox();
      var a = this.collection;
      a.pageableCollection && a.pageableCollection.getFirstPage({silent: !0}), a.reset(this.shadowCollection.models, {reindex: !1})
    }
  }), g = c.Extension.LunrFilter = f.extend({
    ref: "id", fields: null, initialize: function(a) {
      g.__super__.initialize.apply(this, arguments), this.ref = a.ref || this.ref;
      var b = this.collection = this.collection.fullCollection || this.collection;
      this.listenTo(b, "add", this.addToIndex), this.listenTo(b, "remove", this.removeFromIndex), this.listenTo(b, "reset", this.resetIndex), this.listenTo(b, "change", this.updateIndex), this.resetIndex(b)
    }, resetIndex: function(b, c) {
      if (c = a.extend({reindex: !0}, c || {}), c.reindex) {
        var e = this;
        this.index = d(function() {
          a.each(e.fields, function(a, b) {
            this.field(b, a), this.ref(e.ref)
          }, this)
        }), b.each(function(a) {
          this.addToIndex(a)
        }, this)
      }
    }, addToIndex: function(a) {
      var b = this.index, c = a.toJSON();
      b.documentStore.has(c[this.ref]) ? b.update(c) : b.add(c)
    }, removeFromIndex: function(a) {
      var b = this.index, c = a.toJSON();
      b.documentStore.has(c[this.ref]) && b.remove(c)
    }, updateIndex: function(b) {
      var c = b.changedAttributes();
      c && !a.isEmpty(a.intersection(a.keys(this.fields), a.keys(c))) && this.index.update(b.toJSON())
    }, search: function() {
      var a = this.collection;
      if (!this.query())return void a.reset(this.shadowCollection.models, {reindex: !1});
      for (var b = this.index.search(this.query()), c = [], d = 0; d < b.length; d++) {
        var e = b[d];
        c.push(this.shadowCollection.get(e.ref))
      }
      a.pageableCollection && a.pageableCollection.getFirstPage({silent: !0}), a.reset(c, {reindex: !1})
    }
  })
});
