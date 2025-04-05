
# Парсер для анализа логов Apache 

![Project Preview](https://github.com/HannaBykovskaya/apache-logs-parser/blob/main/home-page.png)

Приложение включает в себя веб-интерфейс, реализованный на фреймворке Vue.js (находится на удаленном сервере) и REST API, представленный файлами api_access.php и api_error.php (находятся на сервере Apache).

## Основные возможности

### Логи доступа (Access Log)
![Project Preview](https://github.com/HannaBykovskaya/apache-logs-parser/blob/main/apache-access-logs.png)
- Табличное представление с пагинацией
- Фильтрация по: 
  - IP-адресу
  - HTTP-методу
  - Статусу ответа
  - Пути запроса
- Сортировка по:
	- IP-адресу
	- Дате
	- HTTP-методу

### Логи ошибок (Error Log)
![Project Preview](https://github.com/HannaBykovskaya/apache-logs-parser/blob/main/apache-error-logs.png)
- Группировка ошибок по типам
- Поиск по:
	- Дате
	  - Уровню ошибки (error, warning, notice)
	  - Клиентскому IP
- Сортировка по:
	- Дате
	- Уровню ошибки
	- Клиентскому IP
	- Сообщению

Для каждого компонента реализована пагинация.



### Предварительные требования
- PHP 8.0+
- Apache/Nginx
- Node.js 16+
