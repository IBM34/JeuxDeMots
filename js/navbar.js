window.onscroll = function() {stick()};

var navbar = document.getElementById("navbar");
var sticky = navbar.offsetTop;

function stick() {
  if (window.pageYOffset >= sticky) {
    navbar.classList.add("sticky")
  } else {
    navbar.classList.remove("sticky");
  }
}
$('#accordion').on('shown.bs.collapse', function () {
  
  var panel = $(this).find('.in');
  
  $('html, body').animate({
        scrollTop: panel.offset().top - 100
  }, 300);
  
});

