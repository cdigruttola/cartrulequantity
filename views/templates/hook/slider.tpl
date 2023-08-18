{*
* Copyright since 2007 Carmine Di Gruttola
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    cdigruttola <c.digruttola@hotmail.it>
* @copyright Copyright since 2007 Carmine Di Gruttola
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{block name='slider_product'}
    <div class="featured-products my-4 {block name='slider_product_class'}{/block}">


        {block name='slider_product_header'}
            <div class="featured-products__header d-flex align-items-center mb-3">
                {block name='slider_product_title'}
                    <h2>{$slider.title}</h2>
                {/block}
                <div class="featured-products__navigation d-flex flex-grow-0 flex-shrink-0 ml-auto">
                    <div class="swiper-button-prev swiper-button-custom position-static">
                        <span class="sr-only">{l s='Previous' d='Shop.Theme.Actions'}</span>
                        <span class="material-icons">keyboard_arrow_left</span>
                    </div>
                    <div class="swiper-button-next swiper-button-custom position-static">
                        <span class="sr-only">{l s='Next' d='Shop.Theme.Actions'}</span>
                        <span class="material-icons">keyboard_arrow_right</span>
                    </div>
                </div>
            </div>
            {block name='slider_product_subtitle'}
                {if !empty($slider.subtitle)}
                    <h3>{$slider.subtitle}</h3>
                {/if}
            {/block}
        {/block}

        {$sliderConfig = [
        'speed' => {$speed},
        'pause' => {$pause},
        'loop' => {$wrap},
        'breakpoints' => [
        '320' => [
        'slidesPerView' => 2
        ],
        '768' => [
        'slidesPerView' => 3
        ],
        '992' => [
        'slidesPerView' => 4
        ]
        ],
        'autoplay'=>['delay' =>5000]
        ]}

        <div class="swiper product-slider py-1 my-n1"
             data-swiper='{block name="slider_product_slider_options"}{$sliderConfig|json_encode}{/block}'>
            {block name='slider_product_products'}
                <div class="featured-products__slider swiper-wrapper {block name='slider_product_slider_class'}{/block}">
                    {foreach from=$slider.products item="product"}
                        {block name='product_miniature'}
                            {include file='catalog/_partials/miniatures/product.tpl' product=$product type='slider'}
                        {/block}
                    {/foreach}
                </div>
            {/block}
        </div>

    </div>
{/block}
