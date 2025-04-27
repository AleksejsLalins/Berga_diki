document.addEventListener("DOMContentLoaded", () => {
  const hamburger = document.getElementById("hamburger-menu");
  const navLinks = document.getElementById("nav-links");

  // Переключаем открытие меню
  hamburger.addEventListener("click", (e) => {
    e.stopPropagation();
    navLinks.classList.toggle("active");
    hamburger.classList.toggle("active"); // Добавляем класс для гамбургера
  });

  // Закрываем при клике вне меню и вне бургер-иконки
  document.addEventListener("click", (e) => {
    if (!navLinks.contains(e.target) && !hamburger.contains(e.target)) {
      navLinks.classList.remove("active");
      hamburger.classList.remove("active"); // Убираем активный класс с гамбургера
    }
  });
});
