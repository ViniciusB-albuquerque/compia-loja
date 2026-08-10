<?php

function compia_carregar_recursos(): void
{
    wp_enqueue_style(
        'compia-main',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'compia-main',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        [],
        '1.0.0',
        true
    );

    if (is_cart() || is_checkout()) {
        wp_enqueue_style(
            'compia-cart-checkout',
            get_stylesheet_directory_uri() . '/assets/css/cart-checkout.css',
            ['compia-main'],
            '1.0.0'
        );
    }

    if (is_front_page()) {
        wp_enqueue_style(
            'compia-home',
            get_stylesheet_directory_uri() . '/assets/css/home.css',
            ['compia-main'],
            '1.0.0'
        );
    }

    if (is_product()) {
        wp_enqueue_style(
            'compia-product',
            get_stylesheet_directory_uri() . '/assets/css/product.css',
            ['compia-main'],
            '1.0.0'
        );
    }
}

add_action('wp_enqueue_scripts', 'compia_carregar_recursos', 20);

/*
 * A página de categoria (is_tax product_cat) não recebe a classe
 * post-type-archive-product no body; esta classe unifica o escopo do
 * catálogo entre a loja e os arquivos de categoria.
 *
 * A home e a página de produto entram na mesma condição para reaproveitar o
 * estilo dos cards de produto (.compia-catalogo ul.products): na home é a seção
 * de destaques, na página de produto é a seção "Produtos relacionados" — ambas
 * usam o mesmo markup da loja, então herdam o visual sem duplicar CSS. A classe
 * só é acrescentada; loja e categoria continuam recebendo-a pelas próprias
 * condições, então o escopo existente não muda.
 */
function compia_classe_catalogo(array $classes): array
{
    if (is_shop() || is_product_taxonomy() || is_front_page() || is_product()) {
        $classes[] = 'compia-catalogo';
    }

    return $classes;
}

add_filter('body_class', 'compia_classe_catalogo');

/*
 * Escopo próprio da página de produto individual. Separado de compia-catalogo
 * porque os elementos daqui (galeria, resumo, formulário de compra, abas) são
 * estruturalmente diferentes do grid de cards do catálogo; a classe evita que
 * o CSS de product.css vaze para loja/categoria/home.
 */
function compia_classe_produto(array $classes): array
{
    if (is_product()) {
        $classes[] = 'compia-produto';
    }

    return $classes;
}

add_filter('body_class', 'compia_classe_produto');

/*
 * URL de uma categoria de produto com o tratamento de erro centralizado.
 * Usada pelo filtro da loja e pelos cards da home, evitando repetir o par
 * get_term_link() + is_wp_error() nos dois lugares.
 */
function compia_url_termo(WP_Term $termo): string
{
    $link = get_term_link($termo);

    return is_wp_error($link) ? '' : (string) $link;
}

/*
 * Barra de filtro por categoria de produto. Prioridade 5 para renderizar
 * antes da contagem (20) e da ordenação (30) nativas no mesmo hook.
 */
function compia_filtro_categorias(): void
{
    $categorias = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
    ]);

    // Sem categorias suficientes, o filtro não tem utilidade.
    if (is_wp_error($categorias) || count($categorias) < 2) {
        return;
    }

    $todos_ativo = is_shop() ? ' ativo' : '';
    ?>
    <nav class="compia-filtro-categorias" aria-label="Filtrar por categoria">
        <ul>
            <li>
                <a class="compia-filtro-categorias__item<?php echo $todos_ativo; ?>"
                    href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                    Todos
                </a>
            </li>
            <?php foreach ($categorias as $categoria) :
                $link = compia_url_termo($categoria);

                if ('' === $link) {
                    continue;
                }

                $ativo = is_tax('product_cat', $categoria->term_id) ? ' ativo' : '';
                ?>
                <li>
                    <a class="compia-filtro-categorias__item<?php echo $ativo; ?>"
                        href="<?php echo esc_url($link); ?>">
                        <?php echo esc_html($categoria->name); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php
}

add_action('woocommerce_before_shop_loop', 'compia_filtro_categorias', 5);