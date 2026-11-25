/**
 * Admin Module - Gestione pannello amministrazione
 */

const Admin = (function() {
    'use strict';
    
    const API_BASE = '/api/v1';
    let currentPage = 1;
    let currentPluginsPage = 1;
    
    function init() {
        loadUsers();
        bindUserEvents();
        updateUserInfo();
    }
    
    function initPlugins() {
        loadPlugins();
        bindPluginEvents();
        updateUserInfo();
    }
    
    function updateUserInfo() {
        const userInfo = document.getElementById('admin-user-info');
        if (userInfo) {
            const user = Auth.getUser();
            if (user) {
                userInfo.textContent = `Admin: ${user.username} (${user.role})`;
            }
        }
    }
    
    async function loadUsers() {
        const tbody = document.getElementById('users-tbody');
        const pagination = document.getElementById('admin-pagination');
        
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="8" class="loading">Caricamento utenti...</td></tr>';
        if (pagination) pagination.style.display = 'none';
        
        try {
            const response = await fetch(`${API_BASE}/admin/users.php?limit=20&offset=${(currentPage - 1) * 20}`, {
                credentials: 'include'
            });
            const data = await response.json();
            
            if (data.success && data.users) {
                renderUsers(data.users);
                updateUsersPagination(data.total || 0);
                if (pagination) pagination.style.display = 'flex';
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="loading">Errore caricamento utenti</td></tr>';
            }
        } catch (error) {
            console.error('Load users error:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="loading">Errore di connessione</td></tr>';
        }
    }
    
    function renderUsers(users) {
        const tbody = document.getElementById('users-tbody');
        if (!tbody) return;
        
        if (!users || users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="loading">Nessun utente trovato</td></tr>';
            return;
        }
        
        tbody.innerHTML = users.map(user => {
            const roleClass = `badge-${user.role}`;
            const statusClass = user.is_active ? 'badge-success' : 'badge-danger';
            const statusText = user.is_active ? 'Attivo' : 'Disattivo';
            
            return `
                <tr data-id="${user.id}">
                    <td>${user.id}</td>
                    <td>${escapeHtml(user.username)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td><span class="badge ${roleClass}">${user.role}</span></td>
                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                    <td>${formatDate(user.created_at)}</td>
                    <td>${user.last_login ? formatDate(user.last_login) : 'Mai'}</td>
                    <td>
                        <div class="action-buttons">
                            ${user.id !== Auth.getUserId() && user.role !== 'admin' ? `
                                <button class="btn btn-danger btn-sm btn-delete-user" data-id="${user.id}" data-username="${escapeHtml(user.username)}">Elimina</button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        
        tbody.querySelectorAll('.btn-delete-user').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.target.dataset.id);
                const username = e.target.dataset.username;
                deleteUser(id, username);
            });
        });
    }
    
    function updateUsersPagination(total) {
        const prevBtn = document.getElementById('admin-prev');
        const nextBtn = document.getElementById('admin-next');
        const pageInfo = document.getElementById('admin-page-info');
        
        const totalPages = Math.ceil(total / 20);
        
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        if (pageInfo) pageInfo.textContent = `Pagina ${currentPage} di ${totalPages || 1}`;
    }
    
    function bindUserEvents() {
        const prevBtn = document.getElementById('admin-prev');
        const nextBtn = document.getElementById('admin-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    loadUsers();
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentPage++;
                loadUsers();
            });
        }
    }
    
    async function deleteUser(id, username) {
        if (!confirm(`Sei sicuro di voler eliminare l'utente "${username}"?`)) return;
        
        try {
            const response = await fetch(`${API_BASE}/admin/users.php`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(`Utente ${username} eliminato`, 'success');
                loadUsers();
            } else {
                showToast(data.error || 'Errore eliminazione', 'error');
            }
        } catch (error) {
            console.error('Delete user error:', error);
            showToast('Errore di connessione', 'error');
        }
    }
    
    async function loadPlugins() {
        const grid = document.getElementById('plugins-grid');
        if (!grid) return;
        
        grid.innerHTML = '<div class="loading">Caricamento plugin...</div>';
        
        try {
            const response = await fetch('/plugins/installed.json');
            const data = await response.json();
            
            if (data.plugins) {
                renderPlugins(data.plugins);
            } else {
                grid.innerHTML = '<div class="loading">Nessun plugin installato</div>';
            }
        } catch (error) {
            console.error('Load plugins error:', error);
            grid.innerHTML = '<div class="loading">Errore caricamento plugin</div>';
        }
    }
    
    function renderPlugins(plugins) {
        const grid = document.getElementById('plugins-grid');
        if (!grid) return;
        
        const entries = Object.entries(plugins);
        
        if (entries.length === 0) {
            grid.innerHTML = '<div class="loading">Nessun plugin installato</div>';
            return;
        }
        
        grid.innerHTML = entries.map(([id, plugin]) => {
            const enabled = plugin.enabled !== false;
            return `
                <div class="plugin-card ${enabled ? 'enabled' : 'disabled'}">
                    <div class="plugin-header">
                        <div>
                            <div class="plugin-name">${escapeHtml(plugin.name)}</div>
                            <span class="plugin-version">v${plugin.version}</span>
                        </div>
                        <span class="badge ${enabled ? 'badge-success' : 'badge-secondary'}">${enabled ? 'Attivo' : 'Disattivo'}</span>
                    </div>
                    <p class="plugin-description">${escapeHtml(plugin.description || 'Nessuna descrizione')}</p>
                    <div class="plugin-meta">
                        <span>Autore: ${escapeHtml(plugin.author || 'Sconosciuto')}</span>
                        <span>Installato: ${formatDate(plugin.installed_at)}</span>
                        ${plugin.permissions && plugin.permissions.length ? `<span>Permessi: ${plugin.permissions.join(', ')}</span>` : ''}
                    </div>
                    <div class="plugin-actions">
                        ${enabled ? `
                            <button class="btn btn-secondary btn-sm btn-disable-plugin" data-id="${id}">Disattiva</button>
                        ` : `
                            <button class="btn btn-primary btn-sm btn-enable-plugin" data-id="${id}">Attiva</button>
                        `}
                        <button class="btn btn-danger btn-sm btn-uninstall-plugin" data-id="${id}">Disinstalla</button>
                    </div>
                </div>
            `;
        }).join('');
        
        grid.querySelectorAll('.btn-enable-plugin').forEach(btn => {
            btn.addEventListener('click', (e) => togglePlugin(e.target.dataset.id, true));
        });
        grid.querySelectorAll('.btn-disable-plugin').forEach(btn => {
            btn.addEventListener('click', (e) => togglePlugin(e.target.dataset.id, false));
        });
        grid.querySelectorAll('.btn-uninstall-plugin').forEach(btn => {
            btn.addEventListener('click', (e) => uninstallPlugin(e.target.dataset.id));
        });
    }
    
    function bindPluginEvents() {
        const form = document.getElementById('install-plugin-form');
        if (!form) return;
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const fileInput = document.getElementById('plugin-zip');
            const file = fileInput.files[0];
            const btn = document.getElementById('install-btn');
            const resultDiv = document.getElementById('install-result');
            
            if (!file) {
                showInstallResult('Seleziona un file ZIP', 'error');
                return;
            }
            
            if (!file.name.endsWith('.zip')) {
                showInstallResult('Il file deve essere un archivio .zip', 'error');
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Installazione...';
            hideInstallResult();
            
            const formData = new FormData();
            formData.append('plugin', file);
            
            try {
                const response = await fetch(`${API_BASE}/admin/install_plugin.php`, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showInstallResult(`Plugin "${data.plugin_id}" installato con successo!`, 'success');
                    form.reset();
                    loadPlugins();
                } else {
                    showInstallResult(data.error || 'Errore installazione', 'error');
                }
            } catch (error) {
                console.error('Install plugin error:', error);
                showInstallResult('Errore di connessione', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Installa Plugin';
            }
        });
    }
    
    function togglePlugin(id, enable) {
        showToast(`${enable ? 'Attivazione' : 'Disattivazione'} plugin richiede implementazione API`, 'info');
        loadPlugins();
    }
    
    function uninstallPlugin(id) {
        if (!confirm(`Sei sicuro di voler disinstallare il plugin "${id}"?`)) return;
        showToast('Disinstallazione plugin richiede implementazione API', 'info');
    }
    
    function showInstallResult(message, type) {
        const resultDiv = document.getElementById('install-result');
        if (!resultDiv) return;
        resultDiv.textContent = message;
        resultDiv.className = `install-result alert alert-${type}`;
        resultDiv.style.display = 'block';
    }
    
    function hideInstallResult() {
        const resultDiv = document.getElementById('install-result');
        if (!resultDiv) return;
        resultDiv.style.display = 'none';
    }
    
    function formatDate(dateString) {
        if (!dateString) return '-'; 
        const date = new Date(dateString);
        return date.toLocaleDateString('it-IT', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        const colors = { success: 'var(--success)', error: 'var(--danger)', info: 'var(--primary)', warning: 'var(--warning)' };
        toast.style.cssText = `
            position: fixed; bottom: 20px; right: 20px;
            background: ${colors[type] || colors.info}; color: white;
            padding: 12px 20px; border-radius: var(--radius);
            box-shadow: var(--shadow-hover); z-index: 1000;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.animation = 'slideOut 0.3s ease'; setTimeout(() => toast.remove(), 300); }, 4000);
    }
    
    return { init, initPlugins, loadUsers, loadPlugins };
})();
