// Landing page interactivity
document.addEventListener("DOMContentLoaded", function () {
  // Handle FAQ item toggle
  const faqItems = document.querySelectorAll(".faq-item");
  faqItems.forEach(function (item) {
    item.addEventListener("click", function () {
      this.classList.toggle("active");
    });
  });

  // Smooth scroll for anchor links (optional enhancement)
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth" });
      }
    });
  });
});
