jQuery(document).ready(function ($) {
  // Mobile Menu Toggle
  const btn = $(".tm-hamburger-btn");
  const menu = $(".tm-mobile-menu");
  const menuContent = $(".tm-account-content");

  btn.on("click", function () {
    $(this).toggleClass("active");
    menu.stop().slideToggle(250);
    // menuContent margin top 0 with padding
    if (menuContent.css("margin-top") === "0px") {
      menuContent.css("margin-top", "66px");
      menuContent.css("padding-top", "35px");
    } else {
      menuContent.css("margin-top", "0");
      menuContent.css("padding-top", "0px");
    }
  });

  // ⭐ AUTO FIX: Reset sidebar on window resize
  $(window).on("resize", function () {
    if ($(window).width() > 768) {
      menu.removeAttr("style").hide();
    }
  });
});
