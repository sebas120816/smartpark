function actualizarCountdowns() {
  document.querySelectorAll(".countdown[data-expira]").forEach((item) => {
    const expira = new Date(item.dataset.expira.replace(" ", "T"));
    const ahora = new Date();
    const diff = Math.floor((expira.getTime() - ahora.getTime()) / 1000);

    if (Number.isNaN(expira.getTime())) {
      item.textContent = item.dataset.expira;
      return;
    }

    if (diff <= 0) {
      item.textContent = "Vencida";
      item.classList.remove("text-warning");
      item.classList.add("text-danger");
      return;
    }

    const min = Math.floor(diff / 60).toString().padStart(2, "0");
    const sec = (diff % 60).toString().padStart(2, "0");
    item.textContent = `${min}:${sec}`;

    if (diff <= 180) {
      item.classList.remove("text-warning");
      item.classList.add("text-danger");
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  actualizarCountdowns();
  setInterval(actualizarCountdowns, 1000);

  document.querySelectorAll(".confirm-form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const message = form.dataset.confirm || "¿Confirmar acción?";
      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });
});
