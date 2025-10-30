// Invoice Management System - Main JavaScript
document.addEventListener("DOMContentLoaded", function () {
    // Initialize all interactions
    initSidebarToggle();
    initDropdowns();
    initModals();
    initTooltips();
    initFormValidation();
    initAutoDismiss();
});

// Sidebar toggle for mobile
function initSidebarToggle() {
    const toggleBtn = document.getElementById("sidebar-toggle");
    const sidebar = document.getElementById("sidebar");

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("hidden");
            sidebar.classList.toggle("fixed");
            sidebar.classList.toggle("z-40");
        });
    }
}

// Dropdown menus
function initDropdowns() {
    const dropdowns = document.querySelectorAll(".dropdown-toggle");

    dropdowns.forEach((dropdown) => {
        dropdown.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const menu = this.nextElementSibling;
            const isOpen = menu.classList.contains("hidden");

            // Close all dropdowns
            document.querySelectorAll(".dropdown-menu").forEach((m) => {
                m.classList.add("hidden");
            });

            // Toggle current dropdown
            if (isOpen) {
                menu.classList.remove("hidden");
            } else {
                menu.classList.add("hidden");
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener("click", function () {
        document.querySelectorAll(".dropdown-menu").forEach((menu) => {
            menu.classList.add("hidden");
        });
    });
}

// Modal management
function initModals() {
    // Modal triggers
    document.querySelectorAll("[data-modal-target]").forEach((trigger) => {
        trigger.addEventListener("click", function (e) {
            e.preventDefault();
            const modalId = this.getAttribute("data-modal-target");
            openModal(modalId);
        });
    });

    // Modal close buttons
    document.querySelectorAll(".modal-close").forEach((closeBtn) => {
        closeBtn.addEventListener("click", function () {
            const modal = this.closest(".modal-backdrop");
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
}

// Tooltips
function initTooltips() {
    const tooltips = document.querySelectorAll("[data-tooltip]");

    tooltips.forEach((element) => {
        element.addEventListener("mouseenter", function () {
            const tooltip = document.createElement("div");
            tooltip.className =
                "absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg";
            tooltip.textContent = this.getAttribute("data-tooltip");
            tooltip.id = "tooltip-" + Math.random().toString(36).substr(2, 9);

            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px";
            tooltip.style.left =
                rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";

            this.setAttribute("data-tooltip-id", tooltip.id);
        });

        element.addEventListener("mouseleave", function () {
            const tooltipId = this.getAttribute("data-tooltip-id");
            if (tooltipId) {
                const tooltip = document.getElementById(tooltipId);
                if (tooltip) {
                    tooltip.remove();
                }
                this.removeAttribute("data-tooltip-id");
            }
        });
    });
}

// Form validation feedback
function initFormValidation() {
    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {
        const inputs = form.querySelectorAll("input, textarea, select");

        inputs.forEach((input) => {
            input.addEventListener("blur", function () {
                validateField(this);
            });

            input.addEventListener("input", function () {
                if (this.classList.contains("border-red-500")) {
                    validateField(this);
                }
            });
        });
    });
}

function validateField(field) {
    const isValid = field.checkValidity();
    const errorMsg = field.parentElement.querySelector(".text-red-600");

    if (isValid) {
        field.classList.remove("border-red-500");
        field.classList.add("border-gray-300");
        if (errorMsg) {
            errorMsg.style.display = "none";
        }
    } else {
        field.classList.add("border-red-500");
        field.classList.remove("border-gray-300");
        if (errorMsg) {
            errorMsg.style.display = "block";
        }
    }
}

// Auto-dismiss elements (alerts, notifications)
function initAutoDismiss() {
    const autoDismissElements = document.querySelectorAll(
        "[data-auto-dismiss]",
    );

    autoDismissElements.forEach((element) => {
        const delay =
            parseInt(element.getAttribute("data-auto-dismiss")) || 5000;

        setTimeout(function () {
            element.style.opacity = "0";
            element.style.transform = "translateY(-10px)";

            setTimeout(function () {
                element.remove();
            }, 300);
        }, delay);
    });
}

// Global functions (accessible from blade templates)
window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.style.overflow = "hidden";

        // Focus on first input
        const firstInput = modal.querySelector(
            "input, textarea, select, button",
        );
        if (firstInput) {
            firstInput.focus();
        }
    }
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.body.style.overflow = "auto";
    }
};

window.dismissAlert = function (alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.style.opacity = "0";
        alert.style.transform = "scale(0.95)";
        setTimeout(() => {
            alert.remove();
        }, 200);
    }
};

// Loading states for forms
function showLoadingState(button, originalText) {
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Loading...
    `;
    button.setAttribute("data-original-text", originalText);
}

function hideLoadingState(button) {
    const originalText = button.getAttribute("data-original-text");
    button.disabled = false;
    button.innerHTML = originalText;
    button.removeAttribute("data-original-text");
}

// Confirmation dialogs
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Copy to clipboard
function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(function () {
        const originalText = button.innerHTML;
        button.innerHTML = "Copied!";
        button.classList.add("text-green-600");

        setTimeout(function () {
            button.innerHTML = originalText;
            button.classList.remove("text-green-600");
        }, 2000);
    });
}

// Searchable Select Component
function initSearchableSelects() {
    // Find all item select elements that should be searchable
    const itemSelects = document.querySelectorAll("select.searchable-select");

    console.log("🔍 Searching for searchable selects...");
    console.log("Found:", itemSelects.length, "selects");

    itemSelects.forEach((select) => {
        console.log("Processing select:", select.name, select.id);
        if (select.classList.contains("searchable-select-initialized")) {
            console.log("Already initialized, skipping:", select.name);
            return;
        }

        createSearchableSelect(select);
    });
}

function createSearchableSelect(selectElement) {
    console.log(
        "Creating searchable select for:",
        selectElement.name || selectElement.id,
    );
    selectElement.classList.add("searchable-select-initialized");

    // Create wrapper
    const wrapper = document.createElement("div");
    wrapper.className = "relative w-full searchable-select-wrapper";

    console.log("Wrapper created:", wrapper);

    // Create dropdown
    const dropdown = document.createElement("div");
    dropdown.className =
        "hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg searchable-select-dropdown overflow-hidden";
    dropdown.style.top = "100%";
    dropdown.style.left = "0";

    // Create search input container (inside dropdown)
    const searchContainer = document.createElement("div");
    searchContainer.className = "p-3 border-b border-gray-200 bg-gray-50";

    // Create search input
    const searchInput = document.createElement("input");
    searchInput.type = "text";
    searchInput.className =
        "w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white searchable-select-input";
    searchInput.placeholder = "Search...";
    searchInput.setAttribute("autocomplete", "off");

    searchContainer.appendChild(searchInput);
    dropdown.appendChild(searchContainer);

    // Create options container
    const optionsContainer = document.createElement("div");
    optionsContainer.className = "max-h-60 overflow-y-auto bg-white";

    // Create selected display
    const selectedDisplay = document.createElement("div");
    selectedDisplay.className =
        "mt-1 block w-full rounded-md border border-gray-300 bg-white pl-3 pr-10 py-2 shadow-sm cursor-pointer searchable-select-display hover:border-gray-400 focus:border-gray-500 focus:ring-gray-500 sm:text-sm transition-colors duration-200";
    selectedDisplay.style.display = "none";

    // Add dropdown arrow
    const arrow = document.createElement("div");
    arrow.className =
        "absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none";
    arrow.innerHTML = `
        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
        </svg>
    `;
    wrapper.appendChild(arrow);

    // Insert wrapper before original select
    selectElement.parentNode.insertBefore(wrapper, selectElement);

    // Move original select into wrapper (hidden)
    wrapper.appendChild(selectElement);
    selectElement.style.display = "none";
    wrapper.appendChild(selectedDisplay);
    wrapper.appendChild(dropdown);

    // Populate dropdown with options
    const options = Array.from(selectElement.options);
    options.forEach((option) => {
        if (option.value) {
            const itemDiv = document.createElement("div");
            itemDiv.className =
                "px-3 py-2 cursor-pointer hover:bg-gray-50 searchable-select-option border-b border-gray-100 last:border-b-0 focused:bg-gray-100 focused:text-gray-900";
            itemDiv.textContent = option.text;
            itemDiv.dataset.value = option.value;
            itemDiv.dataset.price = option.dataset.price || "";
            itemDiv.dataset.description = option.dataset.description || "";

            // Add hover effects
            itemDiv.addEventListener("mouseenter", function () {
                this.classList.add("bg-gray-50");
                // Remove focus from keyboard navigation when hovering
                optionsContainer
                    .querySelectorAll(".searchable-select-option.focused")
                    .forEach((opt) => {
                        opt.classList.remove("focused");
                    });
            });
            itemDiv.addEventListener("mouseleave", function () {
                if (!this.classList.contains("focused")) {
                    this.classList.remove("bg-gray-50");
                }
            });

            itemDiv.addEventListener("click", function () {
                selectOption(searchInput, dropdown, selectElement, this);
            });

            optionsContainer.appendChild(itemDiv);
        }
    });

    // Append options container to dropdown
    dropdown.appendChild(optionsContainer);

    // Show search input when clicking selected display
    selectedDisplay.addEventListener("click", function () {
        toggleDropdown(searchInput, dropdown, true);
        // Add visual feedback for active state
        selectedDisplay.classList.add(
            "border-gray-400",
            "ring-1",
            "ring-gray-200",
        );
    });

    // Search functionality
    searchInput.addEventListener("input", function () {
        filterOptions(this.value, dropdown);
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (!wrapper.contains(e.target)) {
            closeDropdown(searchInput, dropdown);
            // Remove visual feedback
            selectedDisplay.classList.remove(
                "border-gray-400",
                "ring-1",
                "ring-gray-200",
            );
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            closeDropdown(searchInput, dropdown);
            return;
        }

        if (e.key === "ArrowDown") {
            e.preventDefault();
            focusNextOption(dropdown, searchInput);
            return;
        }

        if (e.key === "ArrowUp") {
            e.preventDefault();
            focusPreviousOption(dropdown, searchInput);
            return;
        }

        if (e.key === "Enter") {
            e.preventDefault();
            const focusedOption = dropdown.querySelector(
                ".searchable-select-option.focused",
            );
            if (focusedOption && focusedOption.style.display !== "none") {
                selectOption(
                    searchInput,
                    dropdown,
                    selectElement,
                    focusedOption,
                );
            }
            return;
        }

        if (e.key === "Tab") {
            closeDropdown(searchInput, dropdown);
        }
    });

    // Update display when original select changes
    selectElement.addEventListener("change", function () {
        updateSelectedDisplay(this, selectedDisplay);
    });

    // Initialize display
    updateSelectedDisplay(selectElement, selectedDisplay);
}

function toggleDropdown(searchInput, dropdown, focusSearch = false) {
    const isOpen = !dropdown.classList.contains("hidden");

    if (isOpen) {
        closeDropdown(searchInput, dropdown);
    } else {
        openDropdown(searchInput, dropdown, focusSearch);
    }
}

function openDropdown(searchInput, dropdown, focusSearch = false) {
    dropdown.classList.remove("hidden");
    searchInput.focus();
}

function closeDropdown(searchInput, dropdown) {
    dropdown.classList.add("hidden");
    searchInput.value = "";
    filterOptions("", dropdown);
}

function filterOptions(searchTerm, dropdown) {
    const options = dropdown.querySelectorAll(".searchable-select-option");
    const term = searchTerm.toLowerCase();

    options.forEach((option) => {
        const text = option.textContent.toLowerCase();
        if (text.includes(term)) {
            option.style.display = "block";
        } else {
            option.style.display = "none";
        }
    });

    // Show/hide "No results" message
    const visibleOptions = dropdown.querySelectorAll(
        ".searchable-select-option[style*='block'], .searchable-select-option:not([style*='none'])",
    );
    const noResultsMsg = dropdown.querySelector(
        ".searchable-select-no-results",
    );

    if (visibleOptions.length === 0 && !noResultsMsg) {
        const noResults = document.createElement("div");
        noResults.className =
            "px-3 py-2 text-gray-500 text-sm searchable-select-no-results";
        noResults.textContent = "No items found";
        dropdown.appendChild(noResults);
    } else if (visibleOptions.length > 0 && noResultsMsg) {
        noResultsMsg.remove();
    }
}

function focusNextOption(dropdown, searchInput) {
    const options = dropdown.querySelectorAll(
        '.searchable-select-option:not([style*="none"])',
    );

    if (options.length === 0) return;

    const focusedOption = dropdown.querySelector(
        ".searchable-select-option.focused",
    );
    let nextIndex = 0;

    if (focusedOption) {
        const currentIndex = Array.from(options).indexOf(focusedOption);
        if (currentIndex < options.length - 1) {
            nextIndex = currentIndex + 1;
        }
        focusedOption.classList.remove("focused");
    }

    options[nextIndex].classList.add("focused");
    options[nextIndex].scrollIntoView({ block: "nearest", behavior: "smooth" });
}

function focusPreviousOption(dropdown, searchInput) {
    const options = dropdown.querySelectorAll(
        '.searchable-select-option:not([style*="none"])',
    );

    if (options.length === 0) return;

    const focusedOption = dropdown.querySelector(
        ".searchable-select-option.focused",
    );
    let prevIndex = options.length - 1;

    if (focusedOption) {
        const currentIndex = Array.from(options).indexOf(focusedOption);
        if (currentIndex > 0) {
            prevIndex = currentIndex - 1;
        }
        focusedOption.classList.remove("focused");
    }

    options[prevIndex].classList.add("focused");
    options[prevIndex].scrollIntoView({ block: "nearest", behavior: "smooth" });
}

function selectOption(searchInput, dropdown, selectElement, selectedDiv) {
    const value = selectedDiv.dataset.value;

    // Update original select
    selectElement.value = value;

    // Trigger change event to match regular select behavior
    const changeEvent = new Event("change", {
        bubbles: true,
        target: selectElement,
    });
    selectElement.dispatchEvent(changeEvent);

    // Close dropdown and remove active state
    closeDropdown(searchInput, dropdown);
    const wrapper = selectElement.closest(".searchable-select-wrapper");
    const selectedDisplay = wrapper.querySelector(".searchable-select-display");
    selectedDisplay.classList.remove(
        "border-gray-400",
        "ring-1",
        "ring-gray-200",
    );
}

function updateSelectedDisplay(selectElement, selectedDisplay) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];

    if (selectedOption && selectedOption.value) {
        selectedDisplay.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-gray-900">${selectedOption.text}</span>
                <span class="text-sm text-gray-500 ml-2">${selectedOption.dataset.price ? "$" + selectedOption.dataset.price : ""}</span>
            </div>
        `;
        selectedDisplay.style.display = "block";
    } else {
        selectedDisplay.innerHTML = `
            <div class="flex items-center justify-between text-gray-500">
                <span>Select an item</span>
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        `;
        selectedDisplay.style.display = "block";
        selectedDisplay.classList.add("text-gray-400");
    }
}

// Initialize searchable selects on page load
document.addEventListener("DOMContentLoaded", function () {
    // Initialize all interactions
    initSidebarToggle();
    initDropdowns();
    initModals();
    initTooltips();
    initFormValidation();
    initAutoDismiss();
    console.log(
        "🔱 DOMContentLoaded fired - initializing searchable selects...",
    );
    console.log(
        "🔱 DOMContentLoaded fired - initializing searchable selects...",
    );
    initSearchableSelects();
    console.log("✅ InitSearchableSelects called from DOMContentLoaded");
});

// Function to initialize searchable selects on dynamically added content
window.initSearchableSelectsForNewContent = function () {
    initSearchableSelects();
};
