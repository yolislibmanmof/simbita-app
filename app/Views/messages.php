<?php
declare(strict_types=1);
$me = Auth::id();
$myName = Auth::user()['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat Bimbingan - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .ms-view{animation:msFade .4s ease}@keyframes msFade{from{opacity:0}to{opacity:1}}
        .ms-header{margin-bottom:1.5rem}
        .ms-header-title{display:flex;align-items:center;gap:.75rem;font-size:1.6rem;font-weight:800;color:var(--gray-900);margin:0}
        .ms-header-icon{width:44px;height:44px;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;color:#4f46e5;font-size:1.35rem}
        .ms-layout{display:grid;grid-template-columns:300px 1fr;gap:1.25rem;height:calc(100vh - 220px);min-height:480px}
        @media(max-width:900px){.ms-layout{grid-template-columns:1fr;height:auto}}
        .ms-side{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);overflow-y:auto}
        .ms-side-head{padding:1rem 1.25rem;border-bottom:1px solid var(--gray-200);font-weight:700;font-size:.85rem;color:var(--gray-700);text-transform:uppercase;letter-spacing:.04em}
        .ms-partner{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.25rem;border-bottom:1px solid var(--gray-100);text-decoration:none;color:inherit;transition:background .2s}
        .ms-partner:hover{background:var(--gray-50)}
        .ms-partner.is-active{background:#eef2ff;border-left:3px solid #6366f1}
        .ms-avatar{width:38px;height:38px;border-radius:var(--radius-md);background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-weight:800;font-size:.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .ms-partner-name{font-weight:700;font-size:.875rem;color:var(--gray-900);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ms-partner-sub{font-size:.72rem;color:var(--gray-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ms-unread{margin-left:auto;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:#ef4444;color:#fff;font-size:.65rem;font-weight:800;display:flex;align-items:center;justify-content:center}
        .ms-main{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);display:flex;flex-direction:column;overflow:hidden}
        .ms-main-head{padding:1rem 1.5rem;border-bottom:1px solid var(--gray-200);background:linear-gradient(to right,var(--gray-50),#fff)}
        .ms-main-title{font-weight:700;color:var(--gray-900);font-size:.95rem}
        .ms-main-sub{font-size:.78rem;color:var(--gray-500)}
        .ms-thread{flex:1;overflow-y:auto;padding:1.5rem;display:flex;flex-direction:column;gap:.75rem;background:#f9fafb}
        .ms-bubble{max-width:70%;padding:.7rem 1rem;border-radius:var(--radius-xl);font-size:.875rem;line-height:1.5;position:relative;animation:msPop .25s ease}
        @keyframes msPop{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
        .ms-bubble.mine{align-self:flex-end;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border-bottom-right-radius:4px}
        .ms-bubble.theirs{align-self:flex-start;background:#fff;border:1px solid var(--gray-200);color:var(--gray-800);border-bottom-left-radius:4px}
        .ms-bubble-time{display:block;font-size:.65rem;opacity:.7;margin-top:.3rem;font-family:'JetBrains Mono',monospace}
        .ms-empty{flex:1;display:flex;align-items:center;justify-content:center;color:var(--gray-400);font-size:.9rem;flex-direction:column;gap:.5rem}
        .ms-empty i{font-size:2.5rem;opacity:.4}
        .ms-compose{padding:1rem 1.25rem;border-top:1px solid var(--gray-200);display:flex;gap:.75rem;background:#fff}
        .ms-input{flex:1;padding:.75rem 1rem;border:1.5px solid var(--gray-300);border-radius:var(--radius-full);font-size:.9rem;font-family:inherit}
        .ms-input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,.12)}
        .ms-send{width:46px;height:46px;border:none;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-size:1.1rem;cursor:pointer;transition:all .25s;flex-shrink:0}
        .ms-send:hover{transform:scale(1.08)}
        .ms-send:disabled{opacity:.5;cursor:not-allowed}
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/navbar.php'; ?>
    <div class="container ms-view">
        <div class="ms-header">
            <h1 class="ms-header-title"><span class="ms-header-icon"><i class="bi bi-chat-dots-fill"></i></span> Chat Bimbingan</h1>
        </div>

        <div class="ms-layout">
            <div class="ms-side">
                <div class="ms-side-head">Daftar Percakapan</div>
                <?php if (empty($partners)): ?>
                    <div style="padding:2rem 1.25rem;color:var(--gray-400);font-size:.85rem;text-align:center;">Belum ada rekan bimbingan.</div>
                <?php else: foreach ($partners as $p): ?>
                    <a href="?work=<?= (int)$p['work_id'] ?>" class="ms-partner <?= $partner && (int)$p['work_id'] === $workId ? 'is-active' : '' ?>">
                        <div class="ms-avatar"><?= e(strtoupper(substr($p['partner_name'], 0, 1))) ?></div>
                        <div style="min-width:0;flex:1;">
                            <div class="ms-partner-name"><?= e($p['partner_name']) ?></div>
                            <div class="ms-partner-sub"><?= e(mb_substr($p['title'], 0, 34)) ?></div>
                        </div>
                        <?php if ($p['unread'] > 0 && !($partner && (int)$p['work_id'] === $workId)): ?><span class="ms-unread"><?= $p['unread'] ?></span><?php endif; ?>
                    </a>
                <?php endforeach; endif; ?>
            </div>

            <div class="ms-main">
                <?php if (!$partner): ?>
                    <div class="ms-empty"><i class="bi bi-chat-square-dots"></i> Pilih percakapan di sebelah kiri untuk mulai.</div>
                <?php else: ?>
                    <div class="ms-main-head">
                        <div class="ms-main-title"><?= e($partner['partner_name']) ?> <?= e($partner['partner_meta'] ?? '') ?></div>
                        <div class="ms-main-sub"><?= e($partner['title']) ?></div>
                    </div>
                    <div class="ms-thread" id="msThread">
                        <?php if (empty($messages)): ?>
                            <div class="ms-empty" id="msThreadEmpty"><i class="bi bi-emoji-smile"></i> Belum ada pesan. Sapa rekan bimbingan Anda!</div>
                        <?php else: foreach ($messages as $m): ?>
                            <div class="ms-bubble <?= (int)$m['sender_id'] === $me ? 'mine' : 'theirs' ?>">
                                <?= e($m['message']) ?>
                                <span class="ms-bubble-time"><?= date('d/m H:i', strtotime($m['created_at'])) ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <form class="ms-compose" id="msForm">
                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                        <input type="hidden" name="work_id" value="<?= $workId ?>">
                        <input type="text" name="message" id="msInput" class="ms-input" placeholder="Tulis pesan..." autocomplete="off" required>
                        <button type="submit" class="ms-send" id="msSendBtn"><i class="bi bi-send-fill"></i></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <?php if ($partner): ?>
    <script>
    (function(){
        const BASE = '<?= BASE_URL ?>';
        const WORK = <?= $workId ?>;
        const thread = document.getElementById('msThread');
        const form = document.getElementById('msForm');
        const input = document.getElementById('msInput');
        let lastId = <?= (int)($messages[count($messages)-1]['id'] ?? 0) ?>;

        const scrollDown = () => { thread.scrollTop = thread.scrollHeight; };
        scrollDown();

        const addBubble = (text, mine, time) => {
            const empty = document.getElementById('msThreadEmpty');
            if (empty) empty.remove();
            const div = document.createElement('div');
            div.className = 'ms-bubble ' + (mine ? 'mine' : 'theirs');
            div.textContent = text;
            const t = document.createElement('span');
            t.className = 'ms-bubble-time';
            t.textContent = time;
            div.appendChild(t);
            thread.appendChild(div);
            scrollDown();
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            const btn = document.getElementById('msSendBtn');
            btn.disabled = true;
            const fd = new FormData(form);
            try {
                const res = await fetch(BASE + '/messages/send', { method: 'POST', body: fd, credentials: 'same-origin' });
                const data = await res.json();
                if (data.success) {
                    addBubble(text, true, 'baru saja');
                    lastId = Math.max(lastId, data.id);
                    input.value = '';
                } else { alert(data.error || 'Gagal mengirim.'); }
            } catch (err) { alert('Error jaringan.'); }
            btn.disabled = false;
            input.focus();
        });

        // Polling pesan baru tiap 5 detik
        setInterval(async () => {
            try {
                const res = await fetch(BASE + '/api/messages?work=' + WORK + '&after=' + lastId, { credentials: 'same-origin' });
                const data = await res.json();
                if (data.success && data.messages.length) {
                    data.messages.forEach(m => {
                        addBubble(m.message, false, new Date(m.created_at).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }));
                        lastId = Math.max(lastId, m.id);
                    });
                }
            } catch (e) {}
        }, 5000);
    })();
    </script>
    <?php endif; ?>
</body>
</html>