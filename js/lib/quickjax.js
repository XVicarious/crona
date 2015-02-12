function quickjax(e, t, n, r) {
  $.ajax({
    type: e, url: t, data: n, success: function(e) {
      $(r).html(e)
    }
  });
  return false
}
function quickPost(t, n, r) {
  e("POST", t, n, r);
  return false
}
function quickGet(t, n, r) {
  e("GET", t, n, r);
  return false
}