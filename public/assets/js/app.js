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
    const selectedDateLabel = document.querySelector("[data-selected-date-label]");
    const selectedDateLabelSecondary = document.querySelector("[data-selected-date-label-secondary]");
    const selectedTimeLabel = document.querySelector("[data-selected-time-label]");
    const selectedSlotLabel = document.querySelector("[data-selected-slot-label]");
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
        selectedTimeLabel.textContent = "Select time";
      }

      if (selectedSlotLabel) {
        selectedSlotLabel.textContent = "Tap a slot";
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
      bookingSubmitButton.textContent = pendingSelection ? "Continue to login" : "Select a slot to continue";
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

    const renderSlots = async () => {
      const doctorId = doctorSelect.value;
      const date = dateInput.value;

      slotContainer.innerHTML = '<p class="slot-feedback">Loading live slots...</p>';
      pendingSelection = null;
      clearSelection();

      if (!doctorId || !date) {
        syncDateSelection();
        updateBookingCta();
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
          updateBookingCta();
          return;
        }

        if (payload.data.length === 0) {
          slotContainer.innerHTML = '<p class="slot-feedback">No open slots for that date.</p>';
          updateBookingCta();
          return;
        }

        slotContainer.innerHTML = payload.data.map((slot) => `
          <button type="button" data-slot-value="${slot.start_time}" data-slot-label="${slot.label}" class="slot-chip">
            ${slot.label}
          </button>
        `).join("");

        if (selectedSlotValue) {
          const selectedButton = slotContainer.querySelector(`[data-slot-value="${selectedSlotValue}"]`);
          if (selectedButton) {
            applySelectedSlot(selectedSlotValue, selectedSlotText);
          }
        }
      } catch (error) {
        slotContainer.innerHTML = '<p class="slot-feedback slot-feedback--error">Unable to load slots right now.</p>';
      }

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
            selectedSlotLabel.textContent = "Choose a slot first";
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
          selectedSlotLabel.textContent = "Choose a slot first";
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

    syncDateSelection();
    updateBookingCta();
    renderSlots();
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
