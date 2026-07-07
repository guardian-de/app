<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<style>
    .chat-wrapper {
        display: flex;
        background: #1e293b;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.05);
        height: 75vh;
        overflow: hidden;
    }
    .users-sidebar {
        width: 300px;
        border-right: 1px solid rgba(255,255,255,0.05);
        display: flex;
        flex-direction: column;
        background: rgba(15, 23, 42, 0.2);
    }
    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .sidebar-title {
        font-size: 16px;
        font-weight: 700;
        color: white;
        margin: 0;
    }
    .users-list {
        flex: 1;
        overflow-y: auto;
    }
    .user-item {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.02);
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .user-item:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    .user-item.active {
        background: rgba(99, 102, 241, 0.15);
        border-left: 4px solid #6366f1;
    }
    .user-header-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .user-name {
        font-weight: 600;
        color: white;
        font-size: 14px;
    }
    .user-role {
        font-size: 10px;
        background: rgba(255,255,255,0.1);
        color: #cbd5e1;
        padding: 2px 6px;
        border-radius: 4px;
        text-transform: uppercase;
        font-weight: 700;
    }
    .user-last-msg {
        font-size: 12px;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: rgba(15, 23, 42, 0.1);
        overflow: hidden;
    }
    .chat-area-empty {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 15px;
        flex-direction: column;
        gap: 10px;
    }
    .chat-area-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        background: rgba(15, 23, 42, 0.1);
    }
    .chat-area-title {
        font-size: 16px;
        font-weight: 700;
        color: white;
        margin: 0;
    }
    .chat-area-subtitle {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .message-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.4;
    }
    .message-bubble.user {
        align-self: flex-start;
        background: #334155;
        color: white;
        border-bottom-left-radius: 4px;
    }
    .message-bubble.bot {
        align-self: flex-start;
        background: rgba(255, 255, 255, 0.05);
        color: #cbd5e1;
        border: 1px dashed rgba(255,255,255,0.1);
        border-bottom-left-radius: 4px;
    }
    .message-bubble.operator, .message-bubble.admin {
        align-self: flex-end;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border-bottom-right-radius: 4px;
    }
    .message-time {
        font-size: 9px;
        color: rgba(255, 255, 255, 0.4);
        margin-top: 4px;
        text-align: right;
    }
    .chat-input-container {
        padding: 20px;
        border-top: 1px solid rgba(255,255,255,0.05);
        background: rgba(15, 23, 42, 0.1);
        display: flex;
        gap: 10px;
    }
    .chat-input {
        flex: 1;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid #334155;
        border-radius: 10px;
        padding: 12px;
        color: white;
        outline: none;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    .chat-input:focus {
        border-color: #3b82f6;
    }
    .send-button {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .send-button:hover {
        background: #2563eb;
    }
</style>

<div class="header">
    <h1 style="font-size: 24px; color: white;">Suporte via Chat</h1>
</div>

<div class="chat-wrapper">
    <!-- Users Sidebar -->
    <div class="users-sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Clientes Ativos</h2>
        </div>
        <div class="users-list" id="users-list">
            <!-- Rendered dynamically -->
        </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area" id="chat-area">
        <div class="chat-area-empty" id="chat-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Selecione um cliente na lista para iniciar o atendimento</span>
        </div>
        
        <div style="display: none; flex-direction: column; flex: 1; height: 100%; overflow: hidden;" id="chat-content">
            <div class="chat-area-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(15, 23, 42, 0.1);">
                <div>
                    <h2 class="chat-area-title" id="active-user-name">Carregando...</h2>
                    <div class="chat-area-subtitle" id="active-user-role">---</div>
                </div>
                <button onclick="closeActiveChat(event)" class="btn btn-secondary" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.25)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'">
                    Fechar Conversa
                </button>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <!-- Messages rendered dynamically -->
            </div>
            
            <form class="chat-input-container" onsubmit="sendChatMessage(event)">
                <input type="text" id="chat-input-field" class="chat-input" placeholder="Digite uma resposta..." autocomplete="off" required>
                <button type="submit" class="send-button">Enviar</button>
            </form>
        </div>
    </div>
</div>

<script>
    let activeUserId = null;
    let lastMessageId = 0;
    const renderedMessageIds = new Set();
    let usersListInterval = null;
    let messagesInterval = null;

    async function loadUsers() {
        try {
            const response = await fetch('<?= url_to('admin_chat_users') ?>');
            const users = await response.json();
            const listContainer = document.getElementById('users-list');
            
            let html = '';
            users.forEach(u => {
                const isActive = activeUserId === parseInt(u.id) ? 'active' : '';
                const lastMsg = u.last_message || 'Nenhuma mensagem';
                const time = u.last_message_time ? new Date(u.last_message_time).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'}) : '';
                const badge = u.last_sender === 'user' ? '<span style="width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block; margin-left:5px;" title="Nova mensagem do cliente"></span>' : '';
                
                html += `
                    <div class="user-item ${isActive}" onclick="selectUser(${u.id}, '${u.login}', '${u.role}')">
                        <div class="user-header-info">
                            <span class="user-name">${u.login} ${badge}</span>
                            <span class="user-role">${u.role}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                            <span class="user-last-msg">${lastMsg}</span>
                            <span style="font-size:10px; color:#64748b;">${time}</span>
                        </div>
                    </div>
                `;
            });
            listContainer.innerHTML = html || '<div style="padding:20px; color:#64748b; font-size:13px; text-align:center;">Nenhum histórico de chat encontrado.</div>';
        } catch (e) {
            console.error('Error loading users:', e);
        }
    }

    function selectUser(userId, login, role) {
        if (activeUserId === userId) return;
        activeUserId = userId;
        lastMessageId = 0;
        renderedMessageIds.clear();
        
        document.getElementById('chat-empty').style.display = 'none';
        document.getElementById('chat-content').style.display = 'flex';
        document.getElementById('active-user-name').textContent = login;
        document.getElementById('active-user-role').textContent = role === 'user' ? 'Cliente' : role;
        document.getElementById('chat-messages').innerHTML = '';
        document.getElementById('chat-input-field').value = '';
        
        // Highlight active user item
        loadUsers();
        
        loadMessages();
        
        // Restart message polling
        if (messagesInterval) clearInterval(messagesInterval);
        messagesInterval = setInterval(loadMessages, 3000);
    }

    async function loadMessages() {
        if (!activeUserId) return;
        try {
            const response = await fetch(`<?= url_to('admin_chat') ?>/messages/${activeUserId}?last_id=${lastMessageId}`);
            const messages = await response.json();
            const chatMessagesContainer = document.getElementById('chat-messages');
            
            messages.forEach(msg => {
                const msgId = parseInt(msg.id);
                if (renderedMessageIds.has(msgId)) return;
                renderedMessageIds.add(msgId);
                
                if (msgId > lastMessageId) {
                    lastMessageId = msgId;
                }
                
                const bubble = document.createElement('div');
                bubble.className = `message-bubble ${msg.sender}`;
                
                // Add prefix labels for bot/support roles
                let prefix = '';
                if (msg.sender === 'bot') {
                    prefix = '🤖 <strong>Guardian IA:</strong> ';
                } else if (msg.sender === 'operator') {
                    prefix = '👤 <strong>Operador:</strong> ';
                } else if (msg.sender === 'admin') {
                    prefix = '🛡️ <strong>Admin:</strong> ';
                }
                
                bubble.innerHTML = `${prefix}${msg.message}`;
                
                const time = document.createElement('div');
                time.className = 'message-time';
                time.textContent = new Date(msg.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
                bubble.appendChild(time);
                
                chatMessagesContainer.appendChild(bubble);
            });
            
            if (messages.length > 0) {
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            }
        } catch (e) {
            console.error('Error loading messages:', e);
        }
    }

    function getCsrfToken() {
        return '<?= csrf_hash() ?>';
    }

    async function sendChatMessage(e) {
        e.preventDefault();
        if (!activeUserId) return;
        
        const inputField = document.getElementById('chat-input-field');
        const message = inputField.value.trim();
        if (!message) return;
        
        inputField.value = '';
        
        try {
            const csrfToken = getCsrfToken();
            const response = await fetch('<?= url_to('admin_chat_send') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': csrfToken
                },
                body: JSON.stringify({
                    user_id: activeUserId,
                    message: message
                })
            });
            if (response.ok) {
                loadMessages();
                loadUsers();
            }
        } catch (e) {
            console.error('Error sending message:', e);
        }
    }

    async function closeActiveChat(e) {
        if (e) e.preventDefault();
        if (!activeUserId) return;
        
        const confirmClose = confirm('Deseja realmente fechar esta conversa e limpar o histórico de chat do cliente?');
        if (!confirmClose) return;
        
        try {
            const csrfToken = getCsrfToken();
            const response = await fetch(`<?= url_to('admin_chat') ?>/close/${activeUserId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': csrfToken
                }
            });
            if (response.ok) {
                activeUserId = null;
                lastMessageId = 0;
                renderedMessageIds.clear();
                
                document.getElementById('chat-empty').style.display = 'flex';
                document.getElementById('chat-content').style.display = 'none';
                
                if (messagesInterval) clearInterval(messagesInterval);
                
                loadUsers();
            }
        } catch (e) {
            console.error('Error closing chat:', e);
        }
    }

    // Startup
    loadUsers();
    usersListInterval = setInterval(loadUsers, 5000);
</script>
<?= $this->endSection() ?>
