// Main JavaScript for TechStore
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart count
    updateCartCount();
    
    // Add to cart functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart')) {
            e.preventDefault();
            const button = e.target.closest('.add-to-cart');
            const productId = button.getAttribute('data-id');
            addToCart(productId);
        }
    });
    
    // Quantity controls in cart
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quantity-btn')) {
            e.preventDefault();
            const button = e.target.closest('.quantity-btn');
            const action = button.getAttribute('data-action');
            const productId = button.getAttribute('data-id');
            const quantityInput = button.parentElement.querySelector('.quantity-input');
            
            let newQuantity = parseInt(quantityInput.value);
            
            if (action === 'increase') {
                newQuantity++;
            } else if (action === 'decrease' && newQuantity > 1) {
                newQuantity--;
            }
            
            updateCartQuantity(productId, newQuantity);
            quantityInput.value = newQuantity;
        }
    });
    
    // Remove from cart
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-from-cart')) {
            e.preventDefault();
            const button = e.target.closest('.remove-from-cart');
            const productId = button.getAttribute('data-id');
            removeFromCart(productId);
        }
    });
    
    // Search functionality
    const searchForm = document.querySelector('form[action="search.php"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="q"]');
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                showToast('Vui lòng nhập từ khóa tìm kiếm', 'warning');
            }
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Image gallery
    const galleryImages = document.querySelectorAll('.product-gallery img');
    const mainImage = document.querySelector('.main-product-image');
    
    galleryImages.forEach(img => {
        img.addEventListener('click', function() {
            if (mainImage) {
                mainImage.src = this.src;
            }
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Add to cart function
function addToCart(productId, quantity = 1) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showToast('Đã thêm sản phẩm vào giỏ hàng', 'success');
            
            // Animate cart badge
            const cartBadge = document.getElementById('cart-count');
            if (cartBadge) {
                cartBadge.classList.add('updated');
                setTimeout(() => cartBadge.classList.remove('updated'), 600);
            }
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra khi thêm sản phẩm', 'error');
    });
}

// Update cart quantity
function updateCartQuantity(productId, quantity) {
    fetch('ajax/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            updateCartTotal();
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra', 'error');
    });
}

// Remove from cart
function removeFromCart(productId) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount();
                updateCartTotal();
                // Remove cart item from DOM
                const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
                if (cartItem) {
                    cartItem.style.opacity = '0';
                    setTimeout(() => cartItem.remove(), 300);
                }
                showToast('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
            } else {
                showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Có lỗi xảy ra', 'error');
        });
    }
}

// Update cart count
function updateCartCount() {
    fetch('ajax/get_cart_count.php')
    .then(response => response.json())
    .then(data => {
        const cartBadge = document.getElementById('cart-count');
        if (cartBadge) {
            cartBadge.textContent = data.count;
            cartBadge.style.display = data.count > 0 ? 'inline' : 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Update cart total
function updateCartTotal() {
    fetch('ajax/get_cart_total.php')
    .then(response => response.json())
    .then(data => {
        const totalElement = document.getElementById('cart-total');
        if (totalElement) {
            totalElement.textContent = formatPrice(data.total);
        }
        
        const subtotalElement = document.getElementById('cart-subtotal');
        if (subtotalElement) {
            subtotalElement.textContent = formatPrice(data.total);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Create toast container if it doesn't exist
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

// Format price
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

// Search suggestions
function initSearchSuggestions() {
    const searchInput = document.querySelector('input[name="q"]');
    if (!searchInput) return;
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideSearchSuggestions();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            hideSearchSuggestions();
        }
    });
}

// Fetch search suggestions
function fetchSearchSuggestions(query) {
    fetch(`ajax/search_suggestions.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.suggestions.length > 0) {
            showSearchSuggestions(data.suggestions);
        } else {
            hideSearchSuggestions();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Show search suggestions
function showSearchSuggestions(suggestions) {
    let suggestionsContainer = document.querySelector('.search-suggestions');
    if (!suggestionsContainer) {
        suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions position-absolute bg-white border rounded shadow-lg';
        suggestionsContainer.style.top = '100%';
        suggestionsContainer.style.left = '0';
        suggestionsContainer.style.right = '0';
        suggestionsContainer.style.zIndex = '1000';
        suggestionsContainer.style.maxHeight = '300px';
        suggestionsContainer.style.overflowY = 'auto';
        
        const searchContainer = document.querySelector('.search-container');
        if (searchContainer) {
            searchContainer.style.position = 'relative';
            searchContainer.appendChild(suggestionsContainer);
        }
    }
    
    suggestionsContainer.innerHTML = suggestions.map(suggestion => `
        <div class="suggestion-item p-3 border-bottom cursor-pointer" data-product-id="${suggestion.id}">
            <div class="d-flex align-items-center">
                <img src="${suggestion.image}" alt="${suggestion.name}" class="me-3" style="width: 40px; height: 40px; object-fit: cover;">
                <div>
                    <div class="fw-bold">${suggestion.name}</div>
                    <div class="text-muted small">${formatPrice(suggestion.price)}</div>
                </div>
            </div>
        </div>
    `).join('');
    
    // Add click handlers
    suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
        item.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            window.location.href = `product.php?id=${productId}`;
        });
    });
    
    suggestionsContainer.style.display = 'block';
}

// Hide search suggestions
function hideSearchSuggestions() {
    const suggestionsContainer = document.querySelector('.search-suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.style.display = 'none';
    }
}

// Product image zoom
function initImageZoom() {
    const productImages = document.querySelectorAll('.product-image img, .main-product-image');
    
    productImages.forEach(img => {
        img.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'image-zoom-modal position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
            modal.style.backgroundColor = 'rgba(0,0,0,0.9)';
            modal.style.zIndex = '9999';
            modal.innerHTML = `
                <div class="position-relative">
                    <img src="${this.src}" alt="${this.alt}" class="img-fluid" style="max-height: 90vh;">
                    <button class="btn-close position-absolute top-0 end-0 m-3" style="background: white;"></button>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal || e.target.classList.contains('btn-close')) {
                    modal.remove();
                }
            });
        });
    });
}

// Initialize all features
document.addEventListener('DOMContentLoaded', function() {
    initSearchSuggestions();
    initImageZoom();
    
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
            }
        });
    });
});

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for global use
window.TechStore = {
    addToCart,
    updateCartQuantity,
    removeFromCart,
    showToast,
    formatPrice
};
