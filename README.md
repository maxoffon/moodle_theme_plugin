# Плагин темы для Moodle
<li> Чтобы установить тему, клонируйте репозиторий в новую папку <b>max</b> по адресу: /moodle/theme/max <br>
<li> Или сохраните репозиторий в папку "max", заархивируйте данную папку и назовите архив также "max" <i>(важно!)</i> виде zip-архива. Далее на веб-странице Moodle перейдите в раздел установки плагинов, выберите созданный zip-архив в качестве установочного пакета и следуйте инструкциям Moodle
<li> <i>Баг для исправления</i> Если при отображении главной страницы возникает ошибка в методе htmlspecialchars(), то в главной папке moodle откройте файл moodle/lib/weblib.php и в методе s() к условию $var === '' добавьте '|| is_array($var)'. Сохраните изменения.
