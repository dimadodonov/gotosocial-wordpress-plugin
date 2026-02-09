<?php
/**
 * Template: Widget HTML
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="gotosocial" class="gotosocial">
    <button type="button" class="gotosocial__btn" aria-expanded="false" aria-controls="gotosocial-list"></button>
    <div id="gotosocial-list" class="gotosocial__wrap" hidden>
        <?php if (!empty($telegram) && $telegram !== '#') : ?>
        <a class="gotosocial__item telegram" href="<?php echo esc_url($telegram); ?>" target="_blank" rel="noopener">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'assets/svg/icon--social-telegram.svg'; ?>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($whatsapp)) : ?>
        <a class="gotosocial__item whatsapp" href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="noopener">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'assets/svg/icon--social-whatsapp.svg'; ?>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($max)) : ?>
        <a class="gotosocial__item max" href="<?php echo esc_url($max); ?>" target="_blank" rel="noopener">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'assets/svg/icon--social-max.svg'; ?>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($vk) && $vk !== '#') : ?>
        <a class="gotosocial__item vk" href="<?php echo esc_url($vk); ?>" target="_blank" rel="noopener">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'assets/svg/icon--social-vk.svg'; ?>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($instagram) && $instagram !== '#') : ?>
        <a class="gotosocial__item instagram" href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'assets/svg/icon--social-instagram.svg'; ?>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($viber) && $viber !== '#') : ?>
        <a class="gotosocial__item viber" href="<?php echo esc_url($viber); ?>" target="_blank" rel="noopener">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'assets/svg/icon--social-viber.svg'; ?>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($pinterest) && $pinterest !== '#') : ?>
        <a class="gotosocial__item pinterest" href="<?php echo esc_url($pinterest); ?>" target="_blank" rel="noopener">
            <?php include plugin_dir_path(dirname(__FILE__)) . 'assets/svg/icon--social-pinterest.svg'; ?>
        </a>
        <?php endif; ?>
    </div>
</div>
