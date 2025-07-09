// Updated AJAX functions with proper headers
document.addEventListener("DOMContentLoaded", () => {
    initializeApp()
  })
  
  function initializeApp() {
    initializeAjax()
    initializeFiltering()
    initializeSearch()
    initializeForms()
  }
  
  function initializeAjax() {
    // Status update handlers
    document.querySelectorAll(".status-select, .status-select-mini").forEach((select) => {
      select.addEventListener("change", function () {
        if (this.value) {
          updateProjectStatus(this.dataset.projectId, this.value)
          this.value = ""
        }
      })
    })
  
    // Delete button handlers
    document.querySelectorAll(".delete-btn").forEach((button) => {
      button.addEventListener("click", function () {
        const projectTitle = this.closest(".project-card").querySelector(".project-title").textContent.trim()
        if (confirm(`Are you sure you want to delete "${projectTitle}"?`)) {
          deleteProject(this.dataset.projectId)
        }
      })
    })
  }
  
  // Initialize AJAX forms
  function initializeForms() {
    document.querySelectorAll(".ajax-form").forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault()
        submitFormAjax(this)
      })
    })
  
    // Real-time validation
    document.querySelectorAll(".ajax-form input, .ajax-form textarea, .ajax-form select").forEach((field) => {
      field.addEventListener("blur", function () {
        validateField(this)
      })
  
      field.addEventListener("input", function () {
        // Clear error state on input
        this.classList.remove("error")
        const errorContainer = this.parentNode.querySelector(".field-error-container")
        if (errorContainer) {
          errorContainer.innerHTML = ""
        }
      })
    })
  }
  
  // Submit form via AJAX
  function submitFormAjax(form) {
    const formData = new FormData(form)
    const submitBtn = form.querySelector('button[type="submit"]')
  
    // Validate form first
    if (!validateForm(form)) {
      return
    }
  
    // Show loading state
    if (submitBtn) {
      submitBtn.disabled = true
      submitBtn.classList.add("loading")
    }
  
    const xhr = new XMLHttpRequest()
    xhr.open("POST", window.location.href, true)
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest")
  
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        // Reset button
        if (submitBtn) {
          submitBtn.disabled = false
          submitBtn.classList.remove("loading")
        }
  
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText)
  
            if (response.success) {
              showNotification(response.message || "Operation completed successfully!", "success")
  
              // Reset form if successful
              if (form.dataset.resetOnSuccess !== "false") {
                form.reset()
                // Clear any validation errors
                form.querySelectorAll(".error").forEach((field) => field.classList.remove("error"))
                form.querySelectorAll(".field-error").forEach((error) => error.remove())
              }
  
              // Redirect if specified
              if (response.redirect || form.dataset.redirect) {
                setTimeout(() => {
                  window.location.href = response.redirect || form.dataset.redirect
                }, 1500)
              }
            } else {
              showNotification(response.message || "Operation failed", "error")
  
              // Show field-specific errors if available
              if (response.errors) {
                Object.keys(response.errors).forEach((fieldName) => {
                  const field = form.querySelector(`[name="${fieldName}"]`)
                  if (field) {
                    showFieldError(field, response.errors[fieldName])
                  }
                })
              }
            }
          } catch (e) {
            showNotification("An unexpected error occurred", "error")
          }
        } else {
          showNotification("Network error occurred", "error")
        }
      }
    }
  
    xhr.send(formData)
  }
  
  // Form validation
  function validateForm(form) {
    let isValid = true
    const fields = form.querySelectorAll("input[required], textarea[required], select[required]")
  
    fields.forEach((field) => {
      if (!validateField(field)) {
        isValid = false
      }
    })
  
    return isValid
  }
  
  function validateField(field) {
    const value = field.value.trim()
    const isRequired = field.hasAttribute("required")
    const fieldType = field.type
  
    let isValid = true
    let errorMessage = ""
  
    // Required validation
    if (isRequired && !value) {
      isValid = false
      errorMessage = "This field is required"
    }
  
    // Email validation
    if (fieldType === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(value)) {
        isValid = false
        errorMessage = "Please enter a valid email address"
      }
    }
  
    // Password validation
    if (fieldType === "password" && value && field.hasAttribute("minlength")) {
      const minLength = Number.parseInt(field.getAttribute("minlength"))
      if (value.length < minLength) {
        isValid = false
        errorMessage = `Password must be at least ${minLength} characters long`
      }
    }
  
    // Show/hide error message
    showFieldError(field, isValid ? "" : errorMessage)
  
    return isValid
  }
  
  function showFieldError(field, message) {
    // Remove existing error
    const errorContainer = field.parentNode.querySelector(".field-error-container")
    if (errorContainer) {
      errorContainer.innerHTML = ""
    }
  
    // Add new error if message exists
    if (message) {
      const errorDiv = document.createElement("div")
      errorDiv.className = "field-error"
      errorDiv.textContent = message
  
      if (errorContainer) {
        errorContainer.appendChild(errorDiv)
      } else {
        field.parentNode.appendChild(errorDiv)
      }
  
      field.classList.add("error")
    } else {
      field.classList.remove("error")
    }
  }
  
  // Update project status via AJAX with proper headers
  function updateProjectStatus(projectId, newStatus) {
    const card = document.querySelector(`[data-project-id="${projectId}"]`)
    if (!card) return
  
    card.classList.add("loading")
  
    const xhr = new XMLHttpRequest()
    xhr.open("POST", window.location.href, true)
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest") // Important for AJAX detection
  
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        card.classList.remove("loading")
  
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText)
            if (response.success) {
              // Update status badge
              const statusBadge = card.querySelector(".status-badge")
              if (statusBadge) {
                statusBadge.className = `status-badge status-${newStatus}`
                statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1)
              }
  
              // Update data attribute for filtering
              card.dataset.status = newStatus
  
              showNotification("Project status updated successfully!", "success")
            } else {
              showNotification(response.message || "Failed to update status", "error")
            }
          } catch (e) {
            // Fallback for non-JSON response (page reload case)
            showNotification("Status updated successfully!", "success")
  
            // Update UI manually
            const statusBadge = card.querySelector(".status-badge")
            if (statusBadge) {
              statusBadge.className = `status-badge status-${newStatus}`
              statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1)
            }
            card.dataset.status = newStatus
          }
        } else {
          showNotification("Failed to update project status", "error")
        }
      }
    }
  
    const data = `project_id=${projectId}&new_status=${newStatus}&update_status=1`
    xhr.send(data)
  }
  
  // Delete project via AJAX with proper headers
  function deleteProject(projectId) {
    const card = document.querySelector(`[data-project-id="${projectId}"]`)
    if (!card) return
  
    card.classList.add("loading")
  
    const xhr = new XMLHttpRequest()
    xhr.open("POST", window.location.href, true)
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest") // Important for AJAX detection
  
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText)
            if (response.success) {
              // Animate card removal
              card.style.transition = "all 0.3s ease"
              card.style.opacity = "0"
              card.style.transform = "scale(0.8)"
  
              setTimeout(() => {
                card.remove()
                showNotification("Project deleted successfully!", "success")
  
                // Check if no projects left
                const remainingCards = document.querySelectorAll(".project-card, .project-item")
                if (remainingCards.length === 0) {
                  setTimeout(() => location.reload(), 1000)
                }
              }, 300)
            } else {
              card.classList.remove("loading")
              showNotification(response.message || "Failed to delete project", "error")
            }
          } catch (e) {
            // Fallback for non-JSON response
            card.style.transition = "all 0.3s ease"
            card.style.opacity = "0"
            card.style.transform = "scale(0.8)"
  
            setTimeout(() => {
              card.remove()
              showNotification("Project deleted successfully!", "success")
            }, 300)
          }
        } else {
          card.classList.remove("loading")
          showNotification("Failed to delete project", "error")
        }
      }
    }
  
    const data = `project_id=${projectId}&delete_project=1`
    xhr.send(data)
  }
  
  // Filtering and search functions
  function initializeFiltering() {
    document.querySelectorAll("[data-filter]").forEach((filter) => {
      filter.addEventListener("change", applyFilters)
    })
  }
  
  function initializeSearch() {
    const searchInput = document.querySelector("[data-search]")
    if (searchInput) {
      let searchTimeout
      searchInput.addEventListener("input", () => {
        clearTimeout(searchTimeout)
        searchTimeout = setTimeout(() => {
          applyFilters()
        }, 300)
      })
    }
  }
  
  function applyFilters() {
    const searchTerm = getSearchTerm()
    const statusFilter = getFilterValue("status")
    const priorityFilter = getFilterValue("priority")
  
    const cards = document.querySelectorAll(".project-card")
    let visibleCount = 0
  
    cards.forEach((card) => {
      const isVisible = shouldShowCard(card, searchTerm, statusFilter, priorityFilter)
  
      if (isVisible) {
        card.style.display = "block"
        visibleCount++
  
        if (searchTerm) {
          highlightSearchTerms(card, searchTerm)
        } else {
          removeHighlights(card)
        }
      } else {
        card.style.display = "none"
        removeHighlights(card)
      }
    })
  
    updateResultsInfo(visibleCount, cards.length, searchTerm)
  }
  
  function getSearchTerm() {
    const searchInput = document.querySelector("[data-search]")
    return searchInput ? searchInput.value.toLowerCase().trim() : ""
  }
  
  function getFilterValue(filterType) {
    const filter = document.querySelector(`[data-filter="${filterType}"]`)
    return filter ? filter.value : "all"
  }
  
  function shouldShowCard(card, searchTerm, statusFilter, priorityFilter) {
    const title = card.querySelector(".project-title")?.textContent.toLowerCase() || ""
    const description = card.querySelector(".project-description")?.textContent.toLowerCase() || ""
    const searchMatch = !searchTerm || title.includes(searchTerm) || description.includes(searchTerm)
  
    const cardStatus = card.dataset.status || ""
    const statusMatch = statusFilter === "all" || cardStatus === statusFilter
  
    const cardPriority = card.dataset.priority || ""
    const priorityMatch = priorityFilter === "all" || cardPriority === priorityFilter
  
    return searchMatch && statusMatch && priorityMatch
  }
  
  function highlightSearchTerms(card, searchTerm) {
    const elementsToHighlight = card.querySelectorAll(".project-title, .project-description")
  
    elementsToHighlight.forEach((element) => {
      const originalText = element.textContent
      const regex = new RegExp(`(${escapeRegex(searchTerm)})`, "gi")
      element.innerHTML = originalText.replace(regex, "<mark>$1</mark>")
    })
  }
  
  function removeHighlights(card) {
    const marks = card.querySelectorAll("mark")
    marks.forEach((mark) => {
      mark.outerHTML = mark.textContent
    })
  }
  
  function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")
  }
  
  function updateResultsInfo(visible, total, searchTerm) {
    const resultsInfo = document.querySelector(".search-results-info")
    if (resultsInfo) {
      if (searchTerm) {
        resultsInfo.textContent = `Found ${visible} projects matching "${searchTerm}"`
      } else {
        resultsInfo.textContent = `Showing ${visible} of ${total} projects`
      }
    }
  }
  
  function showNotification(message, type = "info") {
    document.querySelectorAll(".notification").forEach((notif) => notif.remove())
  
    const notification = document.createElement("div")
    notification.className = `notification notification-${type}`
    notification.innerHTML = `
          <span class="notification-message">${message}</span>
          <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
      `
  
    document.body.appendChild(notification)
  
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove()
      }
    }, 5000)
  
    setTimeout(() => {
      notification.classList.add("show")
    }, 100)
  }
  
  // ===== KEYBOARD SHORTCUTS =====
  
  document.addEventListener("keydown", (e) => {
    // Ctrl/Cmd + K: Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault()
      const searchInput = document.querySelector("[data-search]")
      if (searchInput) {
        searchInput.focus()
      }
    }
  
    // Escape: Close notifications
    if (e.key === "Escape") {
      document.querySelectorAll(".notification").forEach((notif) => notif.remove())
    }
  })
  
  // ===== UTILITY FUNCTIONS =====
  
  function debounce(func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  }
  
  // Initialize app when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeApp)
  } else {
    initializeApp()
  }
  