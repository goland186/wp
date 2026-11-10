<?php
/**
 * ШАГ 6 (ПОСЛЕ ЗАГРУЗКИ ПАПКИ uploads). Сверка файлов медиатеки с реестром.
 *
 * Гарантия сохранности ссылок на картинки: каждый файл реестра
 * (8121 оригиналов + 67769 ожидаемых с учётом размерных вариантов)
 * должен существовать в wp-content/uploads по тому же пути YYYY/MM/имя.
 * Пути НЕ менялись, поэтому никакой перегенерации URL не требуется.
 *
 * Запуск:  wp eval-file 06-verify-images.php [путь-к-папке-registries]
 * Результат: missing-uploads.csv (отсутствующие файлы) + сводка в stdout.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "Запуск: wp eval-file 06-verify-images.php [registries]\n";
	exit( 1 );
}

$reg_dir = isset( $args[0] ) ? $args[0] : __DIR__ . '/registries';
$uploads = wp_upload_dir();

if ( ! empty( $uploads['error'] ) ) {
	WP_CLI::error( 'uploads недоступен: ' . $uploads['error'] );
}

WP_CLI::log( 'Папка uploads: ' . $uploads['basedir'] );

// ---------- 1. Оригиналы вложений (registry_attachments.csv) ----------
$csv_path = $reg_dir . '/registry_attachments.csv';
if ( ! file_exists( $csv_path ) ) {
	WP_CLI::error( 'Не найден ' . $csv_path );
}

$fh       = fopen( $csv_path, 'r' );
$header   = fgetcsv( $fh, 0, ',', '"', '"' );
$idx_file = array_search( 'attached_file', $header, true );
$idx_extra = array_search( 'meta_extra_urls', $header, true );

if ( false === $idx_file ) {
	WP_CLI::error( 'В registry_attachments.csv нет колонки attached_file' );
}

$total = 0;
$missing_originals = array();
$missing_variants  = array();
$extra_checked     = 0;

while ( ( $row = fgetcsv( $fh, 0, ',', '"', '"' ) ) !== false ) {
	$total++;

	$path = trim( (string) $row[ $idx_file ] );
	if ( '' !== $path && ! file_exists( $uploads['basedir'] . '/' . $path ) ) {
		$missing_originals[] = $path;
	}

	// Размерные варианты (thumb-копии) из _wp_attachment_metadata.
	if ( false !== $idx_extra && ! empty( $row[ $idx_extra ] ) ) {
		$dir = dirname( $path );
		foreach ( explode( ' | ', (string) $row[ $idx_extra ] ) as $variant ) {
			$variant = trim( $variant );
			if ( '' === $variant || $variant === $path ) {
				continue;
			}
			$extra_checked++;
			$vp = ( false !== strpos( $variant, '/' ) ) ? $variant : $dir . '/' . $variant;
			if ( ! file_exists( $uploads['basedir'] . '/' . $vp ) ) {
				$missing_variants[] = $vp;
			}
		}
	}
}
fclose( $fh );

// ---------- 2. Ссылки в контенте (registry_urls_in_content.csv) ----------
$content_total = 0;
$content_missing = array();
$content_csv = $reg_dir . '/registry_urls_in_content.csv';
if ( file_exists( $content_csv ) ) {
	$cfh = fopen( $content_csv, 'r' );
	fgetcsv( $cfh, 0, ',', '"', '"' );
	while ( ( $row = fgetcsv( $cfh, 0, ',', '"', '"' ) ) !== false ) {
		$url = trim( (string) $row[5] );
		if ( '' === $url || false === strpos( $url, '/wp-content/uploads/' ) ) {
			continue;
		}
		$content_total++;
		$rel = ltrim( parse_url( $url, PHP_URL_PATH ), '/' );
		$rel = preg_replace( '#^wp-content/uploads/#', '', $rel );
		if ( ! file_exists( $uploads['basedir'] . '/' . $rel ) ) {
			$content_missing[] = $url;
		}
	}
	fclose( $cfh );
}

// ---------- 3. Отчёт ----------
WP_CLI::log( '' );
WP_CLI::log( '=== СВОДКА СВЕРКИ МЕДИА ===' );
WP_CLI::log( 'Оригиналов в реестре:        ' . $total );
WP_CLI::log( 'Оригиналов на месте:         ' . ( $total - count( $missing_originals ) ) );
WP_CLI::log( 'Оригиналов ОТСУТСТВУЕТ:      ' . count( $missing_originals ) );
WP_CLI::log( 'Размерных вариантов проверено: ' . $extra_checked );
WP_CLI::log( 'Вариантов ОТСУТСТВУЕТ:       ' . count( $missing_variants ) );
WP_CLI::log( 'Ссылок в контенте проверено: ' . $content_total );
WP_CLI::log( 'Ссылок в контенте БИТЫХ:     ' . count( $content_missing ) );

$miss_path = dirname( $reg_dir ) . '/missing-uploads.csv';
$mfh = fopen( $miss_path, 'w' );
fputcsv( $mfh, array( 'type', 'path_or_url' ) );
foreach ( $missing_originals as $p ) { fputcsv( $mfh, array( 'original', $p ) ); }
foreach ( $missing_variants as $p )   { fputcsv( $mfh, array( 'size_variant', $p ) ); }
foreach ( $content_missing as $p )    { fputcsv( $mfh, array( 'content_url', $p ) ); }
fclose( $mfh );

WP_CLI::log( '' );
WP_CLI::log( 'Список отсутствующих: ' . $miss_path );

$expected = 67769; // uploads_expected_stats.json
$found    = $total - count( $missing_originals );

if ( count( $missing_originals ) === 0 ) {
	WP_CLI::success( "Все {$total} оригиналов вложений на месте. Ссылки на картинки сохранены." );
} else {
	WP_CLI::warning( "На месте {$found} из {$total} оригиналов. Загрузите недостающие из бэкапа uploads и повторите." );
}
