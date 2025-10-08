// ================================
// MOBILE MENU TOGGLE
// ================================
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('nav ul');

    if (mobileMenu && navLinks) {
        mobileMenu.addEventListener('click', () => {
            navLinks.classList.toggle('mobile-active');
            mobileMenu.classList.toggle('active');
        });

        // Sulje menu kun klikataan linkkiä
        document.querySelectorAll('nav ul li a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    navLinks.classList.remove('mobile-active');
                    mobileMenu.classList.remove('active');
                }
            });
        });
    }
});

// ================================
// SCROLL-TO-TOP NAPPI
// ================================
document.addEventListener('DOMContentLoaded', function() {
    // Luo nappi
    const scrollBtn = document.createElement('button');
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.innerHTML = '↑';
    scrollBtn.setAttribute('aria-label', 'Scroll to top');
    document.body.appendChild(scrollBtn);
    
    // Näytä/piilota nappi
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });
    
    // Scroll ylös kun klikataan
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

// ================================
// SCROLL ANIMAATIOT
// ================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Tarkkaile elementtejä
document.addEventListener('DOMContentLoaded', () => {
    // Lisää animaatioluokat elementeille
    document.querySelectorAll('section').forEach(section => {
        section.classList.add('fade-in-up');
        observer.observe(section);
    });
    
    document.querySelectorAll('.feature').forEach((feature, index) => {
        feature.classList.add('scale-in');
        feature.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(feature);
    });
    
    document.querySelectorAll('.artist-card').forEach((card, index) => {
        card.classList.add('fade-in-up');
        card.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(card);
    });
});

// ================================
// TOAST-NOTIFIKAATIOT
// ================================
function createToast(message, type = 'info', duration = 3000) {
    // Luo container jos ei ole
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    // Luo toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Ikoni
    let icon = '✓';
    if (type === 'error') icon = '✕';
    if (type === 'info') icon = 'ℹ';
    
    toast.innerHTML = `
        <span style="font-size: 1.5em;">${icon}</span>
        <span>${message}</span>
        <button class="toast-close">×</button>
    `;
    
    container.appendChild(toast);
    
    // Sulje-nappi
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.remove();
    });
    
    // Automaattinen poisto
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, duration);
}

// Muunna vanhat viestit toasteiksi
document.addEventListener('DOMContentLoaded', () => {
    const messages = document.querySelectorAll('.message');
    messages.forEach(msg => {
        const text = msg.textContent.trim();
        const type = msg.classList.contains('success') ? 'success' : 'error';
        
        if (text) {
            createToast(text, type, 4000);
            msg.style.display = 'none';
        }
    });
});

/* ================================
// LOADING ANIMAATIO
// ================================
document.addEventListener('DOMContentLoaded', () => {
    // Luo loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.prepend(loadingOverlay);
    
    // Piilota kun sivu latautunut
    window.addEventListener('load', () => {
        setTimeout(() => {
            loadingOverlay.classList.add('hidden');
            setTimeout(() => {
                loadingOverlay.remove();
            }, 200);
        }, 200);
    });
}); */

// ================================
// RIPPLE EFFECT NAPEILLE
// ================================
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn') || e.target.closest('.btn')) {
        const button = e.target.classList.contains('btn') ? e.target : e.target.closest('.btn');
        
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
});

// ================================
// SMOOTH SCROLL NAVIGATION
// ================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// ================================
// FORM VALIDATION SHAKE
// ================================
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input[required], textarea[required], select[required]');
        let hasError = false;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('shake');
                input.style.borderColor = '#f44336';
                hasError = true;
                
                setTimeout(() => {
                    input.classList.remove('shake');
                }, 500);
            } else {
                input.style.borderColor = '';
            }
        });
    });
});

// ================================
// CONSOLE MESSAGE
// ================================
console.log(`
🎸 Thunderstorm Rock Festival 2025
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Built with ❤️ by Kari Markus
GitHub: @MarKar07
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔥 Rock on! 🔥
`);