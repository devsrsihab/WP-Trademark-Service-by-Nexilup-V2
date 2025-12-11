(function ($) {
  const deleteBtns = document.querySelectorAll(".tm-delete-price");
  console.log("log", deleteBtns);

  if (!deleteBtns.length) return; // No buttons found → stop

  deleteBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      if (!confirm("Are you sure you want to delete this pricing?")) {
        e.preventDefault(); // Stop delete
      } else {
        // If confirmed, trigger the link manually
        window.location.href = this.getAttribute("href");
      }
    });
  });
})(jQuery);
