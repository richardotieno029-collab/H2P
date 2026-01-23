
const btn = document.getElementById("accountBtn");
const dropdown = document.getElementById("accountDropdown");

btn.addEventListener("click", function (e) {
    e.stopPropagation();
    dropdown.classList.toggle("show");
});

// close dropdown when clicking elsewhere
document.addEventListener("click", function () {
    dropdown.classList.remove("show");
});