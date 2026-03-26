function initScrollTop() {

    const btn = document.getElementById("scrollTopBtn");

    window.addEventListener("scroll", () => {
        btn.style.display =
            document.documentElement.scrollTop > 300 ? "block" : "none";
    });

    btn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
}