/**
 * Auth Module - Gestione autenticazione lato client
 */

const Auth = (function() {
    'use strict';
    
    const API_BASE = '/api/v1';
    const TOKEN_COOKIE = 'cyber_session';
    
    let currentUser = null;
    let isInitialized = false;
    
    function init() {
        if (isInitialized) return;
        
        // Verifica se c'è un token nei cookie
        const token = getCookie(TOKEN_COOKIE);
        if (token) {
            validateSession(token);
        }
        
        updateUI();
        isInitialized = true;
    }
    
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    function setCookie(name, value, days = 1) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
    }
    
    function deleteCookie(name) {
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
    }
    
    async function validateSession(token) {
        try {
            const response = await fetch(`${API_BASE}/user/dashboard.php`, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.user) {
                currentUser = data.user;
                return true;
            } else {
                currentUser = null;
                deleteCookie(TOKEN_COOKIE);
                return false;
            }
        } catch (error) {
            console.error('Session validation error:', error);
            currentUser = null;
            return false;
        }
    }
    
    async function login(username, password) {
        const response = await fetch(`${API_BASE}/auth/login.php`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.user;
            // Il token viene impostato via cookie HttpOnly dal server
            return { success: true, user: data.user };
        }
        
        return { success: false, error: data.error || 'Errore di login' };
    }
    
    async function logout() {
        try {
            await fetch(`${API_BASE}/auth/logout.php`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
        } catch (error) {
            console.error('Logout error:', error);
        }
        
        currentUser = null;
        deleteCookie(TOKEN_COOKIE);
        updateUI();
    }
    
    function isAuthenticated() {
        return currentUser !== null;
    }
    
    function getUser() {
        return currentUser;
    }
    
    function getUserRole() {
        return currentUser?.role || null;
    }
    
    function getUserId() {
        return currentUser?.id || null;
    }
    
    function updateUI() {
        const dashboardLink = document.getElementById('dashboard-link');
        const adminLink = document.getElementById('admin-link');
        const logoutBtn = document.getElementById('logout-btn');
        const cartIcon = document.getElementById('cart-icon');
        const loginLink = document.querySelector('a[href="/login.html"]');
        
        if (isAuthenticated()) {
            if (dashboardLink) dashboardLink.style.display = 'inline-block';
            if (logoutBtn) logoutBtn.style.display = 'inline-block';
            if (cartIcon) cartIcon.style.display = 'flex';
            if (loginLink) loginLink.style.display = 'none';
            
            if (getUserRole() === 'admin') {
                if (adminLink) adminLink.style.display = 'inline-block';
            }
        } else {
            if (dashboardLink) dashboardLink.style.display = 'none';
            if (adminLink) adminLink.style.display = 'none';
            if (logoutBtn) logoutBtn.style.display = 'none';
            if (cartIcon) cartIcon.style.display = 'none';
            if (loginLink) loginLink.style.display = 'inline-block';
        }
    }
    
    function bindLoginForm() {
        const form = document.getElementById('login-form');
        const alert = document.getElementById('login-alert');
        
        if (!form) return;
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (!username || !password) {
                showAlert(alert, 'Inserisci username e password', 'error');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Accesso...';
            hideAlert(alert);
            
            const result = await login(username, password);
            
            submitBtn.disabled = false;
            submitBtn.textContent = 'Login';
            
            if (result.success) {
                showAlert(alert, 'Login effettuato! Reindirizzamento...', 'success');
                setTimeout(() => {
                    window.location.href = '/dashboard.html';
                }, 1000);
            } else {
                showAlert(alert, result.error, 'error');
            }
        });
    }
    
    function bindLogoutButton() {
        const logoutBtn = document.getElementById('logout-btn');
        if (!logoutBtn) return;
        
        logoutBtn.addEventListener('click', async () => {
            await logout();
            window.location.href = '/login.html';
        });
    }
    
    function showAlert(element, message, type = 'info') {
        if (!element) return;
        element.textContent = message;
        element.className = `alert alert-${type}`;
        element.style.display = 'flex';
    }
    
    function hideAlert(element) {
        if (!element) return;
        element.style.display = 'none';
    }
    
    // Espone API pubblica
    return {
        init,
        login,
        logout,
        isAuthenticated,
        getUser,
        getUserRole,
        getUserId,
        updateUI,
        bindLoginForm,
        bindLogoutButton
    };
})();

// Auto-bind logout button se presente
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Auth.bindLogoutButton());
} else {
    Auth.bindLogoutButton();
}
