document.addEventListener("DOMContentLoaded", function () {
    const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll(".nav-links a");

    function onScroll() {
        let scrollY = window.pageYOffset;

        sections.forEach((section) => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute("id");

            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                navLinks.forEach((link) => {
                    link.classList.remove("active");
                    if (link.getAttribute("href") === "#" + sectionId) {
                        link.classList.add("active");
                    }
                });
            }
        });
    }

    window.addEventListener("scroll", onScroll);
    onScroll(); // для начальной инициализации
});
