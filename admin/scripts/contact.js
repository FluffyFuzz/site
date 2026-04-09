import { request } from './ajax.js';
import { toast } from './toaster.js';

const list  = document.getElementById('contact-list');
const empty = document.getElementById('contact-empty');

function fmtDate(str) {
    const d = new Date(str);
    return d.toLocaleDateString('fr-FR') + ' ' +
           d.getHours().toString().padStart(2, '0') + ':' +
           d.getMinutes().toString().padStart(2, '0');
}

function esc(s) {
    const d = document.createElement('div');
    d.textContent = s ?? '';
    return d.innerHTML;
}

async function saveReply(id, textarea, btn) {
    btn.disabled = true;
    try {
        await request('/contact.php', 'PATCH', { id_contact: id, reponse_faq: textarea.value.trim() });
        toast('Réponse sauvegardée');
    } catch (e) {
        toast(e.message, true);
    }
    btn.disabled = false;
}

async function load() {
    const messages = await request('/contact.php', 'GET');

    if (!messages.length) {
        empty.hidden = false;
        return;
    }

    messages.forEach(m => {
        const card = document.createElement('div');
        card.className = 'contact-card' + (m.lu_contact == 0 ? ' unread' : '');

        card.innerHTML = `
            <div class="contact-meta">
                ${fmtDate(m.date_contact)} —
                <strong>${esc(m.prenom_contact)} ${esc(m.nom_contact)}</strong>
                &lt;<a href="mailto:${esc(m.email_contact)}">${esc(m.email_contact)}</a>&gt;
                ${m.reponse_faq ? '<span class="faq-badge">✓ Publié en FAQ</span>' : ''}
            </div>
            <div class="contact-objet">${esc(m.objet_contact)}</div>
            <div class="contact-message">${esc(m.message_contact)}</div>
            <div class="contact-reply">
                <textarea placeholder="Répondre publiquement (visible dans la FAQ)…">${esc(m.reponse_faq ?? '')}</textarea>
                <button class="btn-transparent btn-blue">Publier en FAQ</button>
            </div>
        `;

        const btn      = card.querySelector('button');
        const textarea = card.querySelector('textarea');
        btn.addEventListener('click', () => saveReply(m.id_contact, textarea, btn));

        list.appendChild(card);
    });
}

load();
