let E = {
    glb: {},
    toggleLoadingScreen: function(on) {
        let div = $('#loading');
        let screens = $('.screen');
        if (on) {
            div.removeClass('hidden').addClass('visible');
            screens.addClass('blur');
        }
        else {
            div.removeClass('visible').addClass('hidden');
            if ($('.form.visible').length === 0) screens.removeClass('blur');
        }
    },
    toggleForm: function(type = null, id = null, action = null) {
        if (type === null) {
            $('.form.visible').removeClass('visible').addClass('hidden');
            $('.helpText.visible').removeClass('visible').addClass('hidden');
            $('.screen').removeClass('blur');
        }
        else {
            let form;
            if (action === null) form = $('.form[data-entity="'+type+'"]:not([data-action])');
            else form = $('.form[data-entity="'+type+'"][data-action='+action+']');
            if (form.length === 0) throw new Error('No form with type '+type +' action ' + action);
            E.clearInputError(form.find('.error'));
            $('.screen').addClass('blur');
            if (id === null) {
                form.attr('data-id', null);
                form.find('input[type=text]').val('');
                let inputs = form.find('input, select');
                for (let i = 0; i < inputs.length; i++) {
                    let input = inputs.eq(i);
                    let value;
                    if (input.attr('data-default') !== undefined) value = input.attr('data-default');
                    else continue;
                    if (input.attr('type') === 'checkbox') input.prop('checked', value);
                    else input.val(value);
                }
                form.find('select option[data-default="true"]').prop('selected', true);
                form.removeClass('hidden').addClass('visible');
                if (type === 'User' && action === null) form.find('.generatePassword').click();
                if ((type === 'User' || type === 'Shop') && action === null)
                    E.addAssignmentsCheckboxes([], form, type === 'User' ? 'Shop' : 'User')
                        .then(() => E.toggleLoadingScreen(false));
            }
            else {
                if (type === 'User' && action === 'customersAssignment' &&
                    $(`.tab[data-hash="User"] .entity[data-id="${id}"] [data-name="role"]`).val() !== 'MANAGER') {
                    setTimeout(() => E.msg("Клиенты могут быть назначены ТОЛЬКО для менеджеров", 'error'), 500);
                    $('.screen').removeClass('blur');
                    return;
                }
                form.attr('data-id', id);
                E.toggleLoadingScreen(true);
                let promise;
                if (action === null) promise = E.callAPI('getEntity', {type: type, id: id});
                else promise = Promise.resolve([]);
                if ((type === 'User' || type === 'Shop') && action === null)
                    promise = promise.then(response => {
                        return E.addAssignmentsCheckboxes(response, form, type === 'User' ? 'Shop' : 'User')
                    });
                else if (type === 'User' && action === 'customersAssignment') {
                    promise = promise.then(response => {
                        return new Promise(resolve => {
                            E.callAPI('getActiveCustomers').then(customers => {
                                E.toggleLoadingScreen(false);
                                let table = form.find('table');
                                let tbody = table.find('tbody');
                                tbody.html('');
                                let i = 0;
                                let tr = null;
                                for (let customer of customers) {
                                    if (i % 4 === 0) {
                                        tr = $('<tr/>');
                                        tr.appendTo(tbody);
                                    }
                                    i++;
                                    let obj = $(
                                        `<td>${customer.name}</td>` +
                                        `<td><input type="checkbox" name="customers[${customer.id}]"></td>`
                                    );
                                    obj.appendTo(tr);
                                    let chBx = obj.find('[type="checkbox"]');
                                    if (customer.userId === id) chBx.prop('checked', true);
                                    else if (customer.userId !== null) chBx.addClass('foreign');
                                }
                                resolve(response);
                            });
                        });
                    });
                }
                else if (type === 'Shop' && action === 'setAssignmentsRules') {
                    promise = promise.then(response => {
                        return new Promise(resolve => {
                            E.callAPI('getAssignmentsRules', {type: 'Assignment', shopId: id}).then(assignments => {
                                E.toggleLoadingScreen(false);
                                let table = form.find('table');
                                let tbody = table.find('tbody');
                                tbody.html('');
                                let model = table.find('.model');
                                for (let assignment of assignments) {
                                    let tr = model.clone(true).removeClass('model').prependTo(tbody);
                                    tr.attr('data-id', assignment.id);
                                    E.formatEntityBlock(tr, assignment);
                                }
                                resolve(response);
                            });
                        });
                    });
                }
                promise.then(response => {
                    form.removeClass('hidden').addClass('visible');
                    E.toggleLoadingScreen(false);
                    let inputs = form.find('input, select');
                    for (let i = 0; i < inputs.length; i++) {
                        let input = inputs.eq(i);
                        let name = input.attr('name');
                        let value = response[name];
                        if (value === undefined) {
                            if (input.attr('data-default') !== undefined) value = input.attr('data-default');
                            else continue;
                        }
                        if (input.attr('type') === 'checkbox') input.prop('checked', value === "1");
                        else input.val(value);
                    }
                });
            }
        }
    },
    toggleHelp: function() {
        let form = $('.form.visible');
        if (form.length === 0) throw new Error("No active form");
        let help = form.find('.helpText');
        if (help.length === 0) throw new Error("No help here");
        if (help.hasClass('visible')) help.removeClass('visible').addClass('hidden');
        else help.addClass('visible').removeClass('hidden');
    },
    doClosing: function() {
        let form = $('.form.visible');
        let help = form.find('.helpText.visible');
        if (help.length === 1) help.removeClass('visible').addClass('hidden');
        else E.toggleForm();
    },
    addAssignmentsCheckboxes: function(response, form, type) {
        E.toggleLoadingScreen(true);
        return new Promise(resolve => {
            E.callAPI('getShortList', {type: type}).then(assignments => {
                let div = form.find('.styledCheckboxes');
                let checked = [];
                let chInp = div.find('input:checked');
                let list;
                if (type === 'Customer') list = response.customers;
                else list = response.assignments;
                if (list === undefined) {
                    for (let i = 0; i < chInp.length; i++)
                        checked.push(/[\d]+/.exec(chInp.eq(i).attr('name'))[0]);
                    checked.map(value => {return parseInt(value)});
                }
                else checked = list;
                div.html('');
                for (let id in assignments) {
                    if (!assignments.hasOwnProperty(id)) continue;
                    let name = assignments[id];
                    let listType = type === 'Customer' ? 'customers' : 'assignments';
                    $('<input type="checkbox" name="' + listType + '[' + id + ']" title="' + name + '">')
                        .prop('checked', checked.indexOf(parseInt(id)) > -1)
                        .appendTo(div);
                }
                resolve(response);
            });
        });
    },
    saveForm: function() {
        let form = $('.form.visible');
        let id = form.attr('data-id');
        let type = form.attr('data-entity');
        let action = form.attr('data-action');
        let tab = $('.tab[data-hash="'+type+'"]');
        let data = {};
        let inputs = form.find('input, select');
        for (let i = 0; i < inputs.length; i++) {
            let input = inputs.eq(i);
            let name = input.attr('name');
            if (name === undefined) continue;
            let value;
            if (input.attr('type') === 'checkbox') value = input.prop('checked') ? 1 : 0;
            else value = input.val();
            if (/\[/.test(name)) {
                if (input.hasClass('foreign') && value === 0) continue;
                let parsed = /^([-_\w\d]+)\[(\d+)\]/.exec(name);
                let arrName = parsed[1];
                let num = parseInt(parsed[2]);
                if (data[arrName] === undefined) data[arrName] = {};
                data[arrName][num] = value;
            }
            else data[name] = value;
            if (!E.testValue(type, name, value)) {
                /* special for password for edit user */
                if (type === 'User' && action === undefined && name === 'password'
                    && value === '' && id !== undefined) {
                    delete data.password;
                    continue;
                }
                /* --------------------------------- */
                E.msg('Значение не соответствует ограничениям', 'error');
                input.addClass('error');
                return;
            }
        }
        /* special for reservations rules change */
        if (type === 'Shop' && action === 'setAssignmentsRules') {
            let trs = form.find('table tbody tr');
            for (let i = 0; i < trs.length; i++) {
                let tr = trs.eq(i);
                let id = tr.attr('data-id');
                let type = tr.find('[data-name="type"]').val();
                let argument = tr.find('[data-name="argument"]').val();
                data[id] = {type: type, argument: argument};
            }
        }
        /* --------------------------------- */
        E.toggleLoadingScreen(true);
        let postAction, post;
        if (id === undefined) {
            postAction = 'addEntity';
            post = {type: type, data: data};
        }
        else {
            if (type === 'Shop' && action === 'setAssignmentsRules') postAction = 'setAssignmentsRules';
            else postAction = 'editEntity';
            post = {type: type, id: id, data: data}
        }
        E.callAPI(postAction, post).then(response => {
            E.toggleLoadingScreen(false);
            if (response.inputError === undefined) {
                let tr;
                if (postAction === 'addEntity') {
                    tr = tab.find('.model').clone(true).removeClass('model').prependTo(tab.find('tbody'));
                    tr.attr('data-id', response.id);
                }
                else tr = tab.find('.entity[data-id='+id+']');
                for (let name in response.values) if (response.values.hasOwnProperty(name))
                    data[name] = response.values[name];
                E.formatEntityBlock(tr, data);
                E.toggleForm();
            }
            else {
                E.msg(response.inputError.txt, 'error');
                form.find('[name="'+response.inputError.input+'"]').addClass('error');
            }
        });
    },
    toggleTab: function(li) {
        E.get(['mustChangePassword']).then(response => {
            if (response.mustChangePassword) {
                li = $('.tabs li[data-hash="opts"]');
                E.msg('Вы должны сначала сменить пароль');
            }
            let hash = li.attr('data-hash');
            let screen = $('.screen.visible');
            $('.tabs li.active').removeClass('active');
            screen.find('.tab.active').removeClass('active');
            li.addClass('active');
            screen.find('.tab[data-hash="' + hash + '"]').addClass('active');
            E.set({activeTab: hash});
            switch (hash) {
                case 'Shop':
                case 'User':
                case 'Customer':
                    E.loadData(hash);
                    break;
            }
        });
    },
    loadData: function(hash) {
        E.toggleLoadingScreen(true);
        let tab = $('.tab[data-hash="'+hash+'"]');
        let table = tab.find('table');
        table.find('tbody tr').remove();
        E.callAPI('getList', {type: hash}).then(response => {
            E.toggleLoadingScreen(false);
            let model = table.find('.model');
            for (let i in response) {
                let entity = response[i];
                let tr = model.clone(true).removeClass('model').attr('data-id', entity.id).prependTo(tab.find('tbody'));
                E.formatEntityBlock(tr, entity);
            }
        })
    },
    formatEntityBlock: function(tr, data) {
        let nodes = tr.find('[data-name]');
        for (let i = 0; i < nodes.length; i++) {
            let node = nodes.eq(i);
            let name = node.attr('data-name');
            let value;
            if (data[name] === undefined) continue;
            value = data[name];
            switch (node[0].nodeName) {
                case 'INPUT':
                case 'SELECT':
                    if (node.attr('type') === 'checkbox')
                        node.prop('checked', typeof value === 'boolean' ? value : (parseInt(value) === 1));
                    else node.val(value);
                    break;
                default:
                    switch (node.attr('data-type')) {
                        case 'date':
                            node.text(E.countTextDate(value, false)).attr('title', value);
                            break;
                        default:
                            node.text(value);
                            break;
                    }
                    break;
            }
        }
        let svgBlocks = tr.find('[data-addSVG]');
        for (let i = 0; i < svgBlocks.length; i++) {
            let svgBlock = svgBlocks.eq(i);
            let svgs = svgBlock.attr('data-addSVG').split(' ');
            for (let svg of svgs) {
                $.ajax('css/img/'+svg)
                    .then(response => svgBlock.html(svgBlock.html() + response.documentElement.outerHTML));
            }
            svgBlock.attr('data-addSVG', null);
        }
    },
    appendEntityEvents: function(tr) {
        tr.find('.delete').click(function() {
            if ($(this).hasClass('confirm')) {
                let tr = $(this).parents('.entity').addClass('removed');
                setTimeout(() => tr.remove(), parseFloat(tr.css('transition').split(' ')[1]));
                E.callAPI('deleteEntity', {type: tr.parents('.tab').attr('data-hash'), id: tr.attr('data-id')});
            }
            else {
                $(this).addClass('confirm');
                setTimeout(() => $(this).removeClass('confirm'), 5000);
            }
        });
        tr.find('.edit').click(function() {
            E.toggleForm($(this).parents('.tab').attr('data-hash'), $(this).parents('tr').attr('data-id'));
        });
        tr.find('input, select')
            .focus(function() {
                E.prevInputValue = $(this).val();
            })
            .change(function() {
                let data = {};
                let type = $(this).parents('.tab').attr('data-hash');
                let name = $(this).attr('data-name');
                let value = $(this).attr('type') === 'checkbox' ? $(this).prop('checked') : $(this).val();
                if (typeof value === 'boolean') value = value ? 1 : 0;
                if (!E.testValue(type, name, value)) {
                    E.msg('Значение не соответствует ограничениям', 'error');
                    $(this).addClass('error');
                    return;
                }
                data[name] = value;
                E.callAPI('editEntity', {
                    type: type,
                    id: $(this).parents('.entity').attr('data-id'),
                    data: data
                }).then(response => {
                    if (response.inputError !== undefined) {
                        E.msg(response.inputError.txt, 'error');
                        $(this).addClass('error');
                        $(this).val(E.prevInputValue);
                    }
                });
            })
            .keypress(function() {E.clearInputError($(this));});
        tr.find('.changePassword').click(function () {
            E.toggleForm('User', $(this).parents('.entity').attr('data-id'), 'changePassword');
        });
        tr.find('.setAssignmentsRules').click(function() {
            E.toggleForm('Shop', $(this).parents('.entity').attr('data-id'), 'setAssignmentsRules');
        });
        tr.find('.customersAssignment').click(function() {
            E.toggleForm('User', $(this).parents('.entity').attr('data-id'), 'customersAssignment');
        });
    },
    clearInputError: function(obj) {
        obj.removeClass('error');
        E.msg('');
    },
    testValue: function(type, name, value) {
        return E.glb[type] === undefined
            || E.glb[type].regex[name] === undefined
            || E.glb[type].regex[name].test(value);
    },
    callAPI: (action, POST = {}) => {
        POST.action = action;
        return new Promise(resolve => {
            E.get('hash').then(response => {
                if (response !== undefined) POST.hash = response;
                if (DEBUG) console.log('-->', POST);
                $.ajax({
                    url: API,
                    method: 'POST',
                    data: POST,
                    timeout: DEBUG ? 0 : 30000,
                    complete: function (jqXHR, textStatus) {
                        try {
                            if (textStatus !== 'success') {
                                console.log(jqXHR);
                                throw new Error('textStatus = ' + textStatus);
                            }
                            let response = jqXHR.responseText;
                            try {
                                response = JSON.parse(response);
                            }
                            catch (e) {
                                throw new Error('Cant parse JSON. Response:\n' + response);
                            }
                            if (response.output !== undefined) {
                                console.log('Output:\n' + response.output);
                            }
                            if (response.error !== undefined) throw new Error('Got error:\n' + response.error);
                            if (DEBUG) console.log('<--', response.result);
                            resolve(response.result);
                        } catch (e) {
                            E.toggleLoadingScreen(false);
                            E.msg('Got error, see console', 'error');
                            console.log(e.message);
                        }
                    }
                });
                
            });
        })
    },
    toggleAuth: (on) => {
        if (on) {
            E.get(['role', 'login', 'activeTab', 'mustChangePassword']).then(response => {
                $('.screen.login').removeClass('visible');
                let screen = null;
                if (response.role === 'manager') screen = $('.screen.manager');
                else screen = $('.screen.admin');
                screen.addClass('visible');
                $('.hello .login').text(response.login);
                $('.hello .role').text(response.role);
                if (response.mustChangePassword) $('.tabs li[data-hash="opts"]').click();
                else {
                    if (response.activeTab !== undefined) {
                        let tab = screen.find('.tabs li[data-hash="' + response.activeTab + '"]');
                        if (tab.length > 0) tab.click();
                        else screen.find('.tabs li').eq(0).click();
                    }
                    else screen.find('.tabs li').eq(0).click();
                }
            })
        }
        else {
            $('.screen.visible').removeClass('visible');
            $('.screen.login').addClass('visible');
            E.rm(['hash', 'role', 'login', 'mustChangePassword']);
        }
    },
    msgTimer: null,
    msg: (str, type = '') => {
        let msg = $('#message').removeClass('visible');
        if (str) {
            void msg[0].offsetWidth;
            msg.text(str).attr('class', type).addClass('visible');
            if (E.msgTimer !== null) clearTimeout(E.msgTimer);
            E.msgTimer = setTimeout(() => msg.click(), 10000);
        }
        else {
            setTimeout(() => msg.text(''), parseFloat(msg.css('transition').split(' ')[1]));
        }
    },
    get: (name = null) => {
        return new Promise(resolve => {
            chrome.storage.local.get(name, data => {
                resolve((name === null || typeof name === 'object') ? data : data[name]);
            });
        })
    },
    set: obj => {
        return new Promise(resolve => {
            chrome.storage.local.set(obj, () => resolve(true));
        });
    },
    rm: name => {
        return new Promise(resolve => {
            chrome.storage.local.remove(name, () => resolve(true))
        });
    },
    countTextDate: function(value, future) {
        let now = new Date();
        value = new Date(value);
        value.setMinutes(value.getMinutes() - now.getTimezoneOffset());
        let dif = Math.round((value - now) / 1000);
        let str = '';
        let periods = {
            month: 3600 * 24 * 30,
            day: 3600 * 24,
            hour: 3600,
            minute: 60,
        };
        future = (future === undefined ? dif > 0 : future);
        if (Math.abs(dif) > periods.month) {
            if (future) return 'больше месяца';
            else return 'давно';
        }
        else if (Math.abs(dif) < periods.minute) {
            if (future) return 'меньше минуты';
            else return 'только что';
        }
        dif = Math.abs(dif);
        if (dif > periods.day) {
            let num = Math.floor(dif / (periods.day));
            str += num + ' ';
            switch (true) {
                case num >= 5 : str += 'дней'; break;
                case num >= 2 : str += 'дня'; break;
                case num === 1 : str += 'день'; break;
            }
        }
        else if (dif > periods.hour) {
            let num = Math.floor(dif / (periods.hour));
            str += num + ' ';
            switch (true) {
                case num >= 5 : str += 'часов'; break;
                case num >= 2 : str += 'часа'; break;
                case num === 1 : str += 'час'; break;
            }
        }
        else if (dif > periods.minute) {
            let num = Math.floor(dif / (periods.minute));
            str += num + ' ';
            switch (true) {
                case num >= 5 : str += 'минут'; break;
                case num >= 2 : str += 'минуты'; break;
                case num === 1 : str += 'минута'; break;
            }
        }
        if (future) return str;
        else return str + ' назад';
    },
    generatePasswordString: function() {
        let symbols = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        let min = 6, max = 10;
        let string = '';
        let length = min + Math.floor(Math.random() * (max - min + 1));
        while (string.length < length) string += symbols[Math.floor(Math.random() * (symbols.length  - 1))];
        if (!E.testValue('User', 'password', string)) {
            throw new Error('Generator produces wrong passwords');
        }
        return string;
    }
};