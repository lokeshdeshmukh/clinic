document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body?.dataset.baseUrl || window.location.origin;
  const doctorSelect = document.querySelector("[data-slot-doctor]");
  const dateInput = document.querySelector("[data-slot-date]");
  const slotContainer = document.querySelector("[data-slot-results]");
  const slotInput = document.querySelector("[data-slot-input]");

  const renderSlots = async () => {
    if (!doctorSelect || !dateInput || !slotContainer || !slotInput) {
      return;
    }

    const doctorId = doctorSelect.value;
    const date = dateInput.value;

    slotContainer.innerHTML = '<p class="text-sm text-slate-500">Select a doctor and date to view live availability.</p>';
    slotInput.value = "";

    if (!doctorId || !date) {
      return;
    }

    try {
      const response = await fetch(`${baseUrl}/api/v1/doctors/${doctorId}/slots?date=${encodeURIComponent(date)}`, {
        headers: {
          Accept: "application/json"
        }
      });
      const payload = await response.json();

      if (!response.ok || !payload.data) {
        slotContainer.innerHTML = `<p class="text-sm text-rose-600">${payload.message || "No slots available."}</p>`;
        return;
      }

      if (payload.data.length === 0) {
        slotContainer.innerHTML = '<p class="text-sm text-slate-500">No open slots for that date.</p>';
        return;
      }

      slotContainer.innerHTML = payload.data.map((slot) => `
        <button type="button" data-slot-value="${slot.start_time}" class="slot-chip rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-brand-500 hover:text-brand-600">
          ${slot.label}
        </button>
      `).join("");

      slotContainer.querySelectorAll("[data-slot-value]").forEach((button) => {
        button.addEventListener("click", () => {
          slotContainer.querySelectorAll("[data-slot-value]").forEach((item) => {
            item.classList.remove("border-brand-500", "bg-brand-50", "text-brand-700");
          });
          button.classList.add("border-brand-500", "bg-brand-50", "text-brand-700");
          slotInput.value = button.dataset.slotValue || "";
        });
      });
    } catch (error) {
      slotContainer.innerHTML = '<p class="text-sm text-rose-600">Unable to load slots right now.</p>';
    }
  };

  if (doctorSelect) {
    doctorSelect.addEventListener("change", renderSlots);
  }

  if (dateInput) {
    dateInput.addEventListener("change", renderSlots);
  }

  const calendarElement = document.querySelector("[data-calendar]");
  if (calendarElement && window.FullCalendar) {
    const events = JSON.parse(calendarElement.dataset.events || "[]");
    const calendar = new window.FullCalendar.Calendar(calendarElement, {
      initialView: "dayGridMonth",
      height: 640,
      events
    });
    calendar.render();
  }

  document.querySelectorAll("[data-chart]").forEach((canvas) => {
    if (!window.Chart) {
      return;
    }

    const labels = JSON.parse(canvas.dataset.labels || "[]");
    const values = JSON.parse(canvas.dataset.values || "[]");
    const label = canvas.dataset.chartLabel || "Series";

    new window.Chart(canvas, {
      type: canvas.dataset.chart || "bar",
      data: {
        labels,
        datasets: [{
          label,
          data: values,
          borderColor: "#2563eb",
          backgroundColor: "rgba(37, 99, 235, 0.15)",
          fill: true,
          tension: 0.32
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true
          }
        }
      }
    });
  });
});
