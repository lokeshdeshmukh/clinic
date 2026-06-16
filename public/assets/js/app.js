document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.body?.dataset.baseUrl || window.location.origin;

  const initScopedDrawer = () => {
    const drawer = document.querySelector("[data-drawer]");
    const toggle = document.querySelector("[data-drawer-toggle]");
    const closeButtons = document.querySelectorAll("[data-drawer-close]");

    if (!drawer || !toggle) {
      return;
    }

    const openDrawer = () => {
      drawer.hidden = false;
      drawer.classList.add("is-open");
      toggle.setAttribute("aria-expanded", "true");
      document.body.classList.add("has-drawer-open");
    };

    const closeDrawer = () => {
      drawer.classList.remove("is-open");
      drawer.hidden = true;
      toggle.setAttribute("aria-expanded", "false");
      document.body.classList.remove("has-drawer-open");
    };

    toggle.addEventListener("click", () => {
      if (drawer.hidden) {
        openDrawer();
      } else {
        closeDrawer();
      }
    });

    closeButtons.forEach((button) => {
      button.addEventListener("click", closeDrawer);
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !drawer.hidden) {
        closeDrawer();
      }
    });
  };

  const initBookingExperience = () => {
    const bookingExperience = document.querySelector("[data-booking-experience]");
    const bookingForm = document.querySelector("[data-booking-form]");
    const doctorSelect = document.querySelector("[data-slot-doctor]");
    const dateInput = document.querySelector("[data-slot-date]");
    const slotContainer = document.querySelector("[data-slot-results]");
    const slotInput = document.querySelector("[data-slot-input]");
    const bookingSubmitButton = document.querySelector("[data-booking-submit]");
    const quickDateButtons = Array.from(document.querySelectorAll("[data-quick-date]"));
    const dateStrip = document.querySelector("[data-date-strip]");
    const dateScrollButtons = Array.from(document.querySelectorAll("[data-date-scroll]"));
    const selectedDateLabel = document.querySelector("[data-selected-date-label]");
    const selectedDateLabelSecondary = document.querySelector("[data-selected-date-label-secondary]");
    const selectedTimeLabel = document.querySelector("[data-selected-time-label]");
    const selectedSlotLabel = document.querySelector("[data-selected-slot-label]");
    const dateStatus = document.querySelector("[data-date-status]");
    const authModal = document.querySelector("[data-auth-modal]");
    const authCloseButtons = Array.from(document.querySelectorAll("[data-auth-close]"));
    const authTabs = Array.from(document.querySelectorAll("[data-auth-tab]"));
    const authPanels = Array.from(document.querySelectorAll("[data-auth-panel]"));
    const authMessage = document.querySelector("[data-auth-message]");
    const authSlotCopy = document.querySelector("[data-auth-slot-copy]");
    const authVerifyCopy = document.querySelector("[data-auth-verify-copy]");
    const authChallengeToken = document.querySelector("[data-auth-challenge-token]");
    const authChannelInput = document.querySelector("[data-auth-channel-input]");
    const authSendForms = Array.from(document.querySelectorAll("[data-auth-send-form]"));
    const authVerifyForm = document.querySelector("[data-auth-verify-form]");
    const googleLoginForm = document.querySelector("[data-google-login-form]");
    const googleCredentialInput = document.querySelector("[data-google-credential]");

    if (!bookingExperience || !doctorSelect || !dateInput || !slotContainer || !slotInput || !bookingSubmitButton) {
      return;
    }

    let patientLoggedIn = bookingExperience.dataset.patientLoggedIn === "1";
    let selectedSlotValue = "";
    let selectedSlotText = "";
    let pendingSelection = null;
    let loadingAuth = false;
    let activeSlotRequest = 0;
    const searchWindowDays = 14;
    const clinicPhoneHref = bookingExperience.dataset.clinicPhoneHref || "";

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

    const addDays = (value, offset) => {
      const [year, month, day] = value.split("-").map(Number);
      if (!year || !month || !day) {
        return value;
      }

      const date = new Date(year, month - 1, day);
      date.setDate(date.getDate() + offset);
      const nextYear = date.getFullYear();
      const nextMonth = String(date.getMonth() + 1).padStart(2, "0");
      const nextDay = String(date.getDate()).padStart(2, "0");
      return `${nextYear}-${nextMonth}-${nextDay}`;
    };

    const escapeHtml = (value) => String(value)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#39;");

    const setDateStatus = (message, tone = "neutral") => {
      if (!dateStatus) {
        return;
      }

      dateStatus.textContent = message;
      dateStatus.dataset.tone = tone;
    };

    const setAuthMessage = (message, type = "info") => {
      if (!authMessage) {
        return;
      }

      if (!message) {
        authMessage.hidden = true;
        authMessage.textContent = "";
        authMessage.className = "booking-auth-modal__message";
        return;
      }

      authMessage.hidden = false;
      authMessage.textContent = message;
      authMessage.className = `booking-auth-modal__message is-${type}`;
    };

    const syncDateSelection = () => {
      quickDateButtons.forEach((button) => {
        button.classList.toggle("is-active", button.dataset.quickDate === dateInput.value);
      });

      const activeButton = quickDateButtons.find((button) => button.dataset.quickDate === dateInput.value);
      if (activeButton) {
        activeButton.scrollIntoView({
          behavior: "smooth",
          block: "nearest",
          inline: "center"
        });
      }

      const label = formatDateLabel(dateInput.value);
      if (selectedDateLabel) {
        selectedDateLabel.textContent = label;
      }
      if (selectedDateLabelSecondary) {
        selectedDateLabelSecondary.textContent = label;
      }
    };

    const clearSelection = () => {
      selectedSlotValue = "";
      selectedSlotText = "";
      slotInput.value = "";

      slotContainer.querySelectorAll("[data-slot-value]").forEach((button) => {
        button.classList.remove("is-active");
      });

      if (selectedTimeLabel) {
        selectedTimeLabel.textContent = "Pick time";
      }

      if (selectedSlotLabel) {
        selectedSlotLabel.textContent = "Tap a time";
      }
    };

    const updateBookingCta = () => {
      if (!bookingSubmitButton) {
        return;
      }

      if (patientLoggedIn) {
        bookingSubmitButton.type = "submit";
        bookingSubmitButton.textContent = "Confirm appointment";
        bookingSubmitButton.disabled = slotInput.value === "";
        return;
      }

      bookingSubmitButton.type = "button";
      bookingSubmitButton.disabled = false;
      bookingSubmitButton.textContent = pendingSelection ? "Continue to login" : "Pick a time to continue";
    };

    const applySelectedSlot = (value, label) => {
      selectedSlotValue = value;
      selectedSlotText = label;
      slotInput.value = value;

      slotContainer.querySelectorAll("[data-slot-value]").forEach((button) => {
        const isMatch = button.dataset.slotValue === value;
        button.classList.toggle("is-active", isMatch);
      });

      if (selectedTimeLabel) {
        selectedTimeLabel.textContent = label;
      }

      if (selectedSlotLabel) {
        selectedSlotLabel.textContent = label;
      }

      updateBookingCta();
    };

    const activateAuthPanel = (name) => {
      authTabs.forEach((button) => {
        button.classList.toggle("is-active", button.dataset.authTab === name);
      });

      authPanels.forEach((panel) => {
        panel.classList.toggle("is-active", panel.dataset.authPanel === name);
      });
    };

    const openAuthModal = () => {
      if (!authModal) {
        return;
      }

      authModal.hidden = false;
      authModal.classList.add("is-open");
      document.body.classList.add("has-auth-modal-open");
      setAuthMessage("");

      if (authSlotCopy) {
        authSlotCopy.textContent = pendingSelection
          ? `You chose ${pendingSelection.label}. Sign in here and we’ll keep that slot highlighted for you.`
          : "Select a slot first, then sign in here without leaving the page.";
      }
    };

    const closeAuthModal = () => {
      if (!authModal) {
        return;
      }

      authModal.classList.remove("is-open");
      authModal.hidden = true;
      document.body.classList.remove("has-auth-modal-open");
      setAuthMessage("");
      activateAuthPanel("email");

      if (authChallengeToken) {
        authChallengeToken.value = "";
      }
      if (authChannelInput) {
        authChannelInput.value = "";
      }
    };

    const showVerifyPanel = (challenge) => {
      if (authChallengeToken) {
        authChallengeToken.value = challenge.challenge_token || "";
      }

      if (authChannelInput) {
        authChannelInput.value = challenge.channel || "";
      }

      if (authVerifyCopy) {
        authVerifyCopy.textContent = `Enter the OTP we just sent to ${challenge.masked_destination}.`;
      }

      activateAuthPanel("verify");
      setAuthMessage("OTP sent. Enter it below to continue.", "success");
    };

    const postJson = async (path, payload) => {
      const response = await fetch(`${baseUrl}${path}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json"
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json().catch(() => ({
        ok: false,
        message: "Unexpected server response."
      }));

      if (!response.ok || result.ok === false) {
        throw new Error(result.message || "Something went wrong.");
      }

      return result;
    };

    const handleAuthSuccess = (patient) => {
      patientLoggedIn = true;
      bookingExperience.dataset.patientLoggedIn = "1";
      closeAuthModal();

      if (pendingSelection) {
        applySelectedSlot(pendingSelection.value, pendingSelection.label);
      }

      pendingSelection = null;
      updateBookingCta();

      if (patient?.email) {
        setTimeout(() => {
          setAuthMessage("");
        }, 0);
      }
    };

    const renderSlotFeedback = (message, tone = "info") => {
      const phoneAction = clinicPhoneHref
        ? `<a href="${escapeHtml(clinicPhoneHref)}" class="slot-feedback__link">Call clinic</a>`
        : "";

      slotContainer.innerHTML = `
        <div class="slot-feedback slot-feedback--${tone}">
          <p>${escapeHtml(message)}</p>
          ${phoneAction}
        </div>
      `;
    };

    const renderSlotButtons = (slots) => {
      slotContainer.innerHTML = slots.map((slot) => {
        const [startLabel, endLabel] = String(slot.label || "").split(" - ");
        return `
          <button type="button" data-slot-value="${escapeHtml(slot.start_time)}" data-slot-label="${escapeHtml(slot.label)}" class="slot-chip">
            <strong class="slot-chip__time">${escapeHtml(startLabel || slot.label)}</strong>
            <span class="slot-chip__range">${escapeHtml(endLabel || "Available")}</span>
          </button>
        `;
      }).join("");
    };

    const fetchSlotsForDate = async (doctorId, date) => {
      try {
        const response = await fetch(`${baseUrl}/api/v1/doctors/${doctorId}/slots?date=${encodeURIComponent(date)}`, {
          headers: {
            Accept: "application/json"
          }
        });
        const payload = await response.json().catch(() => null);

        if (!response.ok || !payload || !Array.isArray(payload.data)) {
          return {
            error: true,
            message: payload?.message || "Unable to load slots right now.",
            slots: []
          };
        }

        return {
          error: false,
          message: "",
          slots: payload.data
        };
      } catch (error) {
        return {
          error: true,
          message: "Unable to load slots right now.",
          slots: []
        };
      }
    };

    const findNextAvailableDate = async (doctorId, startDate, requestId) => {
      for (let offset = 1; offset < searchWindowDays; offset += 1) {
        if (requestId !== activeSlotRequest) {
          return null;
        }

        const candidate = addDays(startDate, offset);
        const result = await fetchSlotsForDate(doctorId, candidate);

        if (requestId !== activeSlotRequest) {
          return null;
        }

        if (!result.error && result.slots.length > 0) {
          return {
            date: candidate,
            slots: result.slots
          };
        }
      }

      return null;
    };

    const renderSlots = async ({ allowAutoAdvance = false } = {}) => {
      const doctorId = doctorSelect.value;
      const date = dateInput.value;
      const requestId = activeSlotRequest + 1;
      activeSlotRequest = requestId;

      slotContainer.innerHTML = '<div class="slot-feedback"><p>Loading live slots...</p></div>';
      pendingSelection = null;
      clearSelection();

      if (!doctorId || !date) {
        syncDateSelection();
        setDateStatus("Choose a day", "neutral");
        updateBookingCta();
        return;
      }

      setDateStatus("Checking availability", "neutral");

      const result = await fetchSlotsForDate(doctorId, date);
      if (requestId !== activeSlotRequest) {
        return;
      }

      if (result.error) {
        renderSlotFeedback(result.message, "error");
        setDateStatus("Unable to load slots", "error");
        updateBookingCta();
        return;
      }

      if (result.slots.length === 0 && allowAutoAdvance) {
        setDateStatus("Finding the next open day", "neutral");
        const fallback = await findNextAvailableDate(doctorId, date, requestId);

        if (requestId !== activeSlotRequest) {
          return;
        }

        if (fallback) {
          dateInput.value = fallback.date;
          syncDateSelection();
          renderSlotButtons(fallback.slots);
          setDateStatus(`Next open day: ${formatDateLabel(fallback.date)}`, "success");
          updateBookingCta();
          return;
        }
      }

      if (result.slots.length === 0) {
        renderSlotFeedback(`No open slots on ${formatDateLabel(date)}. Try another day${clinicPhoneHref ? " or call the clinic." : "."}`);
        setDateStatus("No slots on selected day", "warning");
        updateBookingCta();
        return;
      }

      renderSlotButtons(result.slots);
      setDateStatus(`Open on ${formatDateLabel(dateInput.value)}`, "success");
      updateBookingCta();
    };

    slotContainer.addEventListener("click", (event) => {
      if (!(event.target instanceof Element)) {
        return;
      }

      const button = event.target.closest("[data-slot-value]");
      if (!button) {
        return;
      }

      const value = button.dataset.slotValue || "";
      const label = button.dataset.slotLabel || "Selected slot";

      if (!patientLoggedIn) {
        pendingSelection = { value, label };
        openAuthModal();
        updateBookingCta();
        return;
      }

      applySelectedSlot(value, label);
    });

    if (bookingForm) {
      bookingForm.addEventListener("submit", (event) => {
        if (!patientLoggedIn) {
          event.preventDefault();

          if (!pendingSelection && selectedSlotValue) {
            pendingSelection = { value: selectedSlotValue, label: selectedSlotText };
          }

          openAuthModal();
          return;
        }

        if (!slotInput.value) {
          event.preventDefault();
          if (selectedSlotLabel) {
            selectedSlotLabel.textContent = "Choose a time first";
          }
          slotContainer.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      });
    }

    bookingSubmitButton.addEventListener("click", (event) => {
      if (patientLoggedIn) {
        return;
      }

      event.preventDefault();

      if (!pendingSelection && !selectedSlotValue) {
        if (selectedSlotLabel) {
          selectedSlotLabel.textContent = "Choose a time first";
        }
        slotContainer.scrollIntoView({ behavior: "smooth", block: "center" });
        return;
      }

      if (!pendingSelection && selectedSlotValue) {
        pendingSelection = { value: selectedSlotValue, label: selectedSlotText };
      }

      openAuthModal();
    });

    authTabs.forEach((button) => {
      button.addEventListener("click", () => {
        activateAuthPanel(button.dataset.authTab || "email");
        setAuthMessage("");
      });
    });

    authCloseButtons.forEach((button) => {
      button.addEventListener("click", closeAuthModal);
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && authModal && !authModal.hidden) {
        closeAuthModal();
      }
    });

    authSendForms.forEach((form) => {
      form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (loadingAuth) {
          return;
        }

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        payload.channel = form.dataset.authChannel || "email";

        try {
          loadingAuth = true;
          setAuthMessage("Sending OTP...", "info");
          const result = await postJson("/patient/login/otp/send", payload);
          showVerifyPanel(result.challenge);
        } catch (error) {
          setAuthMessage(error.message, "error");
        } finally {
          loadingAuth = false;
        }
      });
    });

    if (authVerifyForm) {
      authVerifyForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (loadingAuth) {
          return;
        }

        const payload = Object.fromEntries(new FormData(authVerifyForm).entries());

        try {
          loadingAuth = true;
          setAuthMessage("Verifying OTP...", "info");
          const result = await postJson("/patient/login/otp/verify", payload);
          handleAuthSuccess(result.patient);
        } catch (error) {
          setAuthMessage(error.message, "error");
        } finally {
          loadingAuth = false;
        }
      });
    }

    window.handleGooglePatientSignIn = async (response) => {
      if (!response?.credential) {
        setAuthMessage("Google sign-in did not return a valid credential.", "error");
        return;
      }

      if (googleLoginForm && googleCredentialInput && googleLoginForm.dataset.googleAjax === "true") {
        const payload = Object.fromEntries(new FormData(googleLoginForm).entries());
        payload.credential = response.credential;

        try {
          loadingAuth = true;
          setAuthMessage("Signing you in with Google...", "info");
          const result = await postJson("/patient/login/google", payload);
          handleAuthSuccess(result.patient);
        } catch (error) {
          setAuthMessage(error.message, "error");
        } finally {
          loadingAuth = false;
        }

        return;
      }

      const fallbackGoogleForm = document.querySelector("[data-google-login-form]");
      const fallbackCredentialInput = document.querySelector("[data-google-credential]");

      if (fallbackGoogleForm && fallbackCredentialInput) {
        fallbackCredentialInput.value = response.credential;
        fallbackGoogleForm.submit();
      }
    };

    if (dateInput) {
      dateInput.addEventListener("change", () => {
        syncDateSelection();
        renderSlots();
      });
    }

    quickDateButtons.forEach((button) => {
      button.addEventListener("click", () => {
        dateInput.value = button.dataset.quickDate || "";
        syncDateSelection();
        renderSlots();
      });
    });

    dateScrollButtons.forEach((button) => {
      button.addEventListener("click", () => {
        if (!dateStrip) {
          return;
        }

        const direction = button.dataset.dateScroll === "prev" ? -1 : 1;
        const amount = Math.max(220, Math.round(dateStrip.clientWidth * 0.72));
        dateStrip.scrollBy({
          left: amount * direction,
          behavior: "smooth"
        });
      });
    });

    syncDateSelection();
    updateBookingCta();
    renderSlots({ allowAutoAdvance: true });
  };

  const initStandaloneGoogleForm = () => {
    if (document.querySelector("[data-booking-experience]")) {
      return;
    }

    const googleLoginForm = document.querySelector("[data-google-login-form]");
    const googleCredentialInput = document.querySelector("[data-google-credential]");
    if (!googleLoginForm || !googleCredentialInput) {
      return;
    }

    window.handleGooglePatientSignIn = (response) => {
      if (!response?.credential) {
        return;
      }

      googleCredentialInput.value = response.credential;
      googleLoginForm.submit();
    };
  };

  const initCalendar = () => {
    const calendarElement = document.querySelector("[data-calendar]");
    if (!calendarElement || !window.FullCalendar) {
      return;
    }

    const events = JSON.parse(calendarElement.dataset.events || "[]");
    const calendar = new window.FullCalendar.Calendar(calendarElement, {
      initialView: "dayGridMonth",
      height: 640,
      events
    });
    calendar.render();
  };

  const initCharts = () => {
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
  };

  initScopedDrawer();
  initBookingExperience();
  initStandaloneGoogleForm();
  initCalendar();
  initCharts();
});
