/**
 * Knowledge Base Akademik TI - Main JavaScript
 * Vanilla JS UI, chatbot, and administration interactions.
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ============================================================
    // Shared helpers: toast and confirmation modal
    // ============================================================
    const toastIcons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    window.showToast = function (message, type) {
        type = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
        const oldToast = document.querySelector('.toast-notification');
        if (oldToast) oldToast.remove();

        const toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + type;
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<i class="fas ' + toastIcons[type] + '"></i><span>' +
            escapeHtml(String(message)) + '</span>';
        document.body.appendChild(toast);

        requestAnimationFrame(function () { toast.classList.add('show'); });
        setTimeout(function () {
            toast.classList.remove('show');
            toast.style.opacity = '0';
            setTimeout(function () { toast.remove(); }, 400);
        }, 3000);
    };

    window.showModal = function (title, message, onConfirm) {
        const existing = document.querySelector('.modal-overlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = '<div class="modal-content" role="dialog" aria-modal="true">' +
            '<button type="button" class="modal-close" aria-label="Tutup">&times;</button>' +
            '<h3>' + escapeHtml(String(title)) + '</h3>' +
            '<p>' + escapeHtml(String(message)) + '</p>' +
            '<div class="modal-actions"><button type="button" class="modal-cancel">Batal</button>' +
            '<button type="button" class="modal-confirm">Konfirmasi</button></div></div>';
        document.body.appendChild(overlay);
        requestAnimationFrame(function () { overlay.classList.add('show'); });

        const close = function () {
            overlay.classList.remove('show');
            setTimeout(function () { overlay.remove(); }, 250);
        };
        overlay.querySelector('.modal-cancel').addEventListener('click', close);
        overlay.querySelector('.modal-close').addEventListener('click', close);
        overlay.querySelector('.modal-confirm').addEventListener('click', function () {
            if (typeof onConfirm === 'function') onConfirm();
            close();
        });
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) close();
        });
        return overlay;
    };

    // escapeHtml intentionally preserves the existing implementation.
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }

    // ============================================================
    // Mobile navbar
    // ============================================================
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    const setNavState = function (open) {
        if (!navLinks) return;
        navLinks.classList.toggle('open', open);
        navLinks.classList.toggle('is-open', open);
        if (navToggle) {
            navToggle.setAttribute('aria-expanded', String(open));
            const icon = navToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars', !open);
                icon.classList.toggle('fa-times', open);
            }
        }
    };
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            setNavState(!navLinks.classList.contains('open'));
        });
        navLinks.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () { setNavState(false); });
        });
        document.addEventListener('click', function (event) {
            if (navLinks.classList.contains('open') &&
                !navLinks.contains(event.target) && !navToggle.contains(event.target)) setNavState(false);
        });
    }

    // ============================================================
    // Dark mode
    // ============================================================
    const root = document.documentElement;
    const themeToggle = document.querySelector('.theme-toggle');
    const applyTheme = function (theme) {
        const dark = theme === 'dark';
        root.setAttribute('data-theme', dark ? 'dark' : 'light');
        if (themeToggle) {
            const icon = themeToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-moon', !dark);
                icon.classList.toggle('fa-sun', dark);
            }
            themeToggle.setAttribute('aria-label', dark ? 'Gunakan mode terang' : 'Gunakan mode gelap');
        }
    };
    applyTheme(localStorage.getItem('kb_theme') || 'light');
    if (themeToggle) themeToggle.addEventListener('click', function () {
        const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('kb_theme', next);
        applyTheme(next);
    });

    // ============================================================
    // Chatbot engine (PHP AJAX endpoints intentionally unchanged)
    // ============================================================
    const chatInput = document.getElementById('chatInput');
    const chatSendBtn = document.getElementById('chatSendBtn');
    const chatBody = document.getElementById('chatBody');
    const typingIndicator = document.getElementById('typingIndicator');
    const quickButtons = document.querySelectorAll('.quick-q-btn');
    let sessionCode = localStorage.getItem('kb_chat_session_code') || '';

    function scrollToBottom(smooth) {
        if (!chatBody) return;
        chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
    }
    function updateScrollButton() {
        const button = document.querySelector('.scroll-to-bottom');
        if (!button || !chatBody) return;
        const atBottom = chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 30;
        button.classList.toggle('visible', !atBottom && chatBody.scrollHeight > chatBody.clientHeight);
    }
    function showTypingIndicator() {
        if (!chatBody || document.getElementById('typingIndicator')) return;
        const indicator = document.createElement('div');
        indicator.id = 'typingIndicator';
        indicator.className = 'typing-indicator';
        indicator.innerHTML = '<div class="msg-avatar"><i class="fas fa-robot"></i></div>' +
            '<div class="typing-dots"><span></span><span></span><span></span></div>';
        chatBody.appendChild(indicator);
    }
    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
    }
    function setChatInputDisabled(disabled) {
        if (chatInput) chatInput.disabled = disabled;
        if (chatSendBtn) chatSendBtn.disabled = disabled;
        if (!disabled && chatInput) chatInput.focus();
    }
    function appendUserMessage(text, time) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-message user';
        msgDiv.innerHTML = '<div class="msg-avatar"><i class="fas fa-user"></i></div>' +
            '<div class="msg-bubble"><p>' + escapeHtml(text) + '</p><div class="msg-time">' +
            escapeHtml(String(time || '')) + '</div></div>';
        chatBody.appendChild(msgDiv);
    }
    function appendBotMessage(text, time, chatId, status, confidence, existingRating, sources) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-message bot' + (status === 'error' ? ' message-error' : '');
        const formattedText = escapeHtml(String(text || '')).replace(/\n/g, '<br>');
        let sourcesHtml = '';
        if (Array.isArray(sources) && sources.length) {
            sourcesHtml = '<div class="chat-sources"><strong><i class="fas fa-book"></i> Sumber knowledge:</strong><ul>' + sources.map(function (source) {
                var link = source.source_url ? ' <a href="' + escapeHtml(String(source.source_url)) + '" target="_blank" rel="noopener noreferrer">Buka sumber</a>' : '';
                return '<li>' + escapeHtml(String(source.category || '')) + ' — ' + escapeHtml(String(source.question || '')) + link + '</li>';
            }).join('') + '</ul></div>';
        }
        let feedbackHtml = '';
        if (chatId) {
            const likeActive = existingRating === 'like' ? 'active like' : '';
            const dislikeActive = existingRating === 'dislike' ? 'active dislike' : '';
            feedbackHtml = '<div class="chat-feedback" data-chat-id="' + escapeHtml(String(chatId)) + '">' +
                '<span>Apakah jawaban ini membantu?</span>' +
                '<button class="fb-btn ' + likeActive + '" data-rating="like" title="Membantu"><i class="fas fa-thumbs-up"></i> Ya</button>' +
                '<button class="fb-btn ' + dislikeActive + '" data-rating="dislike" title="Kurang Membantu"><i class="fas fa-thumbs-down"></i> Tidak</button></div>';
        }
        msgDiv.innerHTML = '<div class="msg-avatar"><i class="fas fa-robot"></i></div><div class="msg-bubble">' +
            '<div class="msg-content">' + formattedText + '</div>' + sourcesHtml +
            '<button type="button" class="copy-btn" title="Salin jawaban"><i class="fas fa-copy"></i> Salin</button>' +
            feedbackHtml + '<div class="msg-time">' + escapeHtml(String(time || '')) + '</div></div>';
        chatBody.appendChild(msgDiv);
        msgDiv.querySelector('.copy-btn').addEventListener('click', function () {
            const content = msgDiv.querySelector('.msg-content').innerText;
            const copy = navigator.clipboard ? navigator.clipboard.writeText(content) : Promise.reject();
            copy.then(function () { showToast('Jawaban berhasil disalin!', 'success'); })
                .catch(function () { showToast('Gagal menyalin jawaban.', 'error'); });
        });
        msgDiv.querySelectorAll('.fb-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                sendFeedback(chatId, button.getAttribute('data-rating'), msgDiv.querySelector('.chat-feedback'));
            });
        });
    }
    function sendFeedback(chatId, rating, fbContainer) {
        fetch('../chatbot/feedback.php', { method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: chatId, rating: rating }) })
            .then(function (res) { return res.json(); }).then(function (data) {
                if (data.success) {
                    fbContainer.querySelectorAll('.fb-btn').forEach(function (button) {
                        button.classList.remove('active', 'like', 'dislike');
                        if (button.getAttribute('data-rating') === rating) button.classList.add('active', rating);
                    });
                    showToast('Terima kasih atas feedback Anda!', 'success');
                } else showToast(data.message || 'Gagal mengirim feedback.', 'error');
            }).catch(function (err) { console.error('Feedback error:', err); showToast('Gagal terhubung ke server.', 'error'); });
    }
    function loadChatHistory(code) {
        fetch('../chatbot/get_history.php?session_code=' + encodeURIComponent(code))
            .then(function (res) { return res.json(); }).then(function (data) {
                if (data.success && data.history && data.history.length > 0) {
                    const welcome = chatBody.querySelector('.chat-welcome');
                    if (welcome) welcome.remove();
                    data.history.forEach(function (item) {
                        appendUserMessage(item.user_message, item.time);
                        appendBotMessage(item.bot_answer, item.time, item.chat_id, item.status, item.confidence, item.feedback_rating, item.sources || []);
                    });
                    scrollToBottom(false);
                }
            }).catch(function (err) { console.error('History load error:', err); });
    }
    function sendChatMessage() {
        const text = chatInput.value.trim();
        if (!text || chatInput.disabled) return;
        chatInput.value = '';
        chatInput.dispatchEvent(new Event('input'));
        setChatInputDisabled(true);
        const timeNow = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const welcome = chatBody.querySelector('.chat-welcome');
        if (welcome) welcome.remove();
        appendUserMessage(text, timeNow);
        showTypingIndicator();
        scrollToBottom(false);
        fetch('../chatbot/process.php', { method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, session_code: sessionCode }) })
            .then(function (res) { return res.json(); }).then(function (data) {
                removeTypingIndicator(); setChatInputDisabled(false);
                if (data.success) {
                    if (data.session_code) { sessionCode = data.session_code; localStorage.setItem('kb_chat_session_code', sessionCode); }
                    appendBotMessage(data.bot_answer, data.created_at || timeNow, data.chat_id, data.status, data.confidence, null, data.sources || []);
                } else appendBotMessage('Maaf, terjadi kendala sistem saat memproses pertanyaanmu.', timeNow, null, 'error', 0, null);
                scrollToBottom(false);
            }).catch(function (err) {
                console.error('Chat error:', err); removeTypingIndicator(); setChatInputDisabled(false);
                appendBotMessage('Maaf, tidak dapat terhubung ke server. Pastikan koneksi XAMPP/Database aktif.', timeNow, null, 'error', 0, null);
                scrollToBottom(false);
            });
    }
    if (chatInput && chatBody) {
        if (sessionCode) loadChatHistory(sessionCode);
        if (chatSendBtn) chatSendBtn.addEventListener('click', sendChatMessage);
        chatInput.addEventListener('keydown', function (event) { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendChatMessage(); } });
        quickButtons.forEach(function (button) { button.addEventListener('click', function () { chatInput.value = button.getAttribute('data-q') || button.innerText.trim(); sendChatMessage(); }); });
        chatBody.addEventListener('scroll', updateScrollButton);
        const scrollButton = document.querySelector('.scroll-to-bottom');
        if (scrollButton) scrollButton.addEventListener('click', function () { scrollToBottom(true); });

        // Character counter: display only; input length is not restricted.
        let counter = document.querySelector('.chat-character-count');
        if (!counter) { counter = document.createElement('small'); counter.className = 'chat-character-count'; chatInput.parentNode.appendChild(counter); }
        const updateCounter = function () { counter.textContent = chatInput.value.length + ' karakter'; };
        chatInput.addEventListener('input', updateCounter); updateCounter();
    }

    // ============================================================
    // Clear chat conversation
    // ============================================================
    document.querySelectorAll('.clear-chat-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            showModal('Hapus percakapan?', 'Semua riwayat percakapan ini akan dihapus dari tampilan.', function () {
                if (chatBody) {
                    chatBody.innerHTML = '<div class="chat-welcome"><div class="welcome-icon"><i class="fas fa-robot"></i></div><p>Halo! Ada yang bisa saya bantu?</p></div>';
                    scrollToBottom(false);
                }
                sessionCode = '';
                localStorage.removeItem('kb_chat_session_code');
                showToast('Percakapan berhasil dihapus.', 'success');
            });
        });
    });

    // ============================================================
    // Admin sidebar (mobile)
    // ============================================================
    const sidebar = document.querySelector('.admin-sidebar');
    const sidebarToggle = document.querySelector('.admin-sidebar-toggle');
    let sidebarOverlay = document.querySelector('.admin-sidebar-overlay');
    if (sidebar && sidebarToggle) {
        if (!sidebarOverlay) { sidebarOverlay = document.createElement('div'); sidebarOverlay.className = 'admin-sidebar-overlay'; document.body.appendChild(sidebarOverlay); }
        const setSidebar = function (open) { sidebar.classList.toggle('open', open); sidebarOverlay.classList.toggle('open', open); document.body.classList.toggle('sidebar-open', open); };
        sidebarToggle.addEventListener('click', function () { setSidebar(!sidebar.classList.contains('open')); });
        sidebarOverlay.addEventListener('click', function () { setSidebar(false); });
    }

    // ============================================================
    // Animations, password visibility, responsive tables, flash alerts
    // ============================================================
    const animated = document.querySelectorAll('.animate-fade-in-up');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries, obs) { entries.forEach(function (entry) { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); obs.unobserve(entry.target); } }); }, { threshold: 0.1 });
        animated.forEach(function (element) { observer.observe(element); });
    } else animated.forEach(function (element) { element.classList.add('is-visible'); });

    document.querySelectorAll('.password-toggle, [data-password-toggle]').forEach(function (toggle) {
        let input = document.getElementById(toggle.getAttribute('data-target'));
        if (!input) input = toggle.closest('.password-wrapper, .password-field, .input-group')?.querySelector('input[type="password"], input[type="text"]');
        if (!input) return;
        toggle.addEventListener('click', function () {
            const visible = input.type === 'text'; input.type = visible ? 'password' : 'text';
            const icon = toggle.querySelector('i');
            if (icon) { icon.classList.toggle('fa-eye', visible); icon.classList.toggle('fa-eye-slash', !visible); }
        });
    });

    document.querySelectorAll('table').forEach(function (table) {
        if (table.classList.contains('knowledge-table') || table.closest('.admin-content, .admin-page')) {
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div'); wrapper.className = 'table-responsive'; table.parentNode.insertBefore(wrapper, table); wrapper.appendChild(table);
            }
        }
    });
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () { alert.classList.add('fade-out'); setTimeout(function () { alert.remove(); }, 500); }, 5000);
    });
});
