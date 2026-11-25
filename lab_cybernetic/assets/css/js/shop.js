/**
 * Shop Module - Gestione catalogo e carrello
 */

const Shop = (function() {
    'use strict';
    
    const API_BASE = '/api/v1';
    let currentPage = 1;
    let currentCategory = '';
    let currentSearch = '';
    let totalProducts = 0;
    let cart = JSON.parse(localStorage.getItem('cyber_cart') || '[]');
    
    function init() {
        loadCategories();
        loadProducts();
        bindEvents();
        updateCartCount();
    }
    
    function bindEvents() {
        // Category filter
        const categoryFilter = document.getElementById('category-filter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                currentCategory = e.target.value;
                currentPage = 1;
                loadProducts();
            });
        }
        
        // Search input
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentSearch = e.target.value.trim();
                    currentPage = 1;
                    loadProducts();
                }, 300);
            });
        }
        
        // Pagination
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    loadProducts();
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const totalPages = Math.ceil(totalProducts / 20);
                if (currentPage < totalPages) {
                    currentPage++;
                    loadProducts();
                }
            });
        }
    }
    
    async function loadCategories() {
        try {
            const response = await fetch(`${API_BASE}/shop/products.php`);
            const data = await response.json();
            
            if (data.success && data.products) {
                const categories = [...new Set(data.products.map(p => p.category).filter(Boolean))];
                const select = document.getElementById('category-filter');
                if (select) {
                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat;
                        option.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Load categories error:', error);
        }
    }
    
    async function loadProducts() {
        const grid = document.getElementById('products-grid');
        const pagination = document.getElementById('pagination');
        
        if (!grid) return;
        
        grid.innerHTML = '<div class="loading">Caricamento prodotti...</div>';
        if (pagination) pagination.style.display = 'none';
        
        try {
            const params = new URLSearchParams({
                limit: 20,
                offset: (currentPage - 1) * 20
            });
            
            if (currentCategory) params.append('category', currentCategory);
            if (currentSearch) params.append('search', currentSearch);
            
            const response = await fetch(`${API_BASE}/shop/products.php?${params}`);
            const data = await response.json();
            
            if (data.success) {
                totalProducts = data.total || 0;
                renderProducts(data.products);
                updatePagination();
                if (pagination) pagination.style.display = 'flex';
            } else {
                grid.innerHTML = '<div class="loading">Errore caricamento prodotti</div>';
            }
        } catch (error) {
            console.error('Load products error:', error);
            grid.innerHTML = '<div class="loading">Errore di connessione</div>';
        }
    }
    
    function renderProducts(products) {
        const grid = document.getElementById('products-grid');
        if (!grid) return;
        
        if (!products || products.length === 0) {
            grid.innerHTML = '<div class="loading">Nessun prodotto trovato</div>';
            return;
        }
        
        grid.innerHTML = products.map(product => {
            const hasDiscount = product.discount && product.discount > 0;
            const price = hasDiscount ? product.discounted_price : product.price;
            const originalPrice = hasDiscount ? product.price : null;
            
            return `
                <article class="product-card">
                    <div class="product-image">
                        ${product.image 
                            ? `<img src="/uploads/products/${product.image}" alt="${escapeHtml(product.name)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                               <span style="display:none;">📦</span>`
                            : '<span>📦</span>'}
                    </div>
                    <div class="product-info">
                        <span class="product-category">${escapeHtml(product.category || 'Generale')}</span>
                        <h3 class="product-name">${escapeHtml(product.name)}</h3>
                        <p class="product-description">${escapeHtml(product.description || '')}</p>
                        <div class="product-price">
                            <span class="price-current">€${price.toFixed(2)}</span>
                            ${originalPrice ? `<span class="price-original">€${originalPrice.toFixed(2)}</span>` : ''}
                            ${hasDiscount ? `<span class="discount-badge">-${product.discount}%</span>` : ''}
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-primary btn-add-cart" data-id="${product.id}" data-name="${escapeHtml(product.name)}" data-price="${price}">
                                Aggiungi al carrello
                            </button>
                            <button class="btn btn-secondary btn-product-detail" data-id="${product.id}">Dettagli</button>
                        </div>
                    </div>
                </article>
            `;
        }).join('');
        
        // Bind add to cart buttons
        grid.querySelectorAll('.btn-add-cart').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.target.dataset.id);
                const name = e.target.dataset.name;
                const price = parseFloat(e.target.dataset.price);
                addToCart(id, name, price);
            });
        });
    }
    
    function updatePagination() {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const pageInfo = document.getElementById('page-info');
        
        const totalPages = Math.ceil(totalProducts / 20);
        
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        if (pageInfo) pageInfo.textContent = `Pagina ${currentPage} di ${totalPages || 1}`;
    }
    
    function addToCart(id, name, price) {
        const existing = cart.find(item => item.id === id);
        
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({ id, name, price, quantity: 1 });
        }
        
        saveCart();
        updateCartCount();
        showToast(`${name} aggiunto al carrello!`);
    }
    
    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        saveCart();
        updateCartCount();
    }
    
    function updateQuantity(id, quantity) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.quantity = Math.max(1, quantity);
            saveCart();
            updateCartCount();
        }
    }
    
    function getCart() {
        return [...cart];
    }
    
    function getCartTotal() {
        return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    }
    
    function getCartCount() {
        return cart.reduce((sum, item) => sum + item.quantity, 0);
    }
    
    function saveCart() {
        localStorage.setItem('cyber_cart', JSON.stringify(cart));
    }
    
    function updateCartCount() {
        const countEl = document.getElementById('cart-count');
        if (countEl) {
            countEl.textContent = getCartCount();
        }
    }
    
    function clearCart() {
        cart = [];
        saveCart();
        updateCartCount();
    }
    
    function showToast(message) {
        // Crea toast notification
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 12px 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-hover);
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Espone API pubblica
    return {
        init,
        getCart,
        getCartTotal,
        getCartCount,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart
    };
})();

// Aggiungi stili per toast
const style = document.createElement('style');
style.textContent = `
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
`;
document.head.appendChild(style);
