document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body?.dataset.baseUrl || window.location.origin;
  const googleLoginForm = document.querySelector("[data-google-login-form]");
  const googleCredentialInput = document.querySelector("[data-google-credential]");

  window.handleGooglePatientSignIn = (response) => {
    if (!googleLoginForm || !googleCredentialInput || !response?.credential) {
      return;
    }

    googleCredentialInput.value = response.credential;
    googleLoginForm.submit();
  };

  const doctorSelect = document.querySelector("[data-slot-doctor]");
  const dateInput = document.querySelector("[data-slot-date]");
  const slotContainer = document.querySelector("[data-slot-results]");
  const slotInput = document.querySelector("[data-slot-input]");
  const quickDateButtons = Array.from(document.querySelectorAll("[data-quick-date]"));
  const selectedDateLabel = document.querySelector("[data-selected-date-label]");
  const selectedTimeLabel = document.querySelector("[data-selected-time-label]");
  const selectedSlotLabel = document.querySelector("[data-selected-slot-label]");

  const formatDateLabel = (value) => {
    if (!value) {
      return "Select a date";
    }

    const [year, month, day] = value.split("-").map(Number);
    if (!year || !month || !day) {
      return value;
    }

    const date = new Date(year, month - 1, day);
    return new Intl.DateTimeFormat("en-IN", {
      weekday: "short",
      day: "2-digit",
      month: "short"
    }).format(date);
  };

  const syncDateSelection = () => {
    if (!dateInput) {
      return;
    }

    quickDateButtons.forEach((button) => {
      button.classList.toggle("is-active", button.dataset.quickDate === dateInput.value);
    });

    if (selectedDateLabel) {
      selectedDateLabel.textContent = formatDateLabel(dateInput.value);
    }
  };

  const renderSlots = async () => {
    if (!doctorSelect || !dateInput || !slotContainer || !slotInput) {
      return;
    }

    const doctorId = doctorSelect.value;
    const date = dateInput.value;

    slotContainer.innerHTML = '<p class="slot-feedback">Select a date to view live availability.</p>';
    slotInput.value = "";

    if (selectedTimeLabel) {
      selectedTimeLabel.textContent = "Select time";
    }

    if (selectedSlotLabel) {
      selectedSlotLabel.textContent = "Select a slot";
    }

    if (!doctorId || !date) {
      syncDateSelection();
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
        slotContainer.innerHTML = `<p class="slot-feedback slot-feedback--error">${payload.message || "No slots available."}</p>`;
        return;
      }

      if (payload.data.length === 0) {
        slotContainer.innerHTML = '<p class="slot-feedback">No open slots for that date.</p>';
        return;
      }

      slotContainer.innerHTML = payload.data.map((slot) => `
        <button type="button" data-slot-value="${slot.start_time}" data-slot-label="${slot.label}" class="slot-chip">
          ${slot.label}
        </button>
      `).join("");

      slotContainer.querySelectorAll("[data-slot-value]").forEach((button) => {
        button.addEventListener("click", () => {
          slotContainer.querySelectorAll("[data-slot-value]").forEach((item) => {
            item.classList.remove("is-active");
          });
          button.classList.add("is-active");
          slotInput.value = button.dataset.slotValue || "";

          if (selectedTimeLabel) {
            selectedTimeLabel.textContent = button.dataset.slotLabel || "Select time";
          }

          if (selectedSlotLabel) {
            selectedSlotLabel.textContent = button.dataset.slotLabel || "Select a slot";
          }
        });
      });
    } catch (error) {
      slotContainer.innerHTML = '<p class="slot-feedback slot-feedback--error">Unable to load slots right now.</p>';
    }
  };

  if (doctorSelect) {
    doctorSelect.addEventListener("change", renderSlots);
  }

  if (dateInput) {
    dateInput.addEventListener("change", () => {
      syncDateSelection();
      renderSlots();
    });
  }

  quickDateButtons.forEach((button) => {
    button.addEventListener("click", () => {
      if (!dateInput) {
        return;
      }

      dateInput.value = button.dataset.quickDate || "";
      syncDateSelection();
      renderSlots();
    });
  });

  if (dateInput && doctorSelect) {
    if (!dateInput.value && quickDateButtons[0]?.dataset.quickDate) {
      dateInput.value = quickDateButtons[0].dataset.quickDate;
    }

    syncDateSelection();
    renderSlots();
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
