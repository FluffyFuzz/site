import { request } from './ajax.js';
import { toast } from './toaster.js';

const POLL_INTERVAL_MS = 5000;

const messagesEl = document.getElementById('chat-messages');
const inputEl    = document.getElementById('chat-input');
const sendBtn    = document.getElementById('chat-send-btn');

// ── Utilitaires ──────────────────────────────────────────────────────────────

function fmtDate(dateStr) {
    const d   = new Date(dateStr);
    const now = new Date();
    const time = d.getHours().toString().padStart(2, '0') + ':' +
                 d.getMinutes().toString().padStart(2, '0');
    if (d.toDateString() === now.toDateString()) return time;
    return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + time;
}

// ── Rendu des messages ────────────────────────────────────────────────────────

function renderMessages(messages) {
    const wasAtBottom = messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 60;

    messages.forEach(msg => {
        const row = document.createElement('div');
        row.className = 'msg-row ' + (msg.is_mine ? 'mine' : 'other');

        const meta = document.createElement('div');
        meta.className = 'msg-meta';
        meta.textContent = (msg.is_mine ? '' : msg.auteur + ' · ') + fmtDate(msg.date_message);

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.textContent = msg.contenu;

        row.appendChild(meta);
        row.appendChild(bubble);
        messagesEl.appendChild(row);
    });

    if (wasAtBottom) messagesEl.scrollTop = messagesEl.scrollHeight;
}

async function fetchMessages() {
    try {
        const msgs = await request('/chat.php', 'GET');
        if (Array.isArray(msgs)) {
            messagesEl.innerHTML = '';
            renderMessages(msgs);
        }
    } catch (e) {
        console.warn('Chat poll error:', e);
    }
}

// ── Envoi ─────────────────────────────────────────────────────────────────────

async function sendMessage() {
    const contenu = inputEl.value.trim();
    if (!contenu) return;

    inputEl.disabled = true;
    sendBtn.disabled = true;

    try {
        await request('/chat.php', 'POST', { contenu });
        inputEl.value = '';
        await fetchMessages();
    } catch (e) {
        toast(e.message, true);
    }

    inputEl.disabled = false;
    sendBtn.disabled = false;
    inputEl.focus();
}

sendBtn.addEventListener('click', sendMessage);
inputEl.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

fetchMessages();
setInterval(fetchMessages, POLL_INTERVAL_MS);
