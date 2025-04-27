// sectormapmodal.js — модальное увеличение карты сектора с анимацией

document.addEventListener("DOMContentLoaded", () => {
  const pondMapImage = document.getElementById('pond-map-image'); // маленькая картинка
  const mapModal = document.getElementById('map-modal'); // модальное окно

  if (pondMapImage && mapModal) {
    pondMapImage.addEventListener("click", () => {
      mapModal.classList.add("show");
    });

    mapModal.addEventListener("click", () => {
      mapModal.classList.remove("show");
    });
  }
});
