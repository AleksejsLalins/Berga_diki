document.addEventListener("DOMContentLoaded", () => {
  const pondMap = document.getElementById("pondMap");
  const pondModal = document.getElementById("pondMapModal");

  if (pondMap && pondModal) {
    pondMap.addEventListener("click", () => {
      pondModal.classList.add("show");
    });

    pondModal.addEventListener("click", () => {
      pondModal.classList.remove("show");
    });
  }
});
