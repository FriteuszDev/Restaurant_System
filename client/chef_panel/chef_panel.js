const API_PATH = '../../php/';

const cookElement = document.getElementById('queue_cook');
const readyElement = document.getElementById('queue_ready');
const cookCounter = document.getElementById('cook_counter');
const readyCounter = document.getElementById('ready_counter');

// no signature cache - always render to reflect DB state instantly

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
        cache: 'no-store',
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
        cache: 'no-store',
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

function buildItemCard(item, columnType) {
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

    if (columnType === 'cook') {
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
    }

    card.appendChild(actions);
    return card;
}


function renderColumn(element, counter, items, columnType) {
    counter.innerHTML = '<i class="fa-solid fa-hourglass-half"></i> ' + items.length;

    element.textContent = '';

    if (!items.length) {
        const empty = document.createElement('div');
        empty.className = 'queue_empty';
        if (columnType === 'cook') {
            empty.innerHTML = '<i class="fa-solid fa-face-smile"></i><br>Brak zamówień do przygotowania!';
        } else {
            empty.innerHTML = '<i class="fa-solid fa-face-smile"></i><br>Brak dań gotowych do odbioru!';
        }
        element.appendChild(empty);
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
        header.append('Stolik #' + group.table_number + ' (' + group.floor_name + ')');
        box.appendChild(header);

        group.items.forEach((item) => {
            box.appendChild(buildItemCard(item, columnType));
        });

        element.appendChild(box);
    });
}

function renderQueue(toCook, readyForPickup) {
    renderColumn(cookElement, cookCounter, toCook, 'cook');
    renderColumn(readyElement, readyCounter, readyForPickup, 'ready');
}

async function pollQueue(showErrors = true) {
    try {
        const payload = await getJson('get_chef_queue.php');
        renderQueue(payload.to_cook || [], payload.ready_for_pickup || []);
    } catch (error) {
        if (showErrors) toast(error.message, true);
    }
}

document.getElementById('queue_split').addEventListener('click', async (event) => {
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
setInterval(() => pollQueue(false), 2000);

