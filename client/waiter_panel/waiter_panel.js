const API_PATH = '../../php/';

const state = {
    floors: (window.INITIAL && window.INITIAL.floors) || [],
    menu: (window.INITIAL && window.INITIAL.menu) || [],
    sessions: [],
    items: [],
    openTableId: null,
    openSession: null,
    openOrders: [],
    openSummary: null,
    cart: [],
    itemStatusCache: {}
};

function toast(message, isError = false) {
    const box = document.getElementById('waiter_toasts');
    if (!box) return;

    const el = document.createElement('div');
    el.className = 'toast' + (isError ? ' error' : '');
    el.textContent = message;
    box.appendChild(el);

    setTimeout(() => el.classList.add('visible'), 10);
    setTimeout(() => {
        el.classList.remove('visible');
        setTimeout(() => el.remove(), 300);
    }, 4500);
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

function findTable(tableId) {
    for (const floor of state.floors) {
        for (const table of floor.tables) {
            if (table.table_id === Number(tableId)) {
                return { table, floor };
            }
        }
    }

    return null;
}

function sessionForTable(tableId) {
    return state.sessions.find((session) => session.table_id === Number(tableId)) || null;
}

function itemsForSession(sessionId) {
    return state.items.filter((item) => item.session_id === Number(sessionId));
}

function statusLabel(status) {
    if (status === 'occupied') return 'Occupied';
    if (status === 'to_pay') return 'To pay';
    return 'Free';
}

function itemStatusLabel(status) {
    if (status === 'claimed') return 'In kitchen';
    if (status === 'ready') return 'Ready';
    return 'New';
}

function currentTableStatus(tableId) {
    const found = findTable(tableId);
    if (!found) return 'free';

    const session = sessionForTable(tableId);
    if (session) return session.status === 'to_pay' ? 'to_pay' : 'occupied';

    return 'free';
}


function renderSidebar() {
    const container = document.getElementById('floors_container');
    container.textContent = '';

    if (!state.floors.length) {
        const empty = document.createElement('div');
        empty.className = 'floors_empty';
        empty.textContent = 'No floors. Add them in Config panel.';
        container.appendChild(empty);
        return;
    }

    state.floors.forEach((floor) => {
        const block = document.createElement('div');
        block.className = 'floor_block';

        const header = document.createElement('div');
        header.className = 'floor_header';
        const headerIcon = document.createElement('i');
        headerIcon.className = 'fa-solid fa-building';
        header.appendChild(headerIcon);
        header.append(' ' + floor.floor_name);
        block.appendChild(header);

        const tablesBox = document.createElement('div');
        tablesBox.className = 'floor_tables';

        if (!floor.tables.length) {
            const none = document.createElement('div');
            none.className = 'floors_empty';
            none.textContent = 'Brak stolików na tym piętrze.';
            tablesBox.appendChild(none);
        }

        floor.tables.forEach((table) => {
            const status = currentTableStatus(table.table_id);
            const card = document.createElement('div');
            card.className = 'table_card status_' + status + (state.openTableId === table.table_id ? ' selected' : '');
            card.dataset.tableId = String(table.table_id);

            const number = document.createElement('div');
            number.className = 'table_card_number';
            number.textContent = '#' + table.table_number;

            const chairs = document.createElement('div');
            chairs.className = 'table_card_chairs';
            const chairIcon = document.createElement('i');
            chairIcon.className = 'fa-solid fa-chair';
            chairs.appendChild(chairIcon);
            chairs.append(' ' + table.chairs);

            const badge = document.createElement('div');
            badge.className = 'table_card_status';
            badge.textContent = statusLabel(status);

            card.append(number, chairs, badge);
            tablesBox.appendChild(card);
        });

        block.appendChild(tablesBox);
        container.appendChild(block);
    });
}

///////////////////////////////////////////////////////////////
// Widok stolika

const emptyHint = document.getElementById('empty_hint');
const tableView = document.getElementById('table_view');
const ordersList = document.getElementById('orders_list');
const menuPanel = document.getElementById('menu_panel');
const menuList = document.getElementById('menu_list');
const cartList = document.getElementById('cart_list');
const sendOrderButton = document.getElementById('btn_send_order');
const closeTableFooter = document.getElementById('table_view_footer');
const toPayPanel = document.getElementById('to_pay_panel');

function closeTableView() {
    state.openTableId = null;
    state.openSession = null;
    state.openOrders = [];
    state.openSummary = null;
    state.cart = [];

    tableView.classList.add('hidden');
    emptyHint.classList.remove('hidden');
    renderSidebar();
}

function renderOrders() {
    ordersList.textContent = '';

    if (!state.openOrders.length) {
        const empty = document.createElement('div');
        empty.className = 'orders_empty';
        empty.textContent = 'Brak zamówień - dodaj dania z menu obok.';
        ordersList.appendChild(empty);
        return;
    }

    state.openOrders.forEach((order) => {
        const orderBox = document.createElement('div');
        orderBox.className = 'order_box';

        const orderHeader = document.createElement('div');
        orderHeader.className = 'order_header';
        orderHeader.textContent = 'Zamówienie #' + order.order_id + ' (' + order.created_at + ')';
        orderBox.appendChild(orderHeader);

        order.items.forEach((item) => {
            const itemBox = document.createElement('div');
            itemBox.className = 'item_box item_' + item.item_status;
            itemBox.dataset.itemId = String(item.item_id);

            const top = document.createElement('div');
            top.className = 'item_top';

            const name = document.createElement('span');
            name.className = 'item_name';
            name.textContent = item.dish_name + ' x' + item.quantity;
            top.appendChild(name);

            const badge = document.createElement('span');
            badge.className = 'item_badge badge_' + item.item_status;
            let badgeText = itemStatusLabel(item.item_status);
            if (item.item_status === 'claimed' && item.claimed_by_name) {
                badgeText += ': ' + item.claimed_by_name;
            }
            badge.textContent = badgeText;
            top.appendChild(badge);

            itemBox.appendChild(top);

            if (item.note) {
                const note = document.createElement('div');
                note.className = 'item_note';
                const noteIcon = document.createElement('i');
                noteIcon.className = 'fa-solid fa-comment-dots';
                note.appendChild(noteIcon);
                note.append(' ' + item.note);
                itemBox.appendChild(note);
            }

            orderBox.appendChild(itemBox);
        });

        ordersList.appendChild(orderBox);
    });
}

function renderCart() {
    cartList.textContent = '';

    if (!state.cart.length) {
        const empty = document.createElement('div');
        empty.className = 'orders_empty';
        empty.textContent = 'Koszyk jest pusty - kliknij danie w menu.';
        cartList.appendChild(empty);
        sendOrderButton.disabled = true;
        return;
    }

    sendOrderButton.disabled = false;

    let total = 0;

    state.cart.forEach((line, index) => {
        const dish = state.menu.find((entry) => entry.dish_id === line.dish_id);
        const lineTotal = dish ? Number(dish.dish_price) * line.quantity : 0;
        total += lineTotal;

        const lineBox = document.createElement('div');
        lineBox.className = 'cart_line';

        const top = document.createElement('div');
        top.className = 'cart_line_top';

        const name = document.createElement('span');
        name.className = 'cart_name';
        name.textContent = dish ? dish.dish_name : ('Danie #' + line.dish_id);
        top.appendChild(name);

        const price = document.createElement('span');
        price.className = 'cart_price';
        price.textContent = lineTotal.toFixed(2) + ' zł';
        top.appendChild(price);

        const remove = document.createElement('i');
        remove.className = 'fa-solid fa-circle-xmark cart_remove';
        remove.title = 'Usuń z koszyka';
        remove.dataset.index = String(index);
        top.appendChild(remove);

        lineBox.appendChild(top);

        const controls = document.createElement('div');
        controls.className = 'cart_controls';

        const minus = document.createElement('button');
        minus.type = 'button';
        minus.className = 'qty_button';
        minus.dataset.action = 'minus';
        minus.dataset.index = String(index);
        minus.textContent = '−';

        const qty = document.createElement('span');
        qty.className = 'cart_qty';
        qty.textContent = String(line.quantity);

        const plus = document.createElement('button');
        plus.type = 'button';
        plus.className = 'qty_button';
        plus.dataset.action = 'plus';
        plus.dataset.index = String(index);
        plus.textContent = '+';

        controls.append(minus, qty, plus);
        lineBox.appendChild(controls);

        const noteInput = document.createElement('input');
        noteInput.type = 'text';
        noteInput.className = 'cart_note';
        noteInput.maxLength = 255;
        noteInput.placeholder = 'Uwagi dla kuchni (np. bez cebuli)...';
        noteInput.value = line.note;
        noteInput.dataset.index = String(index);
        lineBox.appendChild(noteInput);

        cartList.appendChild(lineBox);
    });

    const totalBox = document.createElement('div');
    totalBox.className = 'cart_total';
    totalBox.textContent = 'Wartość koszyka: ' + total.toFixed(2) + ' zł';
    cartList.appendChild(totalBox);
}

function renderMenu() {
    menuList.textContent = '';

    if (!state.menu.length) {
        const empty = document.createElement('div');
        empty.className = 'orders_empty';
        empty.textContent = 'Menu jest puste.';
        menuList.appendChild(empty);
        return;
    }

    state.menu.forEach((dish) => {
        const dishBox = document.createElement('div');
        dishBox.className = 'menu_dish';
        dishBox.dataset.dishId = String(dish.dish_id);

        const name = document.createElement('span');
        name.className = 'menu_dish_name';
        name.textContent = dish.dish_name;

        const price = document.createElement('span');
        price.className = 'menu_dish_price';
        price.textContent = Number(dish.dish_price).toFixed(2) + ' zł';

        const add = document.createElement('i');
        add.className = 'fa-solid fa-plus menu_dish_add';
        add.title = 'Dodaj do koszyka';

        dishBox.append(name, price, add);
        menuList.appendChild(dishBox);
    });
}

function renderTableView(rebuildCart = false) {
    if (!state.openSession) {
        closeTableView();
        return;
    }

    const session = state.openSession;
    const table = session.table;

    emptyHint.classList.add('hidden');
    tableView.classList.remove('hidden');

    document.getElementById('tv_table_number').textContent = '#' + table.table_number;
    document.getElementById('tv_floor_name').textContent = table.floor_name;
    document.getElementById('tv_chairs_count').textContent = String(table.chairs);
    document.getElementById('tv_total').textContent = state.openSummary
        ? ('(' + Number(state.openSummary.total).toFixed(2) + ' zł)')
        : '';

    const statusBadge = document.getElementById('tv_status');
    statusBadge.textContent = statusLabel(session.status);
    statusBadge.className = 'status_badge badge_' + session.status;

    renderOrders();

    if (session.status === 'open') {
        menuPanel.classList.remove('hidden');
        closeTableFooter.classList.remove('hidden');
        toPayPanel.classList.add('hidden');

        if (rebuildCart) renderCart();
    } else {
        menuPanel.classList.add('hidden');
        closeTableFooter.classList.add('hidden');
        toPayPanel.classList.remove('hidden');
        renderToPayPanel();
    }

    renderSidebar();
}

function renderSummaryLines(container, summaryOrders) {
    container.textContent = '';

    let any = false;

    summaryOrders.forEach((order) => {
        order.items.forEach((item) => {
            any = true;

            const line = document.createElement('div');
            line.className = 'summary_line';

            const name = document.createElement('span');
            name.textContent = item.dish_name + ' x' + item.quantity;
            line.appendChild(name);

            const price = document.createElement('span');
            price.className = 'summary_line_price';
            price.textContent = (Number(item.dish_price) * Number(item.quantity)).toFixed(2) + ' ';
            line.appendChild(price);

            container.appendChild(line);
        });
    });

    if (!any) {
        const empty = document.createElement('div');
        empty.className = 'orders_empty';
        empty.textContent = 'Brak pozycji.';
        container.appendChild(empty);
    }
}

function renderToPayPanel() {
    renderSummaryLines(document.getElementById('to_pay_list'), state.openOrders);
    document.getElementById('to_pay_total').textContent = state.openSummary
        ? Number(state.openSummary.total).toFixed(2)
        : '0.00';
}

///////////////////////////////////////////////////////////////
// Akcje: otwieranie stolika, koszyk, wysylanie zamowien

async function openTable(tableId) {
    try {
        const opened = await postForm('open_table.php', { table_id: tableId });
        const view = await postForm('get_session_view.php', { session_id: opened.session_id });

        state.openTableId = Number(tableId);
        state.openSession = view.session;
        state.openOrders = view.orders;
        state.openSummary = view.summary;
        state.cart = [];

        renderTableView(true);
    } catch (error) {
        toast(error.message, true);
    }
}

async function refreshOpenTable() {
    if (!state.openSession) return;

    try {
        const view = await postForm('get_session_view.php', { session_id: state.openSession.session_id });

        const previousStatus = state.openSession.status;
        state.openSession = view.session;
        state.openOrders = view.orders;
        state.openSummary = view.summary;

        renderTableView(false);

        if (previousStatus === 'open' && view.session.status === 'to_pay') {
            toast('Stolik został zamknięty przez innego kelnera - czeka na opłacenie.');
        }
    } catch (error) {
        toast(error.message, true);
    }
}

document.getElementById('floors_container').addEventListener('click', (event) => {
    const card = event.target.closest('.table_card');
    if (!card) return;

    openTable(card.dataset.tableId);
});

document.getElementById('btn_close_view').addEventListener('click', () => {
    closeTableView();
});

menuList.addEventListener('click', (event) => {
    const dishBox = event.target.closest('.menu_dish');
    if (!dishBox) return;

    const dishId = Number(dishBox.dataset.dishId);
    const existing = state.cart.find((line) => line.dish_id === dishId);

    if (existing) {
        existing.quantity += 1;
    } else {
        state.cart.push({ dish_id: dishId, quantity: 1, note: '' });
    }

    renderCart();
});

cartList.addEventListener('click', (event) => {
    const remove = event.target.closest('.cart_remove');
    if (remove) {
        state.cart.splice(Number(remove.dataset.index), 1);
        renderCart();
        return;
    }

    const qtyButton = event.target.closest('.qty_button');
    if (qtyButton) {
        const index = Number(qtyButton.dataset.index);
        const line = state.cart[index];
        if (!line) return;

        if (qtyButton.dataset.action === 'plus') {
            line.quantity = Math.min(99, line.quantity + 1);
        } else {
            line.quantity -= 1;
            if (line.quantity < 1) state.cart.splice(index, 1);
        }

        renderCart();
    }
});

cartList.addEventListener('input', (event) => {
    if (!event.target.classList.contains('cart_note')) return;

    const index = Number(event.target.dataset.index);
    if (state.cart[index]) {
        state.cart[index].note = event.target.value;
    }
});

sendOrderButton.addEventListener('click', async () => {
    if (!state.openSession || !state.cart.length) return;

    sendOrderButton.disabled = true;

    try {
        const payload = await postForm('add_order_items.php', {
            session_id: state.openSession.session_id,
            items: JSON.stringify(state.cart)
        });

        state.cart = [];
        toast(payload.message);

        await refreshOpenTable();
        renderCart();
    } catch (error) {
        toast(error.message, true);
    } finally {
        sendOrderButton.disabled = false;
    }
});

///////////////////////////////////////////////////////////////
// Zamykanie stolika, platnosci

const summaryModal = document.getElementById('summary_modal');
const historyModal = document.getElementById('history_modal');

document.getElementById('btn_close_table').addEventListener('click', async () => {
    if (!state.openSession) return;

    try {
        const payload = await postForm('close_session.php', {
            session_id: state.openSession.session_id
        });

        if (payload.cancelled) {
            toast('Pusty stolik został zwolniony.');
            closeTableView();
            await pollUpdates(false);
            return;
        }

        document.getElementById('sm_table').textContent =
            '#' + state.openSession.table.table_number + ' (' + state.openSession.table.floor_name + ')';
        document.getElementById('sm_total').textContent = Number(payload.summary.total).toFixed(2);

        const list = document.getElementById('sm_list');
        list.textContent = '';

        let any = false;
        state.openOrders.forEach((order) => {
            order.items.forEach((item) => {
                any = true;

                const line = document.createElement('div');
                line.className = 'summary_line';

                const name = document.createElement('span');
                name.textContent = item.dish_name + ' x' + item.quantity;
                line.appendChild(name);

                const price = document.createElement('span');
                price.className = 'summary_line_price';
                price.textContent = (Number(item.dish_price) * Number(item.quantity)).toFixed(2) + ' ';
                line.appendChild(price);

                list.appendChild(line);
            });
        });

        if (!any) {
            const empty = document.createElement('div');
            empty.className = 'orders_empty';
            empty.textContent = 'Brak pozycji.';
            list.appendChild(empty);
        }

        summaryModal.classList.remove('hidden');

        state.openSession.status = 'to_pay';
        renderTableView(false);
    } catch (error) {
        toast(error.message, true);
    }
});

async function payCurrentSession(sessionId, sessionLabel) {
    try {
        const payload = await postForm('pay_session.php', { session_id: sessionId });

        summaryModal.classList.add('hidden');
        toast('Opłacono stolik ' + sessionLabel + ': ' + Number(payload.summary.total).toFixed(2) + ' zł');

        closeTableView();
        await pollUpdates(false);
    } catch (error) {
        toast(error.message, true);
    }
}

document.getElementById('btn_modal_pay').addEventListener('click', () => {
    if (!state.openSession) return;
    payCurrentSession(state.openSession.session_id, '#' + state.openSession.table.table_number);
});

document.getElementById('btn_modal_skip').addEventListener('click', () => {
    summaryModal.classList.add('hidden');
    toast('Płatność odłożona - stolik czeka na opłatę (kolor pomarańczowy).');
    renderTableView(false);
});

document.getElementById('btn_pay_now').addEventListener('click', () => {
    if (!state.openSession) return;
    payCurrentSession(state.openSession.session_id, '#' + state.openSession.table.table_number);
});

document.getElementById('btn_skip_pay').addEventListener('click', () => {
    toast('Płatność odłożona - wróć do tego stolika później.');
    closeTableView();
    pollUpdates(false);
});

///////////////////////////////////////////////////////////////
// Historia zamowien

const historyList = document.getElementById('history_list');

document.getElementById('btn_history').addEventListener('click', async () => {
    historyModal.classList.remove('hidden');
    historyList.textContent = 'Ładowanie historii...';

    try {
        const payload = await getJson('get_history.php');
        historyList.textContent = '';

        if (!payload.history.length) {
            historyList.textContent = 'History is empty.';
            return;
        }

        payload.history.forEach((session) => {
            const details = document.createElement('details');
            details.className = 'history_entry';

            const summary = document.createElement('summary');
            summary.textContent =
                'Table #' + session.table_number +
                ' (' + session.floor_name + ') - ' +
                Number(session.total).toFixed(2) + ' - ' +
                'paid: ' + (session.paid_at || '-') +
                (session.paid_by_name ? ' by ' + session.paid_by_name : '');
            details.appendChild(summary);

            session.items.forEach((item) => {
                const line = document.createElement('div');
                line.className = 'summary_line';

                const name = document.createElement('span');
                name.textContent = item.dish_name + ' x' + item.quantity + (item.note ? ' (' + item.note + ')' : '');
                line.appendChild(name);

                const price = document.createElement('span');
                price.className = 'summary_line_price';
                price.textContent = (Number(item.dish_price) * Number(item.quantity)).toFixed(2) + ' ';
                line.appendChild(price);

                details.appendChild(line);
            });

            historyList.appendChild(details);
        });
    } catch (error) {
        historyList.textContent = error.message;
    }
});

document.getElementById('btn_history_close').addEventListener('click', () => {
    historyModal.classList.add('hidden');
});

///////////////////////////////////////////////////////////////
// Odswiezanie (AJAX) + powiadomienia

async function pollUpdates(showErrors = true) {
    try {
        const payload = await getJson('get_waiter_updates.php');

        payload.items.forEach((item) => {
            const previousStatus = state.itemStatusCache[item.item_id];

            if (item.item_status === 'ready' && previousStatus && previousStatus !== 'ready') {
                const found = findTable(item.table_id);
                const tableLabel = found ? ('#' + found.table.table_number) : '';
                toast('🔔 Table ' + tableLabel + ': ' + item.dish_name + ' is READY!');
            }
        });

        state.sessions = payload.sessions;
        state.items = payload.items;

        state.itemStatusCache = {};
        payload.items.forEach((item) => {
            state.itemStatusCache[item.item_id] = item.item_status;
        });

        renderSidebar();

        if (state.openSession) {
            const knownSession = payload.sessions.find(
                (session) => session.session_id === state.openSession.session_id
            );

            if (!knownSession) {
                toast('This table has already been paid.');
                closeTableView();
                return;
            }

            await refreshOpenTable();
        }
    } catch (error) {
        if (showErrors) toast(error.message, true);
    }
}


renderSidebar();
renderMenu();
pollUpdates(false);
setInterval(() => pollUpdates(false), 4000);






