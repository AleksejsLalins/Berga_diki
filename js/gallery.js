$(document).ready(function () {
    const prevBtn = document.querySelector(".prev");
    const nextBtn = document.querySelector(".next");
    const slider = document.querySelector(".slider");
    const slides = document.querySelectorAll(".slide");

    let currentIndex = 0;

    function getSlideWidthPercent() {
        return window.innerWidth <= 768 ? 100 : 33.33;
    }

    function getSlidesToShow() {
        return window.innerWidth <= 768 ? 1 : 3;
    }

    function updateSlider() {
        const slideWidth = getSlideWidthPercent();
        slider.style.transform = `translateX(-${currentIndex * slideWidth}%)`;
    }

    prevBtn.addEventListener("click", function () {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = slides.length - getSlidesToShow();
        }
        updateSlider();
    });

    nextBtn.addEventListener("click", function () {
        if (currentIndex < slides.length - getSlidesToShow()) {
            currentIndex++;
        } else {
            currentIndex = 0;
        }
        updateSlider();
    });

    // При изменении размера окна обновляем слайдер
    window.addEventListener("resize", updateSlider);

    // === Модалка ===
    $(".slider img").click(function () {
        var imgSrc = $(this).attr("src");
        var imgAlt = $(this).attr("alt");

        $(".popup-overlay img").attr("src", imgSrc);
        $(".popup-overlay img").attr("alt", imgAlt);
        $(".popup-overlay").addClass("show");
        $("body").css("overflow", "hidden");
    });

    // Закрытие модалки при клике на фон (не на картинку)
    $(".popup-overlay").click(function (e) {
        // Если клик был не по картинке, то закрываем модалку
        if (!$(e.target).closest('.popup-content img').length) {
            $(".popup-overlay").removeClass("show");
            $("body").css("overflow", "auto");
        }
    });

    // Закрытие модалки при клике на саму картинку
    $(".popup-content img").click(function () {
        $(".popup-overlay").removeClass("show");
        $("body").css("overflow", "auto");
    });
});
