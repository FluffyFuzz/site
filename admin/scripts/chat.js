import { request } from './ajax.js';
import { toast } from './toaster.js';

const CONVERSATION_ID  = 1; // Chat admin général — les tickets futurs auront leur propre id
const POLL_INTERVAL_MS = 5000;

const messagesEl = document.getElementById('chat-messages');
const inputEl    = document.getElementById('chat-input');
const sendBtn    = document.getElementById('chat-send-btn');

let lastMessageId = 0;

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

function appendMessages(messages) {
    const empty = document.getElementById('chat-empty');
    if (empty && messages.length > 0) empty.remove();

    const wasAtBottom = messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 60;

    messages.forEach(msg => {
        const row = document.createElement('div');
        row.className = 'msg-row ' + (msg.is_mine ? 'mine' : 'other');

        const meta = document.createElement('div');
        meta.className = 'msg-meta';
        meta.textContent = (msg.is_mine ? '' : msg.auteur + ' · ') + fmtDate(msg.date_message);

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.textContent = msg.contenu; // textContent échappe le HTML

        row.appendChild(meta);
        row.appendChild(bubble);
        messagesEl.appendChild(row);

        lastMessageId = Math.max(lastMessageId, parseInt(msg.id_message));
    });

    if (wasAtBottom) messagesEl.scrollTop = messagesEl.scrollHeight;
}

// ── Chargement & polling ──────────────────────────────────────────────────────

async function fetchMessages(after = 0) {
    try {
        const msgs = await request(
            `/chat.php?id_conversation=${CONVERSATION_ID}&after=${after}`, 'GET'
        );
        if (Array.isArray(msgs) && msgs.length > 0) appendMessages(msgs);
    } catch (e) {
        console.warn('Chat poll error:', e);
    }
}

async function initialLoad() {
    await fetchMessages(0);

    if (messagesEl.children.length === 0) {
        const empty = document.createElement('p');
        empty.id = 'chat-empty';
        empty.textContent = "Aucun message pour l'instant.";
        messagesEl.appendChild(empty);
    }

    setInterval(() => fetchMessages(lastMessageId), POLL_INTERVAL_MS);
}

// ── Envoi ─────────────────────────────────────────────────────────────────────

async function sendMessage() {
    const contenu = inputEl.value.trim();
    if (!contenu) return;

    inputEl.disabled = true;
    sendBtn.disabled = true;

    try {
        await request('/chat.php', 'POST', { id_conversation: CONVERSATION_ID, contenu });
        inputEl.value = '';
        await fetchMessages(lastMessageId);
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

initialLoad();
