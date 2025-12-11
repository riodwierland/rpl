// Minimal interactivity for dashboard
document.addEventListener("DOMContentLoaded", function () {
  var links = document.querySelectorAll(".sidebar a");
  links.forEach(function (l) {
    l.addEventListener("click", function (e) {
      links.forEach(function (x) {
        x.classList.remove("active");
      });
      e.currentTarget.classList.add("active");
    });
  });
});
