<?php
/**
 * 設定
 */
define( 'LOG_FILE', dirname( __FILE__ ) . '/hook.log' );
define( 'LOG_ERR', dirname( __FILE__ ) . '/hook-error.log' );
define( 'SECRET_KEY', 'secretkey' );

/**
 * 実行するコマンド
 */
$commands = array(
  'develop' => 'git pull origin develop',//developブランチ
  'main'  => 'git pull origin main' // mainブランチ
);