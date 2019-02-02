<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Etsy helper</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i&amp;subset=cyrillic"
          rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel="stylesheet/less" type="text/css" href="css/popup.less">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/less_conf.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/less.js/3.8.1/less.min.js"></script>
    <script type="text/javascript" src="js/E.js"></script>
    <script type="text/javascript" src="js/popup.js"></script>
</head>
<body>
<div id="message"></div>
<div id="loading"><div>загрузка...</div></div>
<div class="login screen visible">
    <div class="header">Авторизация</div>
    <form>
        <label>
            <span class="name">Логин</span>
            <input type="text" name="login">
        </label>
        <label>
            <span class="name">Пароль</span>
            <input type="password" name="password">
        </label>
        <button>Отправить</button>
    </form>
</div>
<div class="admin screen">
    <div class="exit"></div>
    <div class="hello">
        <span class="loginUnderline"><span class="login"></span></span> (<span class="role"></span>)
    </div>
    <ul class="tabs">
        <li data-hash="Shop">Магазины</li>
        <li data-hash="User">Пользователи</li>
        <li data-hash="Customer">Заказчики</li>
        <li data-hash="stats">Статистика</li>
        <li data-hash="opts"></li>
    </ul>
    <div class="tab" data-hash="Shop">
        <div class="add"></div>
        <table class="entitiesList">
            <thead>
            <tr>
                <td class="forButton"></td>
                <td>Название</td>
                <td></td>
                <td>Создан</td>
                <td class="forButton"></td>
            </tr>
            <tr class="model entity">
                <td class="forButton"><a title="Изменить" class="edit" data-addSVG="pencil.svg"></a></td>
                <td><input data-name="name" type="text"/></td>
                <td><button class="setAssignmentsRules">Правила резервации</button></td>
                <td data-name="created" data-type="date"></td>
                <td class="forButton">
                    <a title="Удалить" class="delete" data-addSVG="delete.svg danger.svg"></a>
                </td>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="tab" data-hash="User">
        <div class="add"></div>
        <table class="entitiesList">
            <thead>
            <tr>
                <td class="forButton"></td>
                <td>Логин</td>
                <td>Тип</td>
                <td></td>
                <td>Процентр</td>
                <td></td>
                <td>Создан</td>
                <td class="forButton"></td>
            </tr>
            <tr class="model entity">
                <td class="forButton"><a title="Изменить" class="edit" data-addSVG="pencil.svg"></a></td>
                <td><input type="text" data-name="login"></td>
                <td>
                    <select data-name="role">
                        <option value="ADMIN" selected="true">Админ</option>
                        <option value="MANAGER">Менеджер</option>
                    </select>
                </td>
                <td><button class="customersAssignment">Клиенты</button></td>
                <td><input type="text" data-name="percent">%</td>
                <td>
                    <button class="changePassword" title="Сменить пароль">Пароль</button>
                    <input type="checkbox" data-name="mustChangePassword" title="Должен сменить">
                </td>
                <td data-name="created" data-type="date"></td>
                <td class="forButton">
                    <a title="Удалить" class="delete" data-addSVG="delete.svg danger.svg"></a>
                </td>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="tab" data-hash="Customer">
        <table class="entitiesList">
            <thead>
            <tr>
                <td>Имя</td>
                <td>Менеджер</td>
                <td>Создан</td>
                <td class="forButton"></td>
            </tr>
            <tr class="model entity">
                <td data-name="name"></td>
                <td data-name="login"></td>
                <td data-name="created" data-type="date"></td>
                <td class="forButton">
                    <a title="Удалить" class="delete" data-addSVG="delete.svg danger.svg"></a>
                </td>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="tab" data-hash="stats">
    
    </div>
    <div class="tab" data-hash="opts">
        <div class="flexBox">
            <div class="block changeOwnPassword">
                <div class="header">Сменить пароль</div>
                <table>
                    <tr><td>Новый пароль</td><td><input type="password" name="password1"></td></tr>
                    <tr><td>Повторить</td><td><input type="password" name="password2"></td></tr>
                </table>
                <button>Сменить</button>
            </div>
        </div>
    </div>
</div>
<div class="form" data-entity="Shop">
    <div class="bg"></div>
    <div class="content">
        <div class="close"></div>
        <div class="header">Магазин</div>
        <table>
            <tr><td>Название</td><td><input type="text" name="name"></td></tr>
            <tr><td>Пользователи</td><td class="styledCheckboxes"></td></tr>
        </table>
        <button class="ok">OK</button>
    </div>
</div>
<div class="form" data-entity="Shop" data-action="setAssignmentsRules">
    <div class="bg"></div>
    <div class="content">
        <div class="close"></div>
        <div class="help"></div>
        <div class="header">Правила резервации клиентов</div>
        <table>
            <thead>
            <tr class="model">
                <td data-name="login"></td>
                <td>
                    <select data-name="type" data-default="">
                        <option disabled value="">---ТИП---</option>
                        <option value="ORDER">По порядку</option>
                        <option value="TIME">Период времени</option>
                        <option value="COUNT">Кол-во заказов</option>
                    </select>
                </td>
                <td><input type="text" data-name="argument" placeholder="аргумент"></td>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button class="ok">OK</button>
    </div>
    <div class="helpText">
        Если у менеджера не определены правила резервации, то он не может резервировать клиентов!<br/>
        Правила определимы ТОЛЬКО для менеджеров.<br/>
        <b>Типы правил:<br/>
        <ul>
            <li>
                <u>По порядку</u> аргумент должен быть целым числом больше нуля (1, 5, 100).
                Счет идет от меньшего к бОльшему: например, менеджер с аргументом 100 не сможет брать заказы, пока
                заказ не зарезервируют все менеджеры с арументом меньше 100. Пропуск чисел допустим и не оказывает
                влияния на органичения
            </li>
            <li>
                <u>По времени</u> аргумент должен быть записью времени вида "10:00-11:30". ВНИМАНИЕ! Расчет ведется
                по времени API-сервера.
            </li>
            <li>
                <u>Кол-во заказов</u> аргумент должен быть вида "10/2", где 10 - максимальное кол-во заказов за указанный
                период, 2 - период в днях. Допустима укороченная запись "10", кол-во дней считается равным 1
            </li>
        </ul>
    </div>
</div>
<div class="form" data-entity="User">
    <div class="bg"></div>
    <div class="content">
        <div class="close"></div>
        <div class="header">Пользователь</div>
        <table>
            <tr><td>Логин</td><td><input type="text" name="login"></td></tr>
            <tr>
                <td>Новый пароль</td>
                <td>
                    <input type="text" name="password">
                    <button class="generatePassword">Генерировать</button>
                </td>
            </tr>
            <tr>
                <td>Роль</td>
                <td>
                    <select name="role">
                        <option value="ADMIN">админ</option>
                        <option value="MANAGER">менеджер</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Процент</td>
                <td><input type="text" name="percent" data-default="0">%</td>
            </tr>
            <tr>
                <td>Должен сменить пароль</td>
                <td><input type="checkbox" name="mustChangePassword" data-default="1"></td>
            </tr>
            <tr>
                <td>Магазины</td>
                <td class="styledCheckboxes"></td>
            </tr>
        </table>
        <button class="ok">OK</button>
    </div>
</div>
<div class="form" data-entity="User" data-action="changePassword">
    <div class="bg"></div>
    <div class="content">
        <div class="close"></div>
        <div class="header">Сменить пароль</div>
        <label>Новый пароль пользователя <input type="text" name="password"></label>
        <button class="ok">OK</button>
    </div>
</div>
<div class="form" data-entity="User" data-action="customersAssignment">
    <div class="bg"></div>
    <div class="content">
        <span name="login"></span>
        <div class="close"></div>
        <div class="header">Активные клиенты менеджера</div>
        <div class="text">Полупрозрачность чекбокса означает, что этот заказчик уже закреплен за другим менеджером</div>
        <div data-name="login"></div>
        <table>
            <thead>
            <tr class="model">
                <td data-name="name"></td>
                <td><input type="checkbox" data-name="isAssigned"></td>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button class="ok">OK</button>
    </div>
</div>
<div class="manager screen">
    <div class="hello">
        <span class="loginUnderline"><span class="login"></span></span>
    </div>
    <div class="exit"></div>
    <ul class="tabs">
        <li data-hash="stats">Статистика</li>
        <li data-hash="opts"></li>
    </ul>
    <div class="tab active" data-hash="opts"></div>
</div>
</body>
</html>