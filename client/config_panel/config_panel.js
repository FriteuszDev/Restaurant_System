
(function () {
    'use strict';

    var CAN = window.CAN || {};
    var FLAG_ICON_ON = '<i class="fa-solid fa-circle-check" style="color: rgb(0, 209, 21);"></i>';
    var FLAG_ICON_OFF = '<i class="fa-solid fa-circle-xmark" style="color: rgb(215, 25, 25);"></i>';

    function toast(message, isError) {
        var host = document.getElementById('config_toasts');
        if (!host) {
            alert(message);
            return;
        }
        var el = document.createElement('div');
        el.className = 'toast' + (isError ? ' error' : '');
        el.textContent = message;
        host.appendChild(el);
        requestAnimationFrame(function () { el.classList.add('visible'); });
        setTimeout(function () {
            el.classList.remove('visible');
            setTimeout(function () { el.remove(); }, 250);
        }, 3200);
    }

    function parseJsonResponse(response) {
        return response.text().then(function (text) {
            try {
                return JSON.parse(text);
            } catch (err) {
                throw new Error(text ? text.replace(/<[^>]+>/g, ' ').slice(0, 180) : 'Invalid server response');
            }
        });
    }

    function postForm(url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data).toString()
        }).then(parseJsonResponse);
    }

    function postFormData(url, formData) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        }).then(parseJsonResponse);
    }

    function cellText(td) {
        if (!td) return '';
        var input = td.querySelector('input, select, textarea');
        if (input) return String(input.value || '').trim();
        return String(td.textContent || '').trim();
    }

    function setFlagIcon(td, on) {
        td.dataset.value = on ? '1' : '0';
        td.innerHTML = on ? FLAG_ICON_ON : FLAG_ICON_OFF;
    }

    function highlightEditButton(section) {
        var btn = document.getElementById('btn_edit_mode_' + section);
        if (!btn) return;
        btn.classList.add('need_attention');
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(function () { btn.classList.remove('need_attention'); }, 2200);
    }

    function requireEditMode(section) {
        if (EditMode.isOn(section)) return true;
        toast('You must be in Edit Mode to do that.', true);
        highlightEditButton(section);
        return false;
    }

    window.CheckBox = function (el) {
        if (el && el.checked) {
            var form = el.closest('form');
            var cfg = form ? form.querySelector('[name="config_access"]') : null;
            if (cfg) cfg.checked = true;
        }
    };

    window.uncheckAll = function (el) {
        if (el && !el.checked) {
            var form = el.closest('form');
            if (!form) return;
            form.querySelectorAll('.auto_change').forEach(function (cb) { cb.checked = false; });
        }
    };

    var EditMode = {
        sections: {},

        isOn: function (section) {
            return !!(this.sections[section] && this.sections[section].on);
        },

        initSection: function (name, adapter) {
            var self = this;
            var btn = document.getElementById('btn_edit_mode_' + name);
            if (!btn && !adapter.always) return;

            this.sections[name] = {
                on: false,
                dirty: false,
                history: [],
                index: -1,
                applying: false,
                debounce: null,
                adapter: adapter
            };

            if (btn) {
                btn.addEventListener('click', function () {
                    if (self.sections[name].on) {
                        self.exit(name, false);
                    } else {
                        self.enter(name);
                    }
                });
            }

            var saveBtn = document.getElementById('btn_save_' + name);
            if (saveBtn) {
                saveBtn.addEventListener('click', function () { self.save(name); });
            }
            var cancelBtn = document.getElementById('btn_cancel_' + name);
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () { self.exit(name, true); });
            }
            var undoBtn = document.getElementById('btn_undo_' + name);
            if (undoBtn) {
                undoBtn.addEventListener('click', function () { self.undo(name); });
            }
            var redoBtn = document.getElementById('btn_redo_' + name);
            if (redoBtn) {
                redoBtn.addEventListener('click', function () { self.redo(name); });
            }
        },

        toolbar: function (name, show) {
            ['btn_save_', 'btn_cancel_', 'btn_undo_', 'btn_redo_'].forEach(function (prefix) {
                var el = document.getElementById(prefix + name);
                if (el) el.style.display = show ? '' : 'none';
            });
            var hint = document.getElementById('edit_hint_' + name);
            var state = this.sections[name];
            if (hint) hint.style.display = (show && state.dirty) ? '' : 'none';
            var btn = document.getElementById('btn_edit_mode_' + name);
            if (btn) {
                btn.classList.toggle('active', show);
                btn.innerHTML = show
                    ? '<i class="fa-solid fa-xmark"></i> Exit Edit Mode'
                    : '<i class="fa-solid fa-pen"></i> Edit Mode';
            }
        },

        pushHistory: function (name) {
            var state = this.sections[name];
            if (!state || state.applying || !state.on) return;
            var snap = JSON.stringify(state.adapter.collect());
            if (state.index >= 0 && snap === state.history[state.index]) return;
            state.history = state.history.slice(0, state.index + 1);
            state.history.push(snap);
            state.index = state.history.length - 1;
            state.dirty = state.index > 0;
            this.toolbar(name, true);
        },

        schedulePush: function (name) {
            var self = this;
            var state = this.sections[name];
            if (!state) return;
            clearTimeout(state.debounce);
            state.debounce = setTimeout(function () { self.pushHistory(name); }, 280);
        },

        enter: function (name) {
            var state = this.sections[name];
            if (!state || state.on) return;
            state.on = true;
            state.dirty = false;
            if (state.adapter.enable) state.adapter.enable();
            state.history = [JSON.stringify(state.adapter.collect())];
            state.index = 0;
            this.toolbar(name, true);
        },

        exit: function (name, forceDiscard) {
            var state = this.sections[name];
            if (!state || !state.on) return;
            if (state.dirty && !forceDiscard) {
                toast('Unsaved changes - save or cancel before leaving Edit Mode.', true);
                var hint = document.getElementById('edit_hint_' + name);
                if (hint) hint.style.display = '';
                return;
            }
            if (forceDiscard && state.history.length) {
                state.applying = true;
                state.adapter.apply(JSON.parse(state.history[0]));
                state.applying = false;
            }
            if (state.adapter.disable) state.adapter.disable();
            state.on = false;
            state.dirty = false;
            state.history = [];
            state.index = -1;
            this.toolbar(name, false);
        },

        undo: function (name) {
            var state = this.sections[name];
            if (!state || !state.on || state.index <= 0) return;
            state.index -= 1;
            state.applying = true;
            state.adapter.apply(JSON.parse(state.history[state.index]));
            state.applying = false;
            state.dirty = state.index > 0;
            this.toolbar(name, true);
        },

        redo: function (name) {
            var state = this.sections[name];
            if (!state || !state.on || state.index >= state.history.length - 1) return;
            state.index += 1;
            state.applying = true;
            state.adapter.apply(JSON.parse(state.history[state.index]));
            state.applying = false;
            state.dirty = state.index > 0;
            this.toolbar(name, true);
        },

        save: function (name) {
            var self = this;
            var state = this.sections[name];
            if (!state || !state.on) return;
            if (!confirm('After saving you will not be able to undo these changes. Save anyway?')) {
                return;
            }
            var payload = state.adapter.collect();
            postForm('../../php/save_edit_section.php', {
                section: name,
                payload: JSON.stringify(payload)
            }).then(function (data) {
                if (!data.success) {
                    toast(data.message || 'Save failed.', true);
                    return;
                }
                toast(data.message || 'Saved.');
                if (state.adapter.afterSave) {
                    state.adapter.afterSave(payload);
                }
                if (state.adapter.disable) state.adapter.disable();
                state.on = false;
                state.dirty = false;
                state.history = [];
                state.index = -1;
                self.toolbar(name, false);
                if (payload.deleted && payload.deleted.length) {
                    location.reload();
                }
                if (payload.deleted_tables && payload.deleted_tables.length) {
                    location.reload();
                }
                if (payload.deleted_floors && payload.deleted_floors.length) {
                    location.reload();
                }
            }).catch(function (err) {
                toast(err.message || 'Save failed.', true);
            });
        }
    };

    function enableTextCells(selector, canEdit) {
        if (!canEdit) return;
        document.querySelectorAll(selector).forEach(function (td) {
            if (td.querySelector('.inline_edit')) return;
            var field = td.getAttribute('data-field') || '';
            var input = document.createElement('input');
            input.type = 'text';
            input.className = 'inline_edit';
            input.value = td.textContent.trim();
            if (field) input.setAttribute('data-field', field);
            if (field === 'dish_price') {
                input.inputMode = 'decimal';
                input.pattern = '^\\d{1,8}(\\.\\d{1,2})?$';
                input.setAttribute('oninput', "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1');");
                input.placeholder = 'np. 10.50';
                input.required = true;
            }
            td.textContent = '';
            td.appendChild(input);
        });
    }

    function disableTextCells(selector) {
        document.querySelectorAll(selector).forEach(function (td) {
            var input = td.querySelector('.inline_edit');
            if (!input) return;
            td.textContent = input.value;
        });
    }

    EditMode.initSection('menu', {
        collect: function () {
            var rows = [];
            var deleted = [];
            document.querySelectorAll('.tr_menu[data-dish-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-dish-id');
                if (tr.dataset.deleted === '1') {
                    deleted.push(id);
                    return;
                }
                rows.push({
                    dish_id: id,
                    dish_name: cellText(tr.querySelector('[data-field="dish_name"]')),
                    dish_desc: cellText(tr.querySelector('[data-field="dish_desc"]')),
                    dish_price: cellText(tr.querySelector('[data-field="dish_price"]')),
                    category_id: (tr.querySelector('[data-field="category_id"] select') || {}).value || ''
                });
            });
            return { rows: rows, deleted: deleted };
        },
        apply: function (data) {
            var deleted = {};
            (data.deleted || []).forEach(function (id) { deleted[String(id)] = true; });
            var byId = {};
            (data.rows || []).forEach(function (row) { byId[String(row.dish_id)] = row; });
            document.querySelectorAll('.tr_menu[data-dish-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-dish-id');
                if (deleted[id]) {
                    tr.dataset.deleted = '1';
                    tr.style.display = 'none';
                    return;
                }
                tr.dataset.deleted = '0';
                tr.style.display = '';
                var row = byId[id];
                if (!row) return;
                ['dish_name', 'dish_desc', 'dish_price'].forEach(function (field) {
                    var td = tr.querySelector('[data-field="' + field + '"]');
                    if (!td) return;
                    var input = td.querySelector('.inline_edit');
                    if (input) input.value = row[field] || '';
                    else td.textContent = row[field] || '';
                });
                var catSelect = tr.querySelector('[data-field="category_id"] select');
                if (catSelect) catSelect.value = row.category_id || '';
            });
        },
        enable: function () {
            enableTextCells('.tr_menu[data-dish-id] [data-field]:not([data-field="category_id"])', !!CAN.edit_dishes);
            document.querySelectorAll('.tr_menu[data-dish-id] [data-field="category_id"] select').forEach(function (sel) {
                sel.disabled = !CAN.edit_dishes;
            });
        },
        disable: function () {
            disableTextCells('.tr_menu[data-dish-id] [data-field]:not([data-field="category_id"])');
            document.querySelectorAll('.tr_menu[data-dish-id] [data-field="category_id"] select').forEach(function (sel) {
                sel.disabled = true;
            });
        }
    });

    EditMode.initSection('categories', {
        collect: function () {
            var rows = [];
            var deleted = [];
            document.querySelectorAll('.tr_categories[data-category-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-category-id');
                if (tr.dataset.deleted === '1') {
                    deleted.push(id);
                    return;
                }
                rows.push({
                    category_id: id,
                    category_name: cellText(tr.querySelector('[data-field="category_name"]'))
                });
            });
            return { rows: rows, deleted: deleted };
        },
        apply: function (data) {
            var deleted = {};
            (data.deleted || []).forEach(function (id) { deleted[String(id)] = true; });
            var byId = {};
            (data.rows || []).forEach(function (row) { byId[String(row.category_id)] = row; });
            document.querySelectorAll('.tr_categories[data-category-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-category-id');
                if (deleted[id]) {
                    tr.dataset.deleted = '1';
                    tr.style.display = 'none';
                    return;
                }
                tr.dataset.deleted = '0';
                tr.style.display = '';
                var row = byId[id];
                if (!row) return;
                var td = tr.querySelector('[data-field="category_name"]');
                if (!td) return;
                var input = td.querySelector('.inline_edit');
                if (input) input.value = row.category_name || '';
                else td.textContent = row.category_name || '';
            });
        },
        enable: function () {
            enableTextCells('.tr_categories[data-category-id] [data-field]', !!CAN.edit_dishes);
        },
        disable: function () {
            disableTextCells('.tr_categories[data-category-id] [data-field]');
        }
    });


    EditMode.initSection('employees', {
        collect: function () {
            var rows = [];
            var deleted = [];
            document.querySelectorAll('.tr_employees[data-employee-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-employee-id');
                if (tr.dataset.deleted === '1') {
                    deleted.push(id);
                    return;
                }
                rows.push({
                    employee_id: id,
                    employee_name: cellText(tr.querySelector('[data-field="employee_name"]')),
                    employee_surname: cellText(tr.querySelector('[data-field="employee_surname"]')),
                    grade_name: cellText(tr.querySelector('[data-field="grade_name"]'))
                });
            });
            return { rows: rows, deleted: deleted };
        },
        apply: function (data) {
            var deleted = {};
            (data.deleted || []).forEach(function (id) { deleted[String(id)] = true; });
            var byId = {};
            (data.rows || []).forEach(function (row) { byId[String(row.employee_id)] = row; });
            document.querySelectorAll('.tr_employees[data-employee-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-employee-id');
                if (deleted[id]) {
                    tr.dataset.deleted = '1';
                    tr.style.display = 'none';
                    return;
                }
                tr.dataset.deleted = '0';
                tr.style.display = '';
                var row = byId[id];
                if (!row) return;
                ['employee_name', 'employee_surname'].forEach(function (field) {
                    var td = tr.querySelector('[data-field="' + field + '"]');
                    if (!td) return;
                    var input = td.querySelector('.inline_edit');
                    if (input) input.value = row[field] || '';
                    else td.textContent = row[field] || '';
                });
                var select = tr.querySelector('.emp_grade_select');
                if (select && row.grade_name) select.value = row.grade_name;
            });
        },
        enable: function () {
            enableTextCells('.tr_employees[data-employee-id] [data-field="employee_name"], .tr_employees[data-employee-id] [data-field="employee_surname"]', !!CAN.edit_employees);
            document.querySelectorAll('.emp_grade_select').forEach(function (sel) {
                sel.disabled = !CAN.edit_employees;
            });
        },
        disable: function () {
            disableTextCells('.tr_employees[data-employee-id] [data-field="employee_name"], .tr_employees[data-employee-id] [data-field="employee_surname"]');
            document.querySelectorAll('.emp_grade_select').forEach(function (sel) {
                sel.disabled = true;
            });
        }
    });

    EditMode.initSection('grades', {
        collect: function () {
            var rows = [];
            var deleted = [];
            document.querySelectorAll('.tr_grades[data-grade-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-grade-id');
                if (tr.dataset.deleted === '1') {
                    deleted.push(id);
                    return;
                }
                var permissions = {};
                tr.querySelectorAll('td.td_grades[data-flag]').forEach(function (td) {
                    permissions[td.getAttribute('data-flag')] = td.dataset.value === '1' ? 1 : 0;
                });
                rows.push({
                    grade_id: id,
                    grade_name: cellText(tr.querySelector('[data-field="grade_name"]')),
                    permissions: permissions
                });
            });
            return { rows: rows, deleted: deleted };
        },
        apply: function (data) {
            var deleted = {};
            (data.deleted || []).forEach(function (id) { deleted[String(id)] = true; });
            var byId = {};
            (data.rows || []).forEach(function (row) { byId[String(row.grade_id)] = row; });
            document.querySelectorAll('.tr_grades[data-grade-id]').forEach(function (tr) {
                var id = tr.getAttribute('data-grade-id');
                if (deleted[id]) {
                    tr.dataset.deleted = '1';
                    tr.style.display = 'none';
                    return;
                }
                tr.dataset.deleted = '0';
                tr.style.display = '';
                var row = byId[id];
                if (!row) return;
                var nameTd = tr.querySelector('[data-field="grade_name"]');
                if (nameTd) {
                    var input = nameTd.querySelector('.inline_edit');
                    if (input) input.value = row.grade_name || '';
                    else nameTd.textContent = row.grade_name || '';
                }
                tr.querySelectorAll('td.td_grades[data-flag]').forEach(function (td) {
                    var flag = td.getAttribute('data-flag');
                    setFlagIcon(td, !!(row.permissions && Number(row.permissions[flag]) === 1));
                });
            });
        },
        enable: function () {
            enableTextCells('.tr_grades[data-grade-id] [data-field="grade_name"]', !!CAN.edit_grades);
            document.querySelectorAll('.td_grades[data-flag]').forEach(function (td) {
                td.classList.toggle('editable_flag', !!CAN.edit_grades);
            });
        },
        disable: function () {
            disableTextCells('.tr_grades[data-grade-id] [data-field="grade_name"]');
            document.querySelectorAll('.td_grades[data-flag]').forEach(function (td) {
                td.classList.remove('editable_flag');
            });
        }
    });

    function tableNumberValue(card) {
        var el = card.querySelector('[data-field="table_number"]');
        if (!el) return '';
        var input = el.querySelector('.inline_edit');
        var raw = input ? input.value : el.textContent;
        return String(raw || '').replace(/^#/, '').trim();
    }

    function chairsValue(card) {
        var wrap = card.querySelector('[data-field="chairs"]');
        if (!wrap) return '4';
        var input = wrap.querySelector('.inline_edit');
        if (input) return String(input.value || '').trim();
        var span = wrap.querySelector('.chairs_value');
        return String((span ? span.textContent : wrap.textContent) || '').replace(/[^\d]/g, '');
    }

    EditMode.initSection('tables', {
        collect: function () {
            var floors = [];
            var tables = [];
            var deletedFloors = [];
            var deletedTables = [];
            document.querySelectorAll('.floor_block[data-floor-id]').forEach(function (block) {
                var floorId = block.getAttribute('data-floor-id');
                if (block.dataset.deleted === '1') {
                    deletedFloors.push(floorId);
                    return;
                }
                var nameInput = block.querySelector('.floor_name .inline_edit');
                var nameText = block.querySelector('.floor_name_text');
                floors.push({
                    floor_id: floorId,
                    floor_name: nameInput ? nameInput.value.trim() : (nameText ? nameText.textContent.trim() : '')
                });
                block.querySelectorAll('.table_card[data-table-id]').forEach(function (card) {
                    var tableId = card.getAttribute('data-table-id');
                    if (card.dataset.deleted === '1') {
                        deletedTables.push(tableId);
                        return;
                    }
                    tables.push({
                        table_id: tableId,
                        table_number: tableNumberValue(card),
                        chairs: chairsValue(card)
                    });
                });
            });
            document.querySelectorAll('.table_card[data-table-id]').forEach(function (card) {
                if (card.dataset.deleted === '1') {
                    var id = card.getAttribute('data-table-id');
                    if (deletedTables.indexOf(id) === -1) deletedTables.push(id);
                }
            });
            return {
                floors: floors,
                tables: tables,
                deleted_floors: deletedFloors,
                deleted_tables: deletedTables
            };
        },
        apply: function (data) {
            var deletedFloors = {};
            var deletedTables = {};
            (data.deleted_floors || []).forEach(function (id) { deletedFloors[String(id)] = true; });
            (data.deleted_tables || []).forEach(function (id) { deletedTables[String(id)] = true; });
            var floorsById = {};
            (data.floors || []).forEach(function (row) { floorsById[String(row.floor_id)] = row; });
            var tablesById = {};
            (data.tables || []).forEach(function (row) { tablesById[String(row.table_id)] = row; });

            document.querySelectorAll('.floor_block[data-floor-id]').forEach(function (block) {
                var id = block.getAttribute('data-floor-id');
                if (deletedFloors[id]) {
                    block.dataset.deleted = '1';
                    block.style.display = 'none';
                    return;
                }
                block.dataset.deleted = '0';
                block.style.display = '';
                var floor = floorsById[id];
                if (floor) {
                    var input = block.querySelector('.floor_name .inline_edit');
                    var text = block.querySelector('.floor_name_text');
                    if (input) input.value = floor.floor_name || '';
                    else if (text) text.textContent = floor.floor_name || '';
                }
            });
            document.querySelectorAll('.table_card[data-table-id]').forEach(function (card) {
                var id = card.getAttribute('data-table-id');
                if (deletedTables[id]) {
                    card.dataset.deleted = '1';
                    card.style.display = 'none';
                    return;
                }
                card.dataset.deleted = '0';
                card.style.display = '';
                var table = tablesById[id];
                if (!table) return;
                var numEl = card.querySelector('[data-field="table_number"]');
                if (numEl) {
                    var numInput = numEl.querySelector('.inline_edit');
                    if (numInput) numInput.value = table.table_number;
                    else numEl.textContent = '#' + table.table_number;
                }
                var chairsEl = card.querySelector('[data-field="chairs"]');
                if (chairsEl) {
                    var chairsInput = chairsEl.querySelector('.inline_edit');
                    var span = chairsEl.querySelector('.chairs_value');
                    if (chairsInput) chairsInput.value = table.chairs;
                    else if (span) span.textContent = table.chairs;
                }
            });
        },
        enable: function () {
            document.querySelectorAll('.floor_name_text').forEach(function (span) {
                if (span.parentNode.querySelector('.inline_edit')) return;
                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'inline_edit';
                input.value = span.textContent.trim();
                input.setAttribute('data-field', 'floor_name');
                span.style.display = 'none';
                span.parentNode.appendChild(input);
            });
            document.querySelectorAll('.table_card [data-field="table_number"]').forEach(function (el) {
                if (el.querySelector('.inline_edit')) return;
                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'inline_edit';
                input.value = el.textContent.replace(/^#/, '').trim();
                el.textContent = '';
                el.appendChild(input);
            });
            document.querySelectorAll('.table_card [data-field="chairs"]').forEach(function (el) {
                if (el.querySelector('.inline_edit')) return;
                var span = el.querySelector('.chairs_value');
                var input = document.createElement('input');
                input.type = 'number';
                input.min = '1';
                input.max = '50';
                input.className = 'inline_edit chairs_edit';
                input.value = span ? span.textContent.trim() : '4';
                if (span) span.style.display = 'none';
                el.appendChild(input);
            });
        },
        disable: function () {
            document.querySelectorAll('.floor_name .inline_edit').forEach(function (input) {
                var text = input.parentNode.querySelector('.floor_name_text');
                if (text) {
                    text.textContent = input.value;
                    text.style.display = '';
                }
                input.remove();
            });
            document.querySelectorAll('.table_card [data-field="table_number"]').forEach(function (el) {
                var input = el.querySelector('.inline_edit');
                if (!input) return;
                el.textContent = '#' + input.value.replace(/^#/, '');
            });
            document.querySelectorAll('.table_card [data-field="chairs"]').forEach(function (el) {
                var input = el.querySelector('.inline_edit');
                var span = el.querySelector('.chairs_value');
                if (!input) return;
                if (span) {
                    span.textContent = input.value;
                    span.style.display = '';
                }
                input.remove();
            });
        }
    });

    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('inline_edit') && !e.target.classList.contains('emp_grade_select')) return;
        ['menu', 'employees', 'grades', 'tables', 'categories'].forEach(function (name) {
            if (EditMode.isOn(name) && e.target.closest('.config_' + name)) {
                EditMode.schedulePush(name);
            }
        });
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('menu_category_select')) {
            if (EditMode.isOn('menu')) EditMode.schedulePush('menu');
            return;
        }
        if (!e.target.classList.contains('emp_grade_select')) return;
        if (EditMode.isOn('employees')) EditMode.pushHistory('employees');
    });

    document.addEventListener('click', function (e) {
        var flagTd = e.target.closest('.td_grades[data-flag]');
        if (flagTd) {
            if (!requireEditMode('grades')) return;
            if (!CAN.edit_grades) {
                toast('You do not have permission to edit grades.', true);
                return;
            }
            setFlagIcon(flagTd, flagTd.dataset.value !== '1');
            EditMode.pushHistory('grades');
            return;
        }

        var dishDel = e.target.closest('.dish_delete');
        if (dishDel) {
            if (!requireEditMode('menu')) return;
            if (!confirm('Delete this dish? This will be applied when you save.')) return;
            var dishRow = dishDel.closest('tr');
            if (dishRow) {
                dishRow.dataset.deleted = '1';
                dishRow.style.display = 'none';
                EditMode.pushHistory('menu');
            }
            return;
        }

var catDel = e.target.closest('.category_delete');
        if (catDel) {
            if (!requireEditMode('categories')) return;
            if (!confirm('Delete this category? This will be applied when you save.')) return;
            var catRow = catDel.closest('tr');
            if (catRow) {
                catRow.dataset.deleted = '1';
                catRow.style.display = 'none';
                EditMode.pushHistory('categories');
            }
            return;
        }

        var empDel = e.target.closest('.employee_delete');
        if (empDel) {
            if (!requireEditMode('employees')) return;
            if (!confirm('Delete this employee? This will be applied when you save.')) return;
            var empRow = empDel.closest('tr');
            if (empRow) {
                empRow.dataset.deleted = '1';
                empRow.style.display = 'none';
                EditMode.pushHistory('employees');
            }
            return;
        }

        var gradeDel = e.target.closest('.grade_delete');
        if (gradeDel) {
            if (!requireEditMode('grades')) return;
            if (!confirm('Delete this grade? This will be applied when you save.')) return;
            var gradeRow = gradeDel.closest('tr');
            if (gradeRow) {
                gradeRow.dataset.deleted = '1';
                gradeRow.style.display = 'none';
                EditMode.pushHistory('grades');
            }
            return;
        }

        var floorDel = e.target.closest('.floor_delete');
        if (floorDel) {
            if (!requireEditMode('tables')) return;
            if (!confirm('Delete this floor and its tables? This will be applied when you save.')) return;
            var floorBlock = floorDel.closest('.floor_block');
            if (floorBlock) {
                floorBlock.dataset.deleted = '1';
                floorBlock.style.display = 'none';
                EditMode.pushHistory('tables');
            }
            return;
        }

        var tableDel = e.target.closest('.table_delete');
        if (tableDel) {
            if (!requireEditMode('tables')) return;
            if (!confirm('Delete this table? This will be applied when you save.')) return;
            var tableCard = tableDel.closest('.table_card');
            if (tableCard) {
                tableCard.dataset.deleted = '1';
                tableCard.style.display = 'none';
                EditMode.pushHistory('tables');
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var showAddCategory = document.getElementById('btn_show_add_category');
        var addCategoryForm = document.getElementById('add_category_form');
        if (showAddCategory && addCategoryForm) {
            showAddCategory.addEventListener('click', function () {
                addCategoryForm.style.display = addCategoryForm.style.display === 'none' ? '' : 'none';
            });
        }

        var tabs = document.querySelectorAll('.tabs_container [data-tab]');
        var sections = document.querySelectorAll('.config_menu, .config_employees, .config_grades, .config_tables, .config_settings, .config_categories');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var name = this.getAttribute('data-tab');
                tabs.forEach(function (t) { t.classList.remove('active'); });
                this.classList.add('active');
                sections.forEach(function (s) { s.classList.remove('is-active'); });
                var sec = document.querySelector('.config_' + name);
                if (sec) sec.classList.add('is-active');
            });
        });

        document.querySelectorAll('form.ajax_add_form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var btn = form.querySelector('button[type="submit"]');
                var label = btn ? btn.textContent : '';
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Saving...';
                }
                postFormData(form.action, new FormData(form))
                    .then(function (data) {
                        if (data.success) {
                            toast(data.message || 'Added.');
                            form.reset();
                            location.reload();
                        } else {
                            toast(data.message || 'Could not add.', true);
                        }
                    })
                    .catch(function (err) {
                        toast(err.message || 'Could not add.', true);
                    })
                    .finally(function () {
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = label;
                        }
                    });
            });
        });

        var btnAddFloor = document.getElementById('btn_add_floor');
        var floorNameInput = document.getElementById('floor_name_input');
        if (btnAddFloor && floorNameInput) {
            btnAddFloor.addEventListener('click', function () {
                var name = floorNameInput.value.trim();
                if (name === '') {
                    toast('Enter a floor name.', true);
                    return;
                }
                postForm('../../php/add_floor.php', { floor_name: name })
                    .then(function (data) {
                        if (data.success) {
                            toast(data.message || 'Floor added.');
                            location.reload();
                        } else {
                            toast(data.message || 'Could not add floor.', true);
                        }
                    })
                    .catch(function (err) {
                        toast(err.message || 'Could not add floor.', true);
                    });
            });
        }

        var btnAddTable = document.getElementById('btn_add_table');
        if (btnAddTable) {
            btnAddTable.addEventListener('click', function () {
                var floorSelect = document.getElementById('table_floor_select');
                var tableNumberInput = document.getElementById('table_number_input');
                var tableChairsInput = document.getElementById('table_chairs_input');
                var floorId = floorSelect ? floorSelect.value : '';
                var tableNumber = tableNumberInput ? tableNumberInput.value.trim() : '';
                var chairs = tableChairsInput ? tableChairsInput.value : '4';
                if (!floorId) {
                    toast('Select a floor.', true);
                    return;
                }
                if (tableNumber === '') {
                    toast('Enter a table number.', true);
                    return;
                }
                postForm('../../php/add_table.php', {
                    floor_id: floorId,
                    table_number: tableNumber,
                    chairs: chairs || '4'
                }).then(function (data) {
                    if (data.success) {
                        toast(data.message || 'Table added.');
                        location.reload();
                    } else {
                        toast(data.message || 'Could not add table.', true);
                    }
                }).catch(function (err) {
                    toast(err.message || 'Could not add table.', true);
                });
            });
        }

        var logoInput = document.getElementById('upload_logo_input');
        var logoPreview = document.querySelector('.settings_logo_preview img');
        var logoInputHidden = document.getElementById('setting_restaurant_logo');
        if (logoInput && logoPreview) {
            logoInput.addEventListener('change', function () {
                var file = logoInput.files[0];
                if (!file) return;
                var allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
                if (allowedTypes.indexOf(file.type) === -1) {
                    toast('Please select a valid image file (PNG, JPEG, GIF, WebP).', true);
                    logoInput.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    toast('File size must be under 2MB.', true);
                    logoInput.value = '';
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (ev) {
                    logoPreview.src = ev.target.result;
                    if (logoInputHidden) logoInputHidden.value = file.name;
                };
                reader.readAsDataURL(file);
            });
        }

        var nameForm = document.querySelector('form.ajax_form[data-setting-type="name"]');
        if (nameForm) {
            nameForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var submitBtn = nameForm.querySelector('button[type="submit"]');
                var originalLabel = submitBtn ? submitBtn.textContent : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving...';
                }
                postFormData(nameForm.action, new FormData(nameForm))
                    .then(function (data) {
                        if (data.success) toast('Settings saved successfully!');
                        else toast(data.message || 'Unknown error', true);
                    })
                    .catch(function () { toast('Error saving settings.', true); })
                    .finally(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalLabel;
                        }
                    });
            });
        }

        var logoForm = document.querySelector('form.ajax_form[data-setting-type="logo"]');
        if (logoForm) {
            logoForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var submitBtn = logoForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Uploading...';
                }
                postFormData(logoForm.action, new FormData(logoForm))
                    .then(function (data) {
                        if (data.success || data.status === 'success') {
                            var preview = document.getElementById('current_logo_img') || document.querySelector('.settings_logo_preview img');
                            if (preview && data.logo_url) preview.src = data.logo_url;
                            var topLogo = document.querySelector('.config_top_logo');
                            if (topLogo && data.logo_url) topLogo.src = data.logo_url;
                            var hidden = document.getElementById('setting_restaurant_logo');
                            if (hidden) hidden.value = data.filename || '';
                            toast('Logo uploaded successfully!');
                        } else {
                            toast(data.message || 'Unknown error', true);
                        }
                    })
                    .catch(function () { toast('Error uploading logo.', true); })
                    .finally(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Upload Logo';
                        }
                    });
            });
        }
    });
})();
