import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

document.addEventListener("alpine:init", () => {
    Alpine.store("sidebar", {
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    });
});

Alpine.start();

function initActionLoadingIndicators() {
    if (window.__actionLoadingIndicatorsInitialized) return;
    window.__actionLoadingIndicatorsInitialized = true;

    const lottieModuleUrl =
        "https://unpkg.com/@lottiefiles/dotlottie-wc@latest/dist/dotlottie-wc.js";
    const defaultLottieSrc =
        window.SIPASTI_LOADING_LOTTIE_SRC ||
        document.documentElement.dataset.loadingLottieSrc ||
        "https://assets6.lottiefiles.com/packages/lf20_usmfx6bp.json";
    let lottieLoadStarted = false;

    const hasLottieSource = () =>
        typeof defaultLottieSrc === "string" && defaultLottieSrc.trim() !== "";

    const loadLottiePlayer = () => {
        if (!hasLottieSource() || lottieLoadStarted) return;
        lottieLoadStarted = true;

        if (customElements.get("dotlottie-wc")) {
            document.documentElement.classList.add("sipasti-lottie-ready");
            return;
        }

        const script = document.createElement("script");
        script.type = "module";
        script.src = lottieModuleUrl;
        script.addEventListener("load", () => {
            document.documentElement.classList.add("sipasti-lottie-ready");
        });
        document.head.appendChild(script);
    };

    const submitButtonSelector =
        'button[type="submit"], input[type="submit"], button:not([type])';

    const isLoading = (element) =>
        element?.dataset?.loadingState === "loading";

    const isSkippable = (element) =>
        element?.dataset?.loadingSkip === "true" ||
        element?.closest?.("[data-loading-skip='true']");

    const getLoadingText = (element) =>
        element?.dataset?.loadingText ||
        element?.getAttribute?.("aria-label") ||
        "Memproses...";

    const escapeHtml = (value) =>
        String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    const lottieMarkup = () => {
        if (!hasLottieSource()) return "";

        return `
            <dotlottie-wc
                class="sipasti-loading-lottie"
                src="${escapeHtml(defaultLottieSrc)}"
                autoplay
                loop
                mode="normal"
            ></dotlottie-wc>
        `;
    };

    const activateLottieFallback = (element) => {
        const player = element.querySelector(".sipasti-loading-lottie");
        const mark = element.querySelector(".sipasti-loading-mark");
        if (!player || !mark) return;

        player.addEventListener("load", () => {
            mark.classList.add("sipasti-lottie-loaded");
        });
        player.addEventListener("error", () => {
            mark.classList.remove("sipasti-lottie-loaded");
        });
    };

    const loadingMarkup = (text) => `
        <span class="sipasti-loading-content">
            <span class="sipasti-loading-mark" aria-hidden="true">
                <span class="sipasti-loading-spinner"></span>
                ${lottieMarkup()}
            </span>
            <span class="sipasti-loading-text">${escapeHtml(text)}</span>
        </span>
    `;

    const modalLoadingMarkup = (text) => `
        <div class="sipasti-modal-loading-box">
            <span class="sipasti-loading-mark sipasti-modal-loading-mark" aria-hidden="true">
                <span class="sipasti-loading-spinner"></span>
                ${lottieMarkup()}
            </span>
            <span class="sipasti-modal-loading-text">${escapeHtml(text)}</span>
        </div>
    `;

    const classContains = (element, pattern) =>
        Array.from(element.classList || []).some((className) =>
            className.includes(pattern),
        );

    const looksLikeModalPanel = (element) => {
        if (!(element instanceof HTMLElement)) return false;
        if (element.dataset.loadingModalPanel === "true") return true;
        if (element.matches("[role='dialog'], [aria-modal='true']"))
            return true;

        const hasSurface =
            classContains(element, "bg-white") ||
            classContains(element, "bg-gray") ||
            classContains(element, "bg-slate");
        const hasFrame =
            classContains(element, "rounded") ||
            classContains(element, "shadow") ||
            classContains(element, "border");

        return hasSurface && hasFrame;
    };

    const isFixedModalRoot = (element) =>
        element instanceof HTMLElement &&
        classContains(element, "fixed") &&
        classContains(element, "inset-0");

    const findModalPanel = (element) => {
        if (!(element instanceof HTMLElement)) return null;

        let current = element.parentElement;
        let panelCandidate = null;

        while (current && current !== document.body) {
            if (looksLikeModalPanel(current)) {
                panelCandidate = current;
            }

            if (isFixedModalRoot(current)) {
                return panelCandidate || current;
            }

            current = current.parentElement;
        }

        return null;
    };

    const setModalLoading = (modalPanel, text = "Memproses...") => {
        if (!(modalPanel instanceof HTMLElement)) return;
        if (modalPanel.dataset.loadingModalState === "loading") return;
        if (isSkippable(modalPanel)) return;

        loadLottiePlayer();

        const computedStyle = window.getComputedStyle(modalPanel);
        modalPanel.dataset.loadingModalState = "loading";
        modalPanel.dataset.loadingOriginalPosition =
            modalPanel.style.position || "";
        modalPanel.dataset.loadingOriginalAriaBusy =
            modalPanel.getAttribute("aria-busy") || "";

        if (computedStyle.position === "static") {
            modalPanel.style.position = "relative";
        }

        modalPanel.setAttribute("aria-busy", "true");
        modalPanel.classList.add("sipasti-modal-loading-host");

        const overlay = document.createElement("div");
        overlay.className = "sipasti-modal-loading-overlay";
        overlay.setAttribute("data-loading-modal-overlay", "true");
        overlay.setAttribute("role", "status");
        overlay.setAttribute("aria-live", "polite");
        overlay.innerHTML = modalLoadingMarkup(text);
        modalPanel.appendChild(overlay);
        activateLottieFallback(overlay);
    };

    const restoreModalLoading = (modalPanel) => {
        if (
            !(modalPanel instanceof HTMLElement) ||
            modalPanel.dataset.loadingModalState !== "loading"
        ) {
            return;
        }

        modalPanel
            .querySelectorAll("[data-loading-modal-overlay='true']")
            .forEach((overlay) => overlay.remove());

        modalPanel.classList.remove("sipasti-modal-loading-host");
        modalPanel.style.position =
            modalPanel.dataset.loadingOriginalPosition || "";

        if (modalPanel.dataset.loadingOriginalAriaBusy) {
            modalPanel.setAttribute(
                "aria-busy",
                modalPanel.dataset.loadingOriginalAriaBusy,
            );
        } else {
            modalPanel.removeAttribute("aria-busy");
        }

        delete modalPanel.dataset.loadingModalState;
        delete modalPanel.dataset.loadingOriginalPosition;
        delete modalPanel.dataset.loadingOriginalAriaBusy;
    };

    const setElementLoading = (element, text = null, options = {}) => {
        if (!(element instanceof HTMLElement)) return;
        if (isSkippable(element) || isLoading(element)) return;

        loadLottiePlayer();

        const loadingText = text || getLoadingText(element);
        const shouldDisable = options.disable !== false;

        element.dataset.loadingState = "loading";
        element.dataset.loadingOriginalHtml = element.innerHTML;
        element.dataset.loadingOriginalValue =
            element instanceof HTMLInputElement ? element.value : "";
        element.dataset.loadingOriginalMinWidth = element.style.minWidth || "";
        element.dataset.loadingOriginalDisabled = element.disabled
            ? "true"
            : "false";
        element.dataset.loadingOriginalAriaBusy =
            element.getAttribute("aria-busy") || "";

        element.classList.add("sipasti-loading-button");
        element.setAttribute("aria-busy", "true");
        if (element.offsetWidth > 0) {
            element.style.minWidth = `${Math.ceil(element.offsetWidth)}px`;
        }

        if (element instanceof HTMLInputElement) {
            element.value = loadingText;
        } else {
            element.innerHTML = loadingMarkup(loadingText);
            activateLottieFallback(element);
        }

        if (shouldDisable && "disabled" in element) {
            element.disabled = true;
        } else {
            element.setAttribute("aria-disabled", "true");
        }
    };

    const restoreElementLoading = (element) => {
        if (!(element instanceof HTMLElement) || !isLoading(element)) return;

        if (element instanceof HTMLInputElement) {
            element.value = element.dataset.loadingOriginalValue || "";
        } else {
            element.innerHTML = element.dataset.loadingOriginalHtml || "";
        }
        element.classList.remove("sipasti-loading-button");
        element.style.minWidth = element.dataset.loadingOriginalMinWidth || "";

        if (element.dataset.loadingOriginalAriaBusy) {
            element.setAttribute(
                "aria-busy",
                element.dataset.loadingOriginalAriaBusy,
            );
        } else {
            element.removeAttribute("aria-busy");
        }

        if ("disabled" in element) {
            element.disabled =
                element.dataset.loadingOriginalDisabled === "true";
        } else {
            element.removeAttribute("aria-disabled");
        }

        delete element.dataset.loadingState;
        delete element.dataset.loadingOriginalHtml;
        delete element.dataset.loadingOriginalValue;
        delete element.dataset.loadingOriginalMinWidth;
        delete element.dataset.loadingOriginalDisabled;
        delete element.dataset.loadingOriginalAriaBusy;
    };

    const hasPreventedAlpineSubmit = (form) =>
        Array.from(form.attributes).some((attribute) =>
            attribute.name.toLowerCase().includes("submit.prevent"),
        );

    const findSubmitter = (form, explicitSubmitter = null) => {
        if (
            explicitSubmitter instanceof HTMLElement &&
            explicitSubmitter.form === form
        ) {
            return explicitSubmitter;
        }

        const active = document.activeElement;
        if (active instanceof HTMLElement && active.form === form) {
            return active;
        }

        return form.querySelector(submitButtonSelector);
    };

    const markFormLoading = (form, submitter = null) => {
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.loadingState === "loading") return;
        if (isSkippable(form)) return;

        const button = findSubmitter(form, submitter);
        const modalPanel = findModalPanel(form);
        form.dataset.loadingState = "loading";

        if (modalPanel) {
            form.dataset.loadingMode = "modal";
            setModalLoading(modalPanel, button ? getLoadingText(button) : "Memproses...");
        } else if (button) {
            form.dataset.loadingMode = "button";
            setElementLoading(button, null, { disable: false });

            form.querySelectorAll(submitButtonSelector).forEach((submitButton) => {
                if (submitButton !== button && !submitButton.disabled) {
                    submitButton.dataset.loadingDisabledByForm = "true";
                    submitButton.disabled = true;
                }
            });
        }
    };

    const restoreFormLoading = (form) => {
        if (!(form instanceof HTMLFormElement)) return;

        if (form.dataset.loadingMode === "modal") {
            restoreModalLoading(findModalPanel(form));
        }

        form.querySelectorAll("[data-loading-state='loading']").forEach(
            restoreElementLoading,
        );
        form.querySelectorAll("[data-loading-disabled-by-form='true']").forEach(
            (button) => {
                button.disabled = false;
                delete button.dataset.loadingDisabledByForm;
            },
        );

        delete form.dataset.loadingState;
        delete form.dataset.loadingMode;
    };

    document.addEventListener(
        "submit",
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (hasPreventedAlpineSubmit(form)) return;

            markFormLoading(form, event.submitter);
        },
        true,
    );

    const nativeSubmit = HTMLFormElement.prototype.submit;
    if (!HTMLFormElement.prototype.__sipastiLoadingSubmitPatched) {
        HTMLFormElement.prototype.submit = function submitWithLoading() {
            markFormLoading(this);
            nativeSubmit.call(this);
        };
        HTMLFormElement.prototype.__sipastiLoadingSubmitPatched = true;
    }

    document.addEventListener(
        "click",
        (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;

            const button = target.closest("[data-loading-on-click='true']");
            if (!(button instanceof HTMLElement)) return;
            if (button.closest("form") && button.type === "submit") return;

            const modalPanel = findModalPanel(button);
            if (modalPanel) {
                setModalLoading(modalPanel, getLoadingText(button));
            } else {
                setElementLoading(button);
            }
        },
        true,
    );

    window.addEventListener("pageshow", () => {
        document
            .querySelectorAll("[data-loading-state='loading']")
            .forEach(restoreElementLoading);
        document
            .querySelectorAll("form[data-loading-state='loading']")
            .forEach(restoreFormLoading);
        document
            .querySelectorAll("[data-loading-modal-state='loading']")
            .forEach(restoreModalLoading);
    });

    window.SiPastiLoading = {
        set: setElementLoading,
        restore: restoreElementLoading,
        markForm: markFormLoading,
        restoreForm: restoreFormLoading,
        setModal: setModalLoading,
        restoreModal: restoreModalLoading,
    };
}

function initCriticalActionConfirmations() {
    if (window.__criticalActionConfirmationsInitialized) return;
    window.__criticalActionConfirmationsInitialized = true;

    let pendingForm = null;
    let pendingSubmitter = null;

    const modal = document.createElement("div");
    modal.id = "critical-action-confirmation-modal";
    modal.className =
        "fixed inset-0 z-[9999] hidden items-center justify-center p-4";
    modal.innerHTML = `
        <div data-confirm-backdrop class="absolute inset-0 bg-black/45 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-[#01458E] to-[#0A63C7]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 data-confirm-title class="text-base font-bold text-white">Konfirmasi Aksi</h3>
                        <p data-confirm-subtitle class="text-xs text-blue-100 mt-0.5">Pastikan data sudah benar sebelum dilanjutkan.</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5">
                <p data-confirm-message class="text-sm leading-relaxed text-gray-600"></p>
                <div class="mt-5 rounded-xl bg-amber-50 border border-amber-100 px-4 py-3">
                    <p class="text-xs leading-relaxed text-amber-700">Aksi ini akan langsung diproses setelah dikonfirmasi.</p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" data-confirm-cancel class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                    Batal
                </button>
                <button type="button" data-confirm-ok data-loading-on-click="true" data-loading-text="Memproses..." class="px-4 py-2.5 rounded-xl bg-[#01458E] text-sm font-semibold text-white hover:bg-[#013B7A] transition-colors">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    const titleEl = modal.querySelector("[data-confirm-title]");
    const subtitleEl = modal.querySelector("[data-confirm-subtitle]");
    const messageEl = modal.querySelector("[data-confirm-message]");
    const okButton = modal.querySelector("[data-confirm-ok]");
    const cancelButton = modal.querySelector("[data-confirm-cancel]");
    const backdrop = modal.querySelector("[data-confirm-backdrop]");

    const close = () => {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        pendingForm = null;
        pendingSubmitter = null;
    };

    const open = (form, submitter, config) => {
        pendingForm = form;
        pendingSubmitter = submitter;
        titleEl.textContent = config.title;
        subtitleEl.textContent = config.subtitle;
        messageEl.textContent = config.message;
        okButton.textContent = config.okText;
        okButton.className = config.danger
            ? "px-4 py-2.5 rounded-xl bg-red-600 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
            : "px-4 py-2.5 rounded-xl bg-[#01458E] text-sm font-semibold text-white hover:bg-[#013B7A] transition-colors";
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    };

    const formMethod = (form) => {
        const spoofed = form.querySelector('input[name="_method"]')?.value;
        return (spoofed || form.getAttribute("method") || "GET").toUpperCase();
    };

    const confirmationConfig = (form) => {
        if (form.dataset.confirmSkip === "true") return null;
        if (form.dataset.confirmTitle || form.dataset.confirmMessage) {
            return {
                title: form.dataset.confirmTitle || "Konfirmasi Aksi",
                subtitle:
                    form.dataset.confirmSubtitle ||
                    "Pastikan data sudah benar sebelum dilanjutkan.",
                message:
                    form.dataset.confirmMessage ||
                    "Anda yakin ingin melanjutkan aksi ini?",
                okText: form.dataset.confirmOk || "Ya, Lanjutkan",
                danger: form.dataset.confirmDanger === "true",
            };
        }

        const method = formMethod(form);
        const action = (form.getAttribute("action") || "").toLowerCase();

        return null;
    };

    document.addEventListener(
        "submit",
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.dataset.confirmed === "true") {
                delete form.dataset.confirmed;
                return;
            }

            const config = confirmationConfig(form);
            if (!config) return;

            event.preventDefault();
            event.stopImmediatePropagation();
            open(form, event.submitter || document.activeElement, config);
        },
        true,
    );

    okButton.addEventListener("click", () => {
        if (!pendingForm) return close();
        const form = pendingForm;
        const submitter =
            pendingSubmitter instanceof HTMLElement ? pendingSubmitter : null;
        form.dataset.confirmed = "true";
        close();

        if (
            typeof form.requestSubmit === "function" &&
            submitter?.type === "submit"
        ) {
            form.requestSubmit(submitter);
        } else if (typeof form.requestSubmit === "function") {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });

    cancelButton.addEventListener("click", close);
    backdrop.addEventListener("click", close);
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !modal.classList.contains("hidden"))
            close();
    });
}

if (document.readyState === "loading") {
    document.addEventListener(
        "DOMContentLoaded",
        initCriticalActionConfirmations,
    );
    document.addEventListener("DOMContentLoaded", initActionLoadingIndicators);
} else {
    initCriticalActionConfirmations();
    initActionLoadingIndicators();
}

window.initChatUnreadBadges = function initChatUnreadBadges(
    currentUserId = null,
) {
    if (!window.Echo || window.__chatUnreadBadgesInitialized) return;

    const buttons = Array.from(
        document.querySelectorAll(
            "[data-chat-unread-button][data-chat-room-id]",
        ),
    ).filter((button) => button.dataset.chatRoomId);

    if (!buttons.length) return;

    window.__chatUnreadBadgesInitialized = true;

    const badgeClass =
        "absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full text-[9px] font-bold text-white flex items-center justify-center leading-none border-2 border-white";

    const getBadge = (button) => {
        let badge = button.querySelector("[data-chat-unread-badge]");

        if (!badge) {
            badge = button.querySelector(
                'span.absolute, span[style*="position:absolute"]',
            );
            if (badge) {
                badge.setAttribute("data-chat-unread-badge", "true");
            }
        }

        if (!badge) {
            badge = document.createElement("span");
            badge.setAttribute("data-chat-unread-badge", "true");
            badge.className = badgeClass;
            badge.style.background = "#DC2626";
            button.appendChild(badge);
        }

        return badge;
    };

    const setBadgeCount = (button, count) => {
        const badge = getBadge(button);
        badge.textContent = count > 9 ? "9+" : String(count);
        badge.dataset.chatUnreadCount = String(count);
        badge.style.display = count > 0 ? "flex" : "none";
    };

    const roomIds = [
        ...new Set(
            buttons.map((button) => button.dataset.chatRoomId).filter(Boolean),
        ),
    ];

    roomIds.forEach((roomId) => {
        window.Echo.private("chat." + roomId).listen(
            ".NewChatMessage",
            (event) => {
                if (
                    currentUserId &&
                    String(event.sender_id) === String(currentUserId)
                )
                    return;

                document
                    .querySelectorAll(
                        `[data-chat-unread-button][data-chat-room-id="${CSS.escape(roomId)}"]`,
                    )
                    .forEach((button) => {
                        const existingBadge = getBadge(button);
                        const currentCount =
                            parseInt(
                                existingBadge.dataset.chatUnreadCount ||
                                    existingBadge.textContent ||
                                    "0",
                                10,
                            ) || 0;
                        setBadgeCount(button, currentCount + 1);
                    });
            },
        );
    });
};
