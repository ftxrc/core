</div>

<div class="form-panel two">
  <div class="form-header">
    <h1>SimpleScript</h1>
  </div>
  <div class="form-content">
    <div class="form-group">
      <label for="username">Menu</label>
      <a href="<?php echo $settings["admin_url"]; ?>?page=dash">Dashboard</a>
    </div>
    <div class="form-group">
      <label for="username">About</label>
      SimpleScript is a coding lanuage that is easy to learn and manage. This admin page is designed to allow the user to update SimpleScript but also see site stats and usage data.
    </div>
  </div>
</div>
</div>

<div class="pen-footer">SimpleScript Admin Dashboard. SimpleScript is a free coding lanuage, to learn more visit <a href="https://www.codesimplescript.com" target="_blank">codesimplescript.com</a></div>
<script src="https://www.nodehost.ca/themes/node/js/jquery.min.js"></script>
<script>
$(document).ready(function() {
  var panelOne = $('.form-panel.one')[0].scrollHeight,
    panelTwo = $('.form-panel.two')[0].scrollHeight;

  $('.form-panel.two').not('.form-panel.two.active').on('click', function(e) {
    $('.form-toggle').addClass('visible');
    $('.form-panel.one').addClass('hidden');
    $('.form-panel.two').addClass('active');
    $('.form').animate({
      'height': panelTwo
    }, 200);
  });

  $('.form-toggle').on('click', function(e) {
    $(this).removeClass('visible');
    $('.form-panel.one').removeClass('hidden');
    $('.form-panel.two').removeClass('active');
    $('.form').animate({
      'height': panelOne
    }, 200);
  });
});
</script>

</body>
</html>
