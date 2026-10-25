const API_PATH = '../../php/';

const queueElement = document.getElementById('queue');
const queueCounter = document.getElementById('queue_counter');

let lastItemsSignature = '';

function toast(message, isError = false) {
    const box = document.getElementById('chef_toasts');
    if (!box) return;

    const el = document.createElement('div');
    el.className = 'toast' + (isError ? ' error' : '');
    el.textContent = message;
    box.appendChild(el);

    setTimeout(() => el.classList.add('visible'), 10);
    setTimeout(() => {
        el.classList.remove('visible');
        setTimeout(() => el.remove(), 300);
    }, 4000);
}

function postForm(endpoint, data) {
    const formData = new FormData();

    for (const key in data) {
        formData.append(key, data[key]);
    }

    return fetch(API_PATH + endpoint, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(async (response) => {
        const payload = await response.json().catch(() => null);

        if (!response.ok || !payload || payload.success !== true) {
            if (response.status === 401 || response.status === 403) {
                window.location.href = '../index.php';
            }

            throw new Error((payload && payload.message) || 'Operation failed.');
        }

        return payload;
    });
}

function getJson(endpoint) {
    return fetch(API_PATH + endpoint, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(async (response) => {
        const payload = await response.json().catch(() => null);

        if (!response.ok || !payload || payload.success !== true) {
            if (response.status === 401 || response.status === 403) {
                window.location.href = '../index.php';
            }

            throw new Error((payload && payload.message) || 'Operation failed.');
        }

        return payload;
    });
}

function itemStatusLabel(status) {
    if (status === 'claimed') return 'In kitchen';
    if (status === 'ready') return 'Ready';
    return 'New';
}

function buildItemCard(item) {
    const card = document.createElement('div');
    card.className = 'item_card item_' + item.item_status;
    card.dataset.itemId = String(item.item_id);

    const top = document.createElement('div');
    top.className = 'item_top';

    const name = document.createElement('span');
    name.className = 'item_name';
    name.textContent = item.dish_name + ' x' + item.quantity;
    top.appendChild(name);

    const badge = document.createElement('span');
    badge.className = 'item_badge badge_' + item.item_status;
    badge.textContent = itemStatusLabel(item.item_status);
    top.appendChild(badge);

    card.appendChild(top);

    if (item.note) {
        const note = document.createElement('div');
        note.className = 'item_note';
        const noteIcon = document.createElement('i');
        noteIcon.className = 'fa-solid fa-comment-dots';
        note.appendChild(noteIcon);
        note.append(' ' + item.note);
        card.appendChild(note);
    }

    const meta = document.createElement('div');
    meta.className = 'item_meta';

    const waiter = document.createElement('span');
    waiter.innerHTML = '<i class="fa-solid fa-bell-concierge"></i> ';
    waiter.append(item.waiter_name || 'waiter');
    meta.appendChild(waiter);

    if (item.claimed_by_name) {
        const claimer = document.createElement('span');
        claimer.className = 'claimer';
        claimer.innerHTML = '<i class="fa-solid fa-user-check"></i> ';
        claimer.append(item.claimed_by_name);
        meta.appendChild(claimer);
    }

    card.appendChild(meta);

    const actions = document.createElement('div');
    actions.className = 'item_actions';

    if (item.item_status === 'new') {
        const claimButton = document.createElement('button');
        claimButton.type = 'button';
        claimButton.className = 'btn_claim';
        claimButton.dataset.action = 'claim';
        claimButton.dataset.itemId = String(item.item_id);
        claimButton.innerHTML = '<i class="fa-solid fa-hand"></i> Biorę na siebie';
        actions.appendChild(claimButton);
    }

    if (item.item_status !== 'ready') {
        const readyButton = document.createElement('button');
        readyButton.type = 'button';
        readyButton.className = 'btn_ready';
        readyButton.dataset.action = 'ready';
        readyButton.dataset.itemId = String(item.item_id);
        readyButton.innerHTML = '<i class="fa-solid fa-check"></i> Ready';
        actions.appendChild(readyButton);
    }

    card.appendChild(actions);
    return card;
}


function renderQueue(items) {
    const signature = items
        .map((item) => item.item_id + ':' + item.item_status + ':' + (item.claimed_by_name || ''))
        .join('|');

    queueCounter.innerHTML = '<i class="fa-solid fa-hourglass-half"></i> ' + items.length;

    if (signature === lastItemsSignature) return;
    lastItemsSignature = signature;

    queueElement.textContent = '';

    if (!items.length) {
        const empty = document.createElement('div');
        empty.className = 'queue_empty';
        empty.innerHTML = '<i class="fa-solid fa-face-smile"></i><br>No orders in kitchen - everything ready!';
        queueElement.appendChild(empty);
        return;
    }

    const groups = new Map();

    items.forEach((item) => {
        const key = item.table_id;
        if (!groups.has(key)) {
            groups.set(key, {
                table_number: item.table_number,
                floor_name: item.floor_name,
                items: []
            });
        }
        groups.get(key).items.push(item);
    });

    groups.forEach((group) => {
        const box = document.createElement('div');
        box.className = 'table_group';

        const header = document.createElement('div');
        header.className = 'group_header';
        header.innerHTML = '<i class="fa-solid fa-table-cells"></i> ';
        header.append('Table #' + group.table_number + ' (' + group.floor_name + ')');
        box.appendChild(header);

        const readyCount = group.items.filter((item) => item.item_status === 'ready').length;
        const progress = document.createElement('div');
        progress.className = 'group_progress';
        progress.textContent = readyCount + '/' + group.items.length + ' ready';
        header.appendChild(progress);

        group.items.forEach((item) => {
            box.appendChild(buildItemCard(item));
        });

        queueElement.appendChild(box);
    });
}

async function pollQueue(showErrors = true) {
    try {
        const payload = await getJson('get_chef_queue.php');
        renderQueue(payload.items);
    } catch (error) {
        if (showErrors) toast(error.message, true);
    }
}

queueElement.addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;

    button.disabled = true;

    try {
        const endpoint = button.dataset.action === 'claim' ? 'claim_item.php' : 'ready_item.php';
        const payload = await postForm(endpoint, { item_id: button.dataset.itemId });

        toast(payload.message);
        await pollQueue(false);
    } catch (error) {
        toast(error.message, true);
        button.disabled = false;
    }
});

pollQueue(false);
setInterval(() => pollQueue(false), 4000);

