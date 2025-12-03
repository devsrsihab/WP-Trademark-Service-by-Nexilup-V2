jQuery(document).ready(function ($) {
  // Mobile Menu Toggle
  const btn = $(".tm-hamburger-btn");
  const menu = $(".tm-mobile-menu");
  const menuContent = $(".tm-account-content");

  btn.on("click", function () {
    $(this).toggleClass("active");
    menu.stop().slideToggle(250);
    // menuContent margin top 0 with padding just for mobile size
    if (window.innerWidth <= 768) {
      if (menuContent.css("margin-top") === "0px") {
        menuContent.css("margin-top", "66px");
        menuContent.css("padding-top", "0px");
      } else {
        menuContent.css("margin-top", "0");
        menuContent.css("padding-top", "0px");
      }
    }
  });

  // ⭐ AUTO FIX: Reset sidebar on window resize
  $(window).on("resize", function () {
    if ($(window).width() > 768) {
      menu.removeAttr("style").hide();
      // Reset menuContent styles when switching to desktop view
      menuContent.css("margin-top", "");
      menuContent.css("padding-top", "25px");
    }
  });

  /* ---------------------------------------------------------
       PASSWORD VISIBILITY TOGGLE (Vanilla JS)
    --------------------------------------------------------- */
  const toggles = document.querySelectorAll(".tm-password-toggle");

  toggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const input = this.previousElementSibling;

      if (input.type === "password") {
        input.type = "text";
        this.textContent = "🙈"; // hide icon
      } else {
        input.type = "password";
        this.textContent = "👁️"; // show icon
      }
    });
  });
});
