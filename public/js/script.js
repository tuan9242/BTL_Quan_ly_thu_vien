// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const userDropdown = document.querySelector('.user-dropdown');
    const userBtn = document.querySelector('.user-dropdown .user-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.add('active');
        });
    }
    
    if (closeMobileMenu) {
        closeMobileMenu.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (mobileMenu && !mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            mobileMenu.classList.remove('active');
        }
    });
    
    // Auto-hide alerts after 5 seconds
});

// Toggle user dropdown on click
if (userBtn && userDropdown) {
    userBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('open');
    });
    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!userDropdown.contains(e.target)) {
            userDropdown.classList.remove('open');
        }
    });
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            userDropdown.classList.remove('open');
        }
    });
}
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

// Confirm Delete
function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
    return confirm(message);
}

// Format Number
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Validate Form
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Show Loading
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading';
    loading.innerHTML = `
        <div class="loading-overlay">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Đang xử lý...</p>
            </div>
        </div>
    `;
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.remove();
    }
}

// Search with debounce
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

// Live search
const searchInput = document.querySelector('.search-input');
if (searchInput) {
    const handleSearch = debounce(function(e) {
        const keyword = e.target.value;
        if (keyword.length >= 2) {
            // Perform search
            console.log('Searching for:', keyword);
        }
    }, 500);
    
    searchInput.addEventListener('input', handleSearch);
}

// Table Actions
function editRow(id, type) {
    window.location.href = `index.php?page=admin-${type}&action=edit&id=${id}`;
}

function deleteRow(id, type) {
    if (confirmDelete()) {
        showLoading();
        fetch(`index.php?page=admin-${type}&action=delete&id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(error => {
            hideLoading();
            alert('Có lỗi xảy ra!');
        });
    }
}

// Print Function
function printPage() {
    window.print();
}

// Export to CSV
function exportToCSV(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push(col.innerText);
        });
        csv.push(csvRow.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
}

// Date Picker Helper
function initDatePicker() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.valueAsDate = new Date();
        }
    });
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Image Preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form Validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger)';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showNotification('Vui lòng điền đầy đủ thông tin!', 'error');
        }
    });
});

// Smooth Scroll
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

// Countdown Timer for Due Dates
function startCountdown(elementId, dueDate) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const countdownDate = new Date(dueDate).getTime();
    
    const timer = setInterval(function() {
        const now = new Date().getTime();
        const distance = countdownDate - now;
        
        if (distance < 0) {
            clearInterval(timer);
            element.innerHTML = "ĐÃ QUÁ HẠN";
            element.style.color = 'var(--danger)';
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        
        element.innerHTML = `${days}d ${hours}h ${minutes}m`;
    }, 1000);
}

// Initialize tooltips (if needed)
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

// Initialize on page load
window.addEventListener('load', function() {
    initDatePicker();
    initTooltips();
    initTableDragScroll();
});

// Table drag scroll functionality
function initTableDragScroll() {
    const tableWrappers = document.querySelectorAll('.table-wrapper, .table-responsive');
    tableWrappers.forEach(wrapper => {
        let isDown = false;
        let startX;
        let scrollLeft;

        wrapper.addEventListener('mousedown', (e) => {
            isDown = true;
            wrapper.style.cursor = 'grabbing';
            startX = e.pageX - wrapper.offsetLeft;
            scrollLeft = wrapper.scrollLeft;
        });

        wrapper.addEventListener('mouseleave', () => {
            isDown = false;
            wrapper.style.cursor = 'grab';
        });

        wrapper.addEventListener('mouseup', () => {
            isDown = false;
            wrapper.style.cursor = 'grab';
        });

        wrapper.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - wrapper.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed multiplier
            wrapper.scrollLeft = scrollLeft - walk;
        });
    });
}

// Realtime Validation Enhancements
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form [required]').forEach(function(input){
        input.addEventListener('input', function(){
            if (input.value.trim()) {
                input.classList.remove('error');
                input.style.borderColor = '';
            }
        });
    });
});

// Drag & Drop Upload for cover image
document.addEventListener('DOMContentLoaded', function(){
    const fileInputs = document.querySelectorAll('input[type="file"][name="cover_image"]');
    fileInputs.forEach(function(input){
        let dropArea = input.closest('.dropzone');
        if (!dropArea) {
            // Create a dropzone wrapper if not exists
            dropArea = document.createElement('div');
            dropArea.className = 'dropzone';
            input.parentNode.insertBefore(dropArea, input);
            dropArea.appendChild(input);
            const hint = document.createElement('div');
            hint.className = 'dropzone-hint';
            hint.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Kéo thả ảnh vào đây hoặc chọn file';
            dropArea.appendChild(hint);
        }
        ;['dragenter','dragover','dragleave','drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function(e){
                e.preventDefault();
                e.stopPropagation();
            });
        });
        ;['dragenter','dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, function(){
                dropArea.classList.add('highlight');
            });
        });
        ;['dragleave','drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function(){
                dropArea.classList.remove('highlight');
            });
        });
        dropArea.addEventListener('drop', function(e){
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                input.files = files;
                // optional preview
                const preview = dropArea.querySelector('img.preview') || document.getElementById('cover_preview');
                if (preview) {
                    const reader = new FileReader();
                    reader.onload = function(ev){ preview.src = ev.target.result; };
                    reader.readAsDataURL(files[0]);
                }
            }
        });
    });
});

// Styles for dropzone
(function(){
    const style2 = document.createElement('style');
    style2.textContent = `
    .dropzone {
        border: 2px dashed var(--light-gray);
        border-radius: var(--border-radius);
        padding: 1rem;
        text-align: center;
        position: relative;
    }
    .dropzone.highlight { border-color: var(--primary); background: rgba(99,102,241,0.05); }
    .dropzone input[type="file"] { width: 100%; display: block; margin: .5rem 0; }
    .dropzone-hint { color: var(--gray); font-size: .9rem; }
    `;
    document.head.appendChild(style2);
})();

// Add custom styles for notifications
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-lg);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        transition: opacity 0.3s ease;
    }
    
    .notification-success {
        border-left: 4px solid var(--success);
    }
    
    .notification-error {
        border-left: 4px solid var(--danger);
    }
    
    .notification-info {
        border-left: 4px solid var(--primary);
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    
    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        text-align: center;
    }
    
    .loading-spinner i {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
    
    .tooltip {
        position: absolute;
        background: var(--dark);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        pointer-events: none;
        z-index: 9999;
    }
`;
document.head.appendChild(style);