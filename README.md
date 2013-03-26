# SocialAuther #
**SocialAuther** - это PHP библиотека для упрощения реализации аутентификации на вашем сайте через популярные социальные сети и сервисы:

- [ВКонтакте](http://vk.com/)
- [Одноклассники](http://odnoklassniki.ru/)
- [Mail.Ru](http://mail.ru/)
- [Yandex](http://yandex.ru/)
- [Google](http://google.com/)
- [Facebook](http://facebook.com/)

**Заметка:** _в данном примере настройка осуществляется для **локального сервера**_

**Заметка:** _для работы библиотеки подключите автозагрузчик классов_ `require_once 'lib/SocialAuther/autoload.php';`

## Использование ##

### Аутентификация через ВКонтакте ###

**Внимание!** _Если при переходе по ссылке авторизации, которую генериует метод $vkAdapter->getAuthUrl(), в качестве ответа выводится сооб щение_ `{"error":"invalid_request","error_description":"Security Error"}`_, то вам необходимо отправиться на [страницу настроек](http://vk.com/settings) вашего vk аккаунта. В разделе "Безопасность Вашей страницы" нажмите ссылку "Посмотреть историю активности". В открывшемся окне нажмите "Завершить все сеансы", для очистки vk кэша. Таким образом, возникшая проблема_ `security_error` _будет устранена._

Для осуществления аутентификации через социальную сеть ВКонтакте вам необходимо предварительно создать новый проект и сконфигурировать параметры: `client_id`, `client_secret` и `redirect_uri`:

- **Шаг 1.** Создание [нового приложения](http://vk.com/editapp?act=create):
	- название: "SocialAuther Test"
	- тип: _"Веб-сайт"_
- **Шаг 2.** Настройка секции "_Open API_":
	- адрес сайта: `http://localhost/auth?provider=vk`
	- базовый домен: "localhost"
- **Шаг 3.** Конфигурация параметров `client_id`, `client_secret` и `redirect_uri`:
	- `client_id` - содержится в опции _"ID приложения"_. Пример: `3078654`
	- `client_secret` - содержится в опции _"Защищенный ключ"_. Пример: `zrCHcmKAcBvblSUIBIwu`
	- `redirect_uri` - содержится в опции _"Адрес сайта"_. Пример: `http://localhost/auth?provider=vk`
- **Шаг 4.** Использование **SocialAuther**.

Применение **SocialAuther**:

	<?php
	
	// конфигурация настроек адаптера
    $vkAdapterConfig = array(
        'client_id'     => '3078654',
        'client_secret' => 'zrCHcmKAcBvblSUIBIwu',
        'redirect_uri'  => 'http://localhost/auth?provider=vk'
    );

	// создание адаптера и передача настроек
	$vkAdapter = new SocialAuther\Adapter\Vk($vkAdapterConfig);

	// передача адаптера в SocialAuther
	$auther = new SocialAuther\SocialAuther($vkAdapter);

	// аутентификация и вывод данных пользователя или вывод ссылки для аутентификации
	if (!isset($_GET['code'])) {
		echo '<p><a href="' . $auther->getAuthUrl() . '">Аутентификация через ВКонтакте</a></p>';
	} else {
		if ($auther->authenticate()) {
			if (!is_null($auther->getSocialId()))
				echo "Социальный ID пользователя: " . $auther->getSocialId() . '<br />';
			
			if (!is_null($auther->getName()))
				echo "Имя пользователя: " . $auther->getName() . '<br />';
			
			if (!is_null($auther->getEmail()))
				echo "Email пользователя: " . $auther->getEmail() . '<br />';
			
			if (!is_null($auther->getSocialPage()))
				echo "Ссылка на профиль пользователя: " . $auther->getSocialPage() . '<br />';

			if (!is_null($auther->getSex()))
				echo "Пол пользователя: " . $auther->getSex() . '<br />';

			if (!is_null($auther->getBirthday()))
				echo "День Рождения: " . $auther->getBirthday() . '<br />';

			// аватар пользователя 
			if (!is_null($auther->getAvatar()))
				echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
		}
	}

### Аутентификация через Одноклассники ###

Для осуществления аутентификации через социальную сеть Одноклассники вам необходимо предварительно создать новый проект и сконфигурировать параметры: `client_id`, `client_secret`, `redirect_uri`, `public_key`:

- **Шаг 1.** Создание [нового приложения](http://www.odnoklassniki.ru/dk?st.cmd=appEditWizard&st._aid=Apps_Info_MyDev_AddApp):
    - Выбираем "Вне Одноклассников"
    - Выбираем "oAuth авторизация"
- **Шаг 2.** Настройка приложения:
	- название: "SocialAuther Test"
    - shortname: _www.odnoklassniki.ru/game/auther
    - ссылка на приложение: `http://localhost/auth?provider=odnoklassniki`
    - "Ссылка на картинку" и "Ссылка на аватарку": ссылка на любое изображение
- **Шаг 3.** Конфигурация параметров `client_id`, `client_secret`, `redirect_uri`, `public_key`. Всю нужную информацию вы найдёте в письме, которое было отправлено на вашу электронную почту после успешного добавления приложения:
	- `client_id` - Application ID. Пример: `658606315`
	- `client_secret` - секретный ключ приложения. Пример: `C35045020A8C7C066F25C4C7`
	- `redirect_uri` - ссылка на приложение. Пример: `http://localhost/auth?provider=odnoklassniki`
	- `public_key` - публичный ключ приложения. Пример: `BAMKABABACADCBBAB`
- **Шаг 4.** Использование **SocialAuther**.

Применение **SocialAuther**:

    <?php

    $odnoklassnikiConfig = array(
        'client_id'     => '658606315',
        'client_secret' => 'C35045020A8C7C066F25C4C7',
        'redirect_uri'  => 'http://localhost/auth?provider=odnoklassniki',
        'public_key'    => 'BAMKABABACADCBBAB'
    );

    $odnoklassnikiAdapter = new SocialAuther\Adapter\Odnoklassniki($odnoklassnikiConfig);

    $auther = new SocialAuther\SocialAuther($odnoklassnikiAdapter);

    if (!isset($_GET['code'])) {
        echo '<p><a href="' . $auther->getAuthUrl() . '">Аутентификация через Одноклассники</a></p>';
    } else {
        if ($auther->authenticate()) {
            if (!is_null($auther->getSocialId()))
                echo "Социальный ID пользователя: " . $auther->getSocialId() . '<br />';

            if (!is_null($auther->getName()))
                echo "Имя пользователя: " . $auther->getName() . '<br />';

            if (!is_null($auther->getEmail()))
                echo "Email пользователя: " . $auther->getEmail() . '<br />';

            if (!is_null($auther->getSocialPage()))
                echo "Ссылка на профиль пользователя: " . $auther->getSocialPage() . '<br />';

            if (!is_null($auther->getSex()))
                echo "Пол пользователя: " . $auther->getSex() . '<br />';

            if (!is_null($auther->getBirthday()))
                echo "День Рождения: " . $auther->getBirthday() . '<br />';

            // аватар пользователя
            if (!is_null($auther->getAvatar()))
                echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
        }
    }

### Аутентификация через Mail.ru ###

- **Шаг 1.** Создание [нового приложения](http://api.mail.ru/sites/my/add):
    - соглашаемся с правилами
    - название: "SocialAuther Test"
    - адрес главной страницы: `http://localhost/auth?provider=mailru`
- **Шаг 2.** Настройка приложения:
    - скачиваем предложенный файл receiver.html и помещаем в папку проекта
    - для установки на локальный сервер, нажимаем "Пропустить"
- **Шаг 3.** Конфигурация параметров `client_id`, `client_secret`, `redirect_uri`:
	- `client_id` - ID. Пример: `670707`
	- `client_secret` - секретный ключ. Пример: `a619062972f2073ded61405b8f8eccd2`
	- `redirect_uri` - адрес главной страницы. Пример: `http://localhost/auth?provider=mailru`
- **Шаг 4.** Использование **SocialAuther**.

Применение **SocialAuther**:

    <?php

    $mailruAdapterConfig = array(
        'client_id'     => '670707',
        'client_secret' => 'a619062972f2073ded61405b8f8eccd2',
        'redirect_uri'  => 'http://localhost/auth?provider=mailru'
    );

    $mailruAdapter = new SocialAuther\Adapter\Mailru($mailruAdapterConfig);

    $auther = new SocialAuther\SocialAuther($mailruAdapter);

    if (!isset($_GET['code'])) {
        echo '<p><a href="' . $auther->getAuthUrl() . '">Аутентификация через Mail.ru</a></p>';
    } else {
        if ($auther->authenticate()) {
            if (!is_null($auther->getSocialId()))
                echo "Социальный ID пользователя: " . $auther->getSocialId() . '<br />';

            if (!is_null($auther->getName()))
                echo "Имя пользователя: " . $auther->getName() . '<br />';

            if (!is_null($auther->getEmail()))
                echo "Email пользователя: " . $auther->getEmail() . '<br />';

            if (!is_null($auther->getSocialPage()))
                echo "Ссылка на профиль пользователя: " . $auther->getSocialPage() . '<br />';

            if (!is_null($auther->getSex()))
                echo "Пол пользователя: " . $auther->getSex() . '<br />';

            if (!is_null($auther->getBirthday()))
                echo "День Рождения: " . $auther->getBirthday() . '<br />';

            // аватар пользователя
            if (!is_null($auther->getAvatar()))
                echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
        }
    }

### Аутентификация через Yandex ###

- **Шаг 1.** Создание [нового приложения](https://oauth.yandex.ru/client/new):
- **Шаг 2.** Настройка приложения:
    - Название: "SocialAuther Test"
    - Права: "Яндекс.Логин - Адрес электронной почты; Дата рождения; Имя пользователя, ФИО, пол"
    - Callback URI: `http://localhost/auth?provider=yandex`
- **Шаг 3.** Конфигурация параметров `client_id`, `client_secret`, `redirect_uri`:
	- `client_id` - Id приложения. Пример: `bff0bfcaef054ab66c0538b39e0a86cf`
	- `client_secret` - Пароль приложения. Пример: `219ba88d386b114b9c6abef7eab4e8e4`
	- `redirect_uri` - Callback URI. Пример: `http://localhost/auth?provider=yandex`
- **Шаг 4.** Использование **SocialAuther**.

Применение **SocialAuther**:

    <?php

    $yandexAdapterConfig = array(
        'client_id'     => 'bff0bfcaef054ab66c0538b39e0a86cf',
        'client_secret' => '219ba88d386b114b9c6abef7eab4e8e4',
        'redirect_uri'  => 'http://localhost/auth?provider=yandex'
    );

    $yandexAdapter = new SocialAuther\Adapter\Yandex($yandexAdapterConfig);

    $auther = new SocialAuther\SocialAuther($yandexAdapter);

    if (!isset($_GET['code'])) {
        echo '<p><a href="' . $auther->getAuthUrl() . '">Аутентификация через Yandex</a></p>';
    } else {
        if ($auther->authenticate()) {
            if (!is_null($auther->getSocialId()))
                echo "Социальный ID пользователя: " . $auther->getSocialId() . '<br />';

            if (!is_null($auther->getName()))
                echo "Имя пользователя: " . $auther->getName() . '<br />';

            if (!is_null($auther->getEmail()))
                echo "Email пользователя: " . $auther->getEmail() . '<br />';

            if (!is_null($auther->getSocialPage()))
                echo "Ссылка на профиль пользователя: " . $auther->getSocialPage() . '<br />';

            if (!is_null($auther->getSex()))
                echo "Пол пользователя: " . $auther->getSex() . '<br />';

            if (!is_null($auther->getBirthday()))
                echo "День Рождения: " . $auther->getBirthday() . '<br />';

            // аватар пользователя
            if (!is_null($auther->getAvatar()))
                echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
        }
    }

### Аутентификация через Google ###

- **Шаг 1.** Создание [нового приложения](https://code.google.com/apis/console/):
    - в меню выбираем "API Access"
    - нажимаем на кнопку "Create an OAuth 2.0 Client ID..."
- **Шаг 2.** Настройка приложения:
    - API Access: "SocialAuther Test"
    - Нажимаем Next
    - Application Type: "Web application"
    - Your site or hostname: `localhost/google-auth`
    - Нажимаем Create client ID
- **Шаг 3.** Конфигурация параметров `client_id`, `client_secret`, `redirect_uri`:
	- `client_id` - Id приложения. Пример: `333739311538.apps.googleusercontent.com`
	- `client_secret` - Пароль приложения. Пример: `lZB3aW8UG8gDj6WVIEIcidt5`
	- `redirect_uri` - Callback URI. Пример: `http://localhost/auth?provider=google`
- **Шаг 4.** Использование **SocialAuther**.

Применение **SocialAuther**:

    <?php

    $googleAdapterConfig = array(
        'client_id'     => '393337311853.apps.googleusercontent.com',
        'client_secret' => 'B38WaUlZG8gDI6jIEWVct5id',
        'redirect_uri'  => 'http://localhost/auth?provider=google'
    );

    $googleAdapter = new SocialAuther\Adapter\Google($googleAdapterConfig);

    $auther = new SocialAuther\SocialAuther($googleAdapter);

    if (!isset($_GET['code'])) {
        echo '<p><a href="' . $auther->getAuthUrl() . '">Аутентификация через Google</a></p>';
    } else {
        if ($auther->authenticate()) {
            if (!is_null($auther->getSocialId()))
                echo "Социальный ID пользователя: " . $auther->getSocialId() . '<br />';

            if (!is_null($auther->getName()))
                echo "Имя пользователя: " . $auther->getName() . '<br />';

            if (!is_null($auther->getEmail()))
                echo "Email пользователя: " . $auther->getEmail() . '<br />';

            if (!is_null($auther->getSocialPage()))
                echo "Ссылка на профиль пользователя: " . $auther->getSocialPage() . '<br />';

            if (!is_null($auther->getSex()))
                echo "Пол пользователя: " . $auther->getSex() . '<br />';

            if (!is_null($auther->getBirthday()))
                echo "День Рождения: " . $auther->getBirthday() . '<br />';

            // аватар пользователя
            if (!is_null($auther->getAvatar()))
                echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
        }
    }

### Аутентификация через Facebook ###

- **Шаг 1.** Создание [нового приложения](https://developers.facebook.com/apps):
    - Нажимаем на кнопку "+ Создать новое приложение"
    - App Name: "SocialAuther Test"
    - Нажимаем "Продолжить"
- **Шаг 2.** Настройка приложения:
    - Нажимаем "Website with Facebook Login"
    - Вводим Site URL: `http://localhost/auth?provider=facebook`
- **Шаг 3.** Конфигурация параметров `client_id`, `client_secret`, `redirect_uri`:
	- `client_id` - App ID. Пример: `346158195993388`
	- `client_secret` - App Secret. Пример: `2de1ab376d1c17cd47250920c05ab386`
	- `redirect_uri` - Callback URI. Пример: `http://localhost/auth?provider=facebook`
- **Шаг 4.** Использование **SocialAuther**.

Применение **SocialAuther**:

    <?php

    $facebookAdapterConfig = array(
        'client_id'     => '346158195993388',
        'client_secret' => '2de1ab376d1c17cd47250920c05ab386',
        'redirect_uri'  => 'http://localhost/auth?provider=facebook'
    );

    $facebookAdapter = new SocialAuther\Adapter\Facebook($facebookAdapterConfig);

    $auther = new SocialAuther\SocialAuther($facebookAdapter);

    if (!isset($_GET['code'])) {
        echo '<p><a href="' . $auther->getAuthUrl() . '">Аутентификация через Facebook</a></p>';
    } else {
        if ($auther->authenticate()) {
            if (!is_null($auther->getSocialId()))
                echo "Социальный ID пользователя: " . $auther->getSocialId() . '<br />';

            if (!is_null($auther->getName()))
                echo "Имя пользователя: " . $auther->getName() . '<br />';

            if (!is_null($auther->getEmail()))
                echo "Email пользователя: " . $auther->getEmail() . '<br />';

            if (!is_null($auther->getSocialPage()))
                echo "Ссылка на профиль пользователя: " . $auther->getSocialPage() . '<br />';

            if (!is_null($auther->getSex()))
                echo "Пол пользователя: " . $auther->getSex() . '<br />';

            if (!is_null($auther->getBirthday()))
                echo "День Рождения: " . $auther->getBirthday() . '<br />';

            // аватар пользователя
            if (!is_null($auther->getAvatar()))
                echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
        }
    }

## Использование SocialAuther с несколькими социальными сетями и сервисами ##

    <?php

    $adapterConfigs = array(
        'vk' => array(
            'client_id'     => '3078654',
            'client_secret' => 'zrCHcmKAcBvblSUIBIwu',
            'redirect_uri'  => 'http://localhost/auth/?provider=vk'
        ),
        'odnoklassniki' => array(
            'client_id'     => '658606315',
            'client_secret' => 'C35045020A8C7C066F25C4C7',
            'redirect_uri'  => 'http://localhost/auth?provider=odnoklassniki',
            'public_key'    => 'BAMKABABACADCBBAB'
        ),
        'mailru' => array(
            'client_id'     => '670707',
            'client_secret' => 'a619062972f2073ded61405b8f8eccd2',
            'redirect_uri'  => 'http://localhost/auth/?provider=mailru'
        ),
        'yandex' => array(
            'client_id'     => 'bff0bfcaef054ab66c0538b39e0a86cf',
            'client_secret' => '219ba88d386b114b9c6abef7eab4e8e4',
            'redirect_uri'  => 'http://localhost/auth/?provider=yandex'
        ),
        'google' => array(
            'client_id'     => '393337311853.apps.googleusercontent.com',
            'client_secret' => 'B38WaUlZG8gDI6jIEWVct5id',
            'redirect_uri'  => 'http://localhost/auth?provider=google'
        ),
        'facebook' => array(
            'client_id'     => '346158195993388',
            'client_secret' => '2de1ab376d1c17cd47250920c05ab386',
            'redirect_uri'  => 'http://localhost/auth?provider=facebook'
        )
    );

    // создание адаптеров
    $adapters = array();
    foreach ($adapterConfigs as $adapter => $settings) {
        $class = 'SocialAuther\Adapter\\' . ucfirst($adapter);
        $adapters[$adapter] = new $class($settings);
    }

    if (!isset($_GET['code'])) {
        foreach ($adapters as $title => $adapter) {
            echo '<p><a href="' . $adapter->getAuthUrl() . '">Аутентификация через ' . ucfirst($title) . '</a></p>';
        }
    } else {
        if (isset($_GET['provider']) && array_key_exists($_GET['provider'], $adapters)) {
            $auther = new SocialAuther\SocialAuther($adapters[$_GET['provider']]);
        }

        if ($auther->authenticate()) {
            if (!is_null($auther->getSocialId()))
                echo "Социальный ID пользователя: " . $auther->getSocialId() . '<br />';

            if (!is_null($auther->getName()))
                echo "Имя пользователя: " . $auther->getName() . '<br />';

            if (!is_null($auther->getEmail()))
                echo "Email пользователя: " . $auther->getEmail() . '<br />';

            if (!is_null($auther->getSocialPage()))
                echo "Ссылка на профиль пользователя: " . $auther->getSocialPage() . '<br />';

            if (!is_null($auther->getSex()))
                echo "Пол пользователя: " . $auther->getSex() . '<br />';

            if (!is_null($auther->getBirthday()))
                echo "День Рождения: " . $auther->getBirthday() . '<br />';

            // аватар пользователя
            if (!is_null($auther->getAvatar()))
                echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
        }
    }

## История изменений ##

### SocialAuther 1.0 ###
- Добавлена возможность аутентификации через [ВКонтакте](http://vk.com/), [Одноклассники](http://odnoklassniki.ru/), [Mail.Ru](http://mail.ru/), [Yandex](http://yandex.ru/), [Google](http://google.com/), [Facebook](http://facebook.com/)