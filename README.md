## msTimeStamp
Снятие метки новинка, особый или популярный по времени

Приложение добавляет дополнительное поле "**действителен до**"  с выбором даты до которой будет действовать метка о том что товар **Новинка, Особый или Популярный**.

Проверка даты действия метки происходит во время входа пользователя на страницу. Если время действия метки истекло, то запись автоматически удаляется а у товара автоматически снимается метка.

*Например можно установить у товара чекбокс **Новинка** и выбрать дату по наступлению которой товар перестанет быть новинкой*

[logo]: https://file.modx.pro/files/5/5/4/55480fb5b3c965c33ffd2d3d9424bfd8.png

Возможность добавление своих полей с метками через системную настройку **mstimestamp_fields_stamp**. 
По умолчанию добавлены: *new,favorite,popular**
*Все поля должны хранить значения в msProductData.*

### Автоматическое снятие меток
По умолчанию раз в 4 часа происходит проверка меток, что позволяет не напрягать систему постоянными запросами в базу данных на наличие истекших меток. (время на проверку можно увеличить).
Это позволяет отказаться от **crontab** и начать использовать приложение сразу после установки.