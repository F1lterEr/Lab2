var emptyField = 'Заполните поле!',
    shortLogin = 'Слишком короткий логин!',
    shortPass = 'Слишком короткий пароль!',
    notEqualPass = 'Пароли не совпадают!',
    notUniqueLogin = 'Пользователь с таким именем уже зарегистрирован!',
    valid = true;

var req = false;
if(window.XMLHttpRequest)
    req = new XMLHttpRequest();
else if(window.ActiveXObject)
    req = new ActiveXObject("Microsoft.XMLHTTP");

function ge(id) {
    return document.getElementById(id);
}

function isEmptyStr(str) {
    if(str == "") return true;
    var count = 0;
    for(var i = 0; i < str.length; ++i)
    if(str.charAt(i) == " ") ++count;
    return count == str.length;
}

function notValidField(field, str) {
    field.value = str; // Выводим инфу об ошибке в поле
    field.error = true; // Запоминаем, что поле заполнено не верно
    valid = false; // Считаем форму не валидной
    /* Вешаем обработчик события, который будет очищать поле от информации об ошибке при фокусе.
    При потере фокуса поля с type="password" меняют туре на "text", чтобы информация об ошибках не заменялась звёздочками.
    При фокусе на эти поля им необходимо вернуть назад их родной туре */
    field.onfocus = function () {
        if(field.id == 'pass' || field.id == 're_pass') field.type = 'password';
        if(field.error) field.value = '';
    }
    // Обработчик, который проверяет поле на корректность при потере им фокуса.
    field.onblur = function () {
        if(isEmptyStr(field.value)) {
            notValidField(field, emptyField);
            if(field.id == 'pass' || field.id == 're_pass') field.type = 'text';
        } else {
            field.error = false;
            switch(field.id) {
                /* Функции checkLogin() выполняют проверку полей по дополнительным параметрам,
                разным для каждого поля. */
                case 'login': checkLogin(); break;
            }
        }
    }
}

function checkLogin() {
    var login = ge('login');
    /*Логин не может быть короче 4 символов.
    Выводим инфу о том, что логин слишком короткий только если поле
    было заполнено ранее (!login.error).
    */
    if(login.value.length < 4 && !login.error) {
        notValidField(login, shortLogin);
        valid = false;
    } else if(!login.error) {
        /* Если логин достаточно длинный, то отправляем синхронный запрос
        для проверки его уникальности.
        */
        req.open('GET', 'index.php?isset_login=' + encodeURIComponent(login.value), false);
        req.send();
        //console.log('index.php?isset_login=' + encodeURIComponent(login.value), req);
        if(req.readyState == 4 && req.status == 200) {
            /*Если пользователь с таким логином уже есть, то
            выводим инфу об этом в поле.
            */
            if(req.responseText == '1') {
                notValidField(login, notUniqueLogin);
                valid = false;
            }
        }
    }
}

function checkPass() {
    var pass = ge('pass');
    var re_pass = ge('re_pass');
    if(!pass.error && !re_pass.error) {
        // Проверяем пароли на длинну и совпадают ли они.
        if(pass.value.length < 5 && pass.value == re_pass.value) {
            notValidField(pass, shortPass);
            notValidField(re_pass, shortPass);
            valid = false;
            /* Меняем туре на текст, чтобы не отображал звёздочки,
            как при вводе пароля.
            */
            pass.type = 'text';
            re_pass.type = 'text';
        // Аналогично, если пароли не совпадают.
        } else if(pass.value != re_pass.value) {
            notValidField(pass, notEqualPass);
            notValidField(re_pass, notEqualPass);
            pass.type = 'text';
            re_pass.type = 'text';
            valid = false;
        }
    }
}

function isValidForm() {
    var elementsF = ge('reg_form').elements,
        login = ge('login'),
        pass = ge('pass'),
        re_pass = ge('re_pass');
    valid = true;
    // Проверяем поля с type="text" и type="password" на "заполненность"
    for(var i = 0; i < elementsF.length; ++i) {
        if((elementsF[i].type == 'text' || elementsF[i].type == 'password') && isEmptyStr(elementsF[i].value)) {
            notValidField(elementsF[i], emptyField);
            elementsF[i].type = 'text';
        }
        if(elementsF[i].error) valid = false;
    }
    /* Выполняем дополнительную проверку полей по параметрам,
    разным для каждого поля
    */
    checkLogin();
    checkPass();
    return valid;
}