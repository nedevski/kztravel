<?php
defined( 'ABSPATH' ) || exit;

$GLOBALS['kztravel_country_labels'] = array(
	'bulgaria' => 'България',
	'greece'   => 'Гърция',
	'turkey'   => 'Турция',
);

$GLOBALS['kztravel_weekday_labels'] = array(
	'Понеделник',
	'Вторник',
	'Сряда',
	'Четвъртък',
	'Петък',
	'Събота',
	'Неделя',
);

$GLOBALS['kztravel_ui'] = array(
	'homeHeading'          => 'Открийте следващото си приключение',
	'homeSubheading'       => 'Подбрани ваканции с прозрачни цени и гъвкави дати на заминаване.',
	'noTripsMatch'         => 'Няма екскурзии, отговарящи на филтрите.',
	'clearFilters'         => 'Изчисти филтрите',
	'clear'                => 'Изчисти',
	'filterCountry'        => 'Държава',
	'filterByCountry'      => 'Филтрирай по държава',
	'filterPrice'          => 'Цена',
	'filterByPrice'        => 'Филтрирай по цена',
	'filterDuration'       => 'Продължителност',
	'filterByDuration'     => 'Филтрирай по продължителност',
	'filterCategory'       => 'Категория',
	'filterByCategory'     => 'Филтрирай по категория',
	'filterDiscount'       => 'Оферти',
	'filtersHeading'       => 'Филтри',
	'all'                  => 'Всички',
	'fullyBooked'          => 'Напълно заета',
	'contactUs'            => 'Свържете се с нас',
	'contactForAvailability' => 'Свържете се с нас за наличност',
	'bookNow'              => 'Запази сега',
	'contactPageTitle'     => 'Контакти',
	'contactHeading'       => 'Свържете се с нас',
	'contactIntro'         => 'Обадете се или ни пишете - ще ви помогнем да изберете дата и да запазите място.',
	'contactPhone'         => 'Телефон',
	'contactEmail'         => 'Имейл',
	'contactOffice'        => 'Или ни посетете в нашия офис',
	'contactWorkingHours'  => 'Работно време',
	'contactBankName'      => 'Банка',
	'contactIban'          => 'IBAN',
	'contactAccountHolder' => 'Титуляр',
	'contactBookingLink'   => 'Условия за записване',
	'allTrips'             => '← Всички екскурзии',
	'datesAndPricing'      => 'Дати и цени',
	'gallery'              => 'Галерия',
	'itinerary'            => 'Маршрут',
	'included'             => 'Включено',
	'notIncluded'          => 'Не е включено',
	'includedPrice'        => 'Включено',
	'onRequest'            => 'По запитване',
	'date'                 => 'Дата',
	'item'                 => 'Услуга',
	'price'                => 'Цена',
	'status'               => 'Статус',
	'available'            => 'Налична',
	'lastSpots'            => 'Последни места',
	'soldOut'              => 'Изчерпано',
	'completed'            => 'Приключила',
	'switchToDark'         => 'Превключи на тъмен режим',
	'switchToLight'        => 'Превключи на светъл режим',
	'darkMode'             => 'Тъмен режим',
	'lightMode'            => 'Светъл режим',
	'mainNav'              => 'Основна навигация',
	'navTrips'             => 'Ваканции',
	'navContact'           => 'Контакти',
	'navBooking'           => 'Как да резервирам',
	'openMenu'             => 'Отвори меню',
	'closeMenu'            => 'Затвори меню',
);

function kztravel_ui( string $key, ...$args ): string {
	$ui = $GLOBALS['kztravel_ui'];

	switch ( $key ) {
		case 'contactTripInquiry':
			return sprintf( 'Запитване за: %s', $args[0] ?? '' );
		case 'moreDates':
			$count = (int) ( $args[0] ?? 0 );
			return 1 === $count ? '(+ още 1 дата)' : sprintf( '(+ още %d дати)', $count );
		case 'nextDeparture':
			return sprintf( 'Следващо заминаване: %s', $args[0] ?? '' );
		case 'day':
			return sprintf( 'Ден %d', (int) ( $args[0] ?? 0 ) );
		case 'viewPhoto':
			return sprintf( 'Преглед на снимка %d от %d', (int) ( $args[0] ?? 0 ), (int) ( $args[1] ?? 0 ) );
		case 'photoGallery':
			return sprintf( 'Галерия със снимки от %s', $args[0] ?? '' );
		case 'suggestedTrips':
			return sprintf( 'Още от %s', $args[0] ?? '' );
		default:
			return $ui[ $key ] ?? $key;
	}
}

function kztravel_weekday_labels(): array {
	return $GLOBALS['kztravel_weekday_labels'];
}

function kztravel_country_labels(): array {
	return $GLOBALS['kztravel_country_labels'];
}
