// Mobile menu button
(function () {
    var btn = document.getElementById("navToggle");
    var menu = document.getElementById("navLinks");
    if (!btn || !menu) { return; }
    btn.addEventListener("click", function () {
        var open = menu.classList.toggle("open");
        btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
})();
